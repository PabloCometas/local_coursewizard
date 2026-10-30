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
 * @package     local_coursewizard
 * @copyright   2026 cometas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$categoryid = optional_param('category', 0, PARAM_INT);

$category = $categoryid ? core_course_category::get($categoryid) : core_course_category::get_default();
$context = context_coursecat::instance($category->id);

require_login();
require_capability('moodle/course:create', $context);

$PAGE->set_url(new moodle_url('/local/coursewizard/index.php', ['category' => $category->id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_coursewizard'));
$PAGE->set_heading(get_string('pluginname', 'local_coursewizard'));
$PAGE->requires->js_call_amd('local_coursewizard/wizard', 'init', ['#local-coursewizard']);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_coursewizard/wizard', ['categoryid' => $category->id]);
echo $OUTPUT->footer();
