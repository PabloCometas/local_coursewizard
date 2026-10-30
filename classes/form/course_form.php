<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Course form behind the guided dialog.
 *
 * @package     local_coursewizard
 * @copyright   2026 cometas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursewizard\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/edit_form.php');

/**
 * Moodle's course edit form (course/edit_form.php) with all fields, defaults and
 * Moodle's own validation. The dialog (amd/src/wizard.js) only shows the fields of
 * one step at a time, all other fields keep their defaults.
 *
 * The short name is never asked: it is derived from the full name and made unique.
 */
class course_form extends \course_edit_form {

    /** @var array Dialog steps in order: step key => form elements shown in that step. */
    public const STEPS = [
        'fullname' => ['fullname'],
        'category' => ['category'],
        'dates' => ['startdate', 'enddate'],
        'summary' => ['summary_editor'],
        'image' => ['overviewfiles_filemanager'],
        'visible' => ['visible'],
    ];

    /** @var array Steps that are only asked if enabled in the plugin settings: step key => setting. */
    public const OPTIONALSTEPS = [
        'dates' => 'askdates',
    ];

    /** @var array Elements the dialog fills in itself and never shows. */
    public const AUTOMATIC = ['shortname'];

    /** @var array Element types that are never shown as a question. */
    protected const SKIPPEDTYPES = ['hidden', 'header', 'static', 'html', 'submit', 'button'];

    /**
     * Moodle's course form with labels that fit the dialog.
     */
    public function definition() {
        parent::definition();

        $mform = $this->_form;

        // Without a short name to tell apart, "Course full name" is needlessly long.
        if ($mform->elementExists('fullname')) {
            $mform->getElement('fullname')->setLabel(get_string('coursename', 'local_coursewizard'));
        }
        // Same word as in the question of the dialog.
        if ($mform->elementExists('category') && $mform->getElement('category')->getType() !== 'hidden') {
            $mform->getElement('category')->setLabel(get_string('category', 'local_coursewizard'));
        }
    }

    /**
     * Apply Moodle's own post-processing, derive the short name and expand all sections.
     */
    public function definition_after_data() {
        parent::definition_after_data();

        $mform = $this->_form;

        // Set before validation, so Moodle's required and unique checks see it.
        if ($this->is_submitted()) {
            $mform->_submitValues['shortname'] = self::unique_shortname((string) $mform->getSubmitValue('fullname'));
        }

        // The last step of the dialog creates and opens the course, same label as everywhere in the dialog.
        if ($mform->elementExists('buttonar')) {
            foreach ($mform->getElement('buttonar')->getElements() as $button) {
                if ($button->getName() === 'saveanddisplay') {
                    $button->setValue(get_string('createnow', 'local_coursewizard'));
                }
            }
        }

        // Collapsed sections would hide fields of the current step.
        foreach ($mform->_elements as $element) {
            if ($element->getType() === 'header') {
                $mform->setExpanded($element->getName(), true);
            }
        }
    }

    /**
     * Returns a short name based on the full name that no other course uses yet.
     *
     * @param string $fullname Full name of the new course.
     * @return string Short name, the full name with " 2", " 3" … appended if needed.
     */
    public static function unique_shortname(string $fullname): string {
        global $DB;

        $base = trim(\core_text::substr(clean_param($fullname, PARAM_TEXT), 0, 100));
        if ($base === '') {
            return '';
        }
        $shortname = $base;
        $number = 2;
        while ($DB->record_exists('course', ['shortname' => $shortname])) {
            $suffix = ' ' . $number++;
            $shortname = trim(\core_text::substr($base, 0, 100 - \core_text::strlen($suffix))) . $suffix;
        }
        return $shortname;
    }

    /**
     * Returns the steps of the dialog for amd/src/wizard.js.
     *
     * Steps without a usable element (e.g. no permission to choose the category) are
     * left out. Required fields Moodle needs in addition (e.g. required custom course
     * fields) and fields with a validation error are asked in an extra step. Steps with
     * a required field come first, after them the course can be created right away.
     *
     * @return array Steps and the index of the first step with a validation error (or null).
     */
    public function get_wizard_config(): array {
        $mform = $this->_form;

        $steps = [];
        $used = self::AUTOMATIC;
        foreach (self::STEPS as $key => $names) {
            // A disabled step keeps the defaults; required fields or errors in it are still asked in "more".
            if (isset(self::OPTIONALSTEPS[$key]) && !get_config('local_coursewizard', self::OPTIONALSTEPS[$key])) {
                continue;
            }
            $used = array_merge($used, $names);
            if ($elements = $this->export_elements($names)) {
                $steps[] = ['key' => $key, 'question' => get_string('step_' . $key, 'local_coursewizard'),
                    'elements' => $elements];
            }
        }

        $more = [];
        foreach ($mform->_elements as $element) {
            $name = $element->getName();
            if ($name !== '' && !in_array($name, $used) &&
                    ($mform->isElementRequired($name) || $mform->getElementError($name))) {
                $more[] = $name;
            }
        }
        if ($elements = $this->export_elements($more)) {
            $steps[] = ['key' => 'more', 'question' => get_string('step_more', 'local_coursewizard'),
                'elements' => $elements];
        }

        // Steps with a required field first: after them the course can be created right away.
        foreach ($steps as &$step) {
            $step['required'] = (bool) array_filter($step['elements'], fn($element) => $element['requiredmessage'] !== '');
            // Shown on request ("What is this for?") below the question.
            $step['explanation'] = get_string('explain_' . $step['key'], 'local_coursewizard');
        }
        unset($step);
        $steps = array_merge(
            array_values(array_filter($steps, fn($step) => $step['required'])),
            array_values(array_filter($steps, fn($step) => !$step['required']))
        );

        $errorstep = null;
        foreach ($steps as $index => $step) {
            foreach ($step['elements'] as $element) {
                if ($mform->getElementError($element['name'])) {
                    $errorstep = $errorstep ?? $index;
                }
            }
        }

        return ['steps' => $steps, 'errorstep' => $errorstep];
    }

    /**
     * Describes the given form elements for the dialog.
     *
     * @param array $names Element names.
     * @return array For each shown element: name, type, label and the message if it is required.
     */
    protected function export_elements(array $names): array {
        $mform = $this->_form;

        $elements = [];
        foreach ($names as $name) {
            if (!$mform->elementExists($name)) {
                continue;
            }
            $type = $mform->getElement($name)->getType();
            if (in_array($type, self::SKIPPEDTYPES)) {
                continue;
            }
            $label = trim(strip_tags((string) $mform->getElement($name)->getLabel()));
            $element = ['name' => $name, 'type' => $type, 'label' => $label, 'requiredmessage' => ''];
            // Moodle's "Search" does not say that a category is already selected and can be changed.
            if ($name === 'category') {
                $element['placeholder'] = get_string('searchcategory', 'local_coursewizard');
            }
            if ($mform->isElementRequired($name)) {
                $element['requiredmessage'] = get_string('required');
                foreach ($mform->_rules[$name] ?? [] as $rule) {
                    if ($rule['type'] === 'required' && $rule['message'] !== '') {
                        $element['requiredmessage'] = $rule['message'];
                    }
                }
            }
            $elements[] = $element;
        }
        return $elements;
    }
}
