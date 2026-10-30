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
 * Guided dialog for creating a new course.
 *
 * Creates the course exactly like course/edit.php does for a new course.
 *
 * @package     local_coursewizard
 * @copyright   2026 cometas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/edit_form.php');

use local_coursewizard\form\course_form;
use local_coursewizard\local\templates;

$categoryid = optional_param('category', 0, PARAM_INT);

require_login();
if ($categoryid) {
    $category = $DB->get_record('course_categories', ['id' => $categoryid], '*', MUST_EXIST);
} else {
    $category = core_course_category::get_default();
}
$catcontext = context_coursecat::instance($category->id);
require_capability('moodle/course:create', $catcontext);

$PAGE->set_url(new moodle_url('/local/coursewizard/index.php', ['category' => $category->id]));
$PAGE->set_context($catcontext);
$PAGE->set_pagelayout('admin');
// The dialog stands on its own: no category tabs, no page header (see styles.css).
$PAGE->set_secondary_navigation(false);
$PAGE->add_body_class('local-coursewizard-page');

$returnurl = new moodle_url('/course/index.php', ['categoryid' => $category->id]);

$editoroptions = ['maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false,
    'noclean' => true, 'context' => $catcontext, 'subdirs' => 0];
$course = file_prepare_standard_editor(null, 'summary', $editoroptions, null, 'course', 'summary', null);
if ($overviewfilesoptions = course_overviewfiles_options(null)) {
    file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, null, 'course', 'overviewfiles', 0);
}

$editform = new course_form(null, [
    'course' => $course,
    'category' => $category,
    'editoroptions' => $editoroptions,
    'returnto' => 0,
    'returnurl' => $returnurl,
], 'post', '', ['class' => 'coursewizard-form']);
$editform->set_display_vertical();

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $course = create_course($data, $editoroptions);

    // Enrol the creator like course/edit.php does.
    $context = context_course::instance($course->id, MUST_EXIST);
    if (is_siteadmin($USER->id)) {
        $enroluser = $CFG->enroladminnewcourse;
    } else {
        $enroluser = !is_viewing($context, null, 'moodle/role:assign');
    }
    if (!empty($CFG->creatornewroleid) && $enroluser && !is_enrolled($context, null, 'moodle/role:assign')) {
        enrol_try_internal_enrol($course->id, $USER->id, $CFG->creatornewroleid);
    }

    $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
    if (empty($data->templateid)) {
        redirect($courseurl);
    }

    // Fill the new course with the chosen template (local_coursetemplatewizard), then open it.
    $template = get_course($data->templateid);
    templates::apply($course, (int) $template->id);
    redirect($courseurl, get_string('templateapplied', 'local_coursewizard', format_string($template->fullname)),
        null, \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->set_primary_active_tab('home');
$PAGE->set_title(get_string('addnewcourse'));
$PAGE->add_body_class('limitedwidth');
$PAGE->set_heading(format_string($category->name));

$config = $editform->get_wizard_config();
$config['done'] = get_string('step_done', 'local_coursewizard', $USER->firstname);
// After the required steps the dialog offers to create the course or to add the optional details.
$details = [];
foreach ($config['steps'] as $step) {
    if (!$step['required']) {
        $details = array_merge($details, array_column($step['elements'], 'label'));
    }
}
$config['decision'] = get_string('decision', 'local_coursewizard',
    (object) ['name' => $USER->firstname, 'details' => implode(', ', array_filter($details))]);
$config['strings'] = [
    'later' => get_string('later', 'local_coursewizard'),
    'none' => get_string('none'),
    'yes' => get_string('yes'),
    'no' => get_string('no'),
];
$PAGE->requires->js_call_amd('local_coursewizard/wizard', 'init', ['#local-coursewizard']);

echo $OUTPUT->header();
// The title is the header line of the chat window (templates/wizard.mustache).
echo $OUTPUT->render_from_template('local_coursewizard/wizard', [
    'title' => get_string('addnewcourse'),
    'greeting' => get_string('greeting', 'local_coursewizard', $USER->firstname),
    'config' => json_encode($config),
    'form' => $editform->render(),
]);
echo $OUTPUT->footer();
