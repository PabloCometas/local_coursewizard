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
 * Course templates of local_coursetemplatewizard (oncampus), if that plugin is installed.
 *
 * @package     local_coursewizard
 * @copyright   2026 cometas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursewizard\local;

use core_course\external\course_summary_exporter;

/**
 * Optional integration: the dialog offers the templates of local_coursetemplatewizard and
 * fills the new course with the chosen one. Without that plugin nothing changes.
 */
class templates {

    /** @var string Capability of local_coursetemplatewizard to overwrite a course with a template. */
    public const CAPABILITY = 'local/coursetemplatewizard:use';

    /**
     * Whether local_coursetemplatewizard is installed and has a template category.
     *
     * @return int Id of the template category, 0 if templates are not available.
     */
    public static function get_categoryid(): int {
        if (!class_exists('\local_coursetemplatewizard\template_utilization_manager') ||
                !get_capability_info(self::CAPABILITY)) {
            return 0;
        }
        return (int) get_config('local_coursetemplatewizard', 'templatecoursecategoryid');
    }

    /**
     * Returns the template courses the current user may use for a new course in a category.
     *
     * The capability is checked in the category, or for the role the creator gets in the new
     * course (course creators usually have it only there, as teacher).
     *
     * @param \context $context Context of the category of the new course.
     * @return array Objects with id, fullname, image (URL) and url (to view the template).
     */
    public static function get_courses(\context $context): array {
        global $CFG, $DB;

        $categoryid = self::get_categoryid();
        if (!$categoryid) {
            return [];
        }
        $allowed = has_capability(self::CAPABILITY, $context) || (!empty($CFG->creatornewroleid) &&
            $DB->record_exists('role_capabilities', ['roleid' => $CFG->creatornewroleid, 'capability' => self::CAPABILITY,
                'permission' => CAP_ALLOW, 'contextid' => \context_system::instance()->id]));
        if (!$allowed) {
            return [];
        }

        $templates = [];
        foreach ($DB->get_records('course', ['category' => $categoryid], 'sortorder') as $course) {
            $image = course_summary_exporter::get_course_image($course);
            $templates[] = (object) [
                'id' => (int) $course->id,
                'fullname' => format_string($course->fullname, true, ['context' => \context_course::instance($course->id)]),
                'image' => $image ?: course_summary_exporter::get_course_pattern($course),
                'url' => (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
            ];
        }
        return $templates;
    }

    /**
     * Fills a new course with a template.
     *
     * local_coursetemplatewizard keeps name, short name, dates, visibility and enrolments of the
     * course and copies contents and course format of the template. The description and image
     * given in the dialog are kept too. The copy runs right away (a few seconds), so its progress
     * page and log output are not shown.
     *
     * @param \stdClass $course The new course.
     * @param int $templateid Id of the template course.
     */
    public static function apply(\stdClass $course, int $templateid): void {
        $context = \context_course::instance($course->id);
        require_capability(self::CAPABILITY, $context);

        // Description and image from the dialog, before the template replaces them.
        $summary = trim(strip_tags((string) $course->summary, '<img>')) !== '' ? $course->summary : null;
        $fs = get_file_storage();
        $images = [];
        foreach ($fs->get_area_files($context->id, 'course', 'overviewfiles', 0, 'filename', false) as $file) {
            $images[] = ['filename' => $file->get_filename(), 'content' => $file->get_content()];
        }

        $manager = new \local_coursetemplatewizard\template_utilization_manager();
        ob_start();
        try {
            $manager->replace_course_with_template_and_render_progress($context, (int) $course->id, $templateid);
        } finally {
            ob_end_clean();
        }

        if ($summary !== null) {
            update_course((object) ['id' => $course->id, 'summary' => $summary, 'summaryformat' => $course->summaryformat]);
        }
        if ($images) {
            $fs->delete_area_files($context->id, 'course', 'overviewfiles', 0);
            foreach ($images as $image) {
                $fs->create_file_from_string(['contextid' => $context->id, 'component' => 'course',
                    'filearea' => 'overviewfiles', 'itemid' => 0, 'filepath' => '/', 'filename' => $image['filename']],
                    $image['content']);
            }
        }
    }
}
