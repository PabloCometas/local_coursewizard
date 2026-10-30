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
 * Event courses: a course that only offers sessions of mod_facetoface to sign up for.
 *
 * @package     local_coursewizard
 * @copyright   2026 cometas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursewizard\local;

/**
 * Optional integration: the dialog can create an event course instead of a course.
 *
 * An event course is what block_course_explorer lists as event only: guest access without
 * password and a visible face-to-face activity. The dialog sets it up in one go: guest access,
 * the activity (format "single activity", so the course opens the sign-up page) and its first
 * session. Without mod_facetoface or the guest enrolment method nothing changes.
 */
class events {
    /** @var string Course format of event courses: the course is its face-to-face activity. */
    public const FORMAT = 'singleactivity';

    /** @var array Capabilities to set up an event course, besides creating the course. */
    public const CAPABILITIES = ['enrol/guest:config', 'mod/facetoface:addinstance', 'mod/facetoface:editsessions'];

    /** @var int Places of a session if not given, as in the session form of mod_facetoface. */
    public const CAPACITY = 10;

    /**
     * Whether the current user can create event courses in a category.
     *
     * Event courses are offered only if enabled in the plugin settings (many sites have no
     * events with a date and place).
     *
     * The capabilities are checked in the category, or for the role the creator gets in the
     * new course (course creators usually have them only there, as teacher).
     *
     * @param \context $context Context of the category of the new course.
     * @return bool
     */
    public static function is_available(\context $context): bool {
        global $CFG;

        if (
            !get_config('local_coursewizard', 'events') ||
            !\core_component::get_component_directory('mod_facetoface') ||
            !file_exists($CFG->dirroot . '/mod/facetoface/lib.php') ||
            !enrol_is_enabled('guest') || get_config('enrol_guest', 'requirepassword') ||
            !\core_component::get_component_directory('format_' . self::FORMAT) ||
            get_config('format_' . self::FORMAT, 'disabled')
        ) {
            return false;
        }
        foreach (self::CAPABILITIES as $capability) {
            if (!get_capability_info($capability) || !creator::can($capability, $context)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Turns a new course into an event course: guest access, face-to-face activity, first session.
     *
     * The course was created with the format of event courses (see index.php).
     *
     * @param \stdClass $course The new course.
     * @param int $timestart Start of the first session.
     * @param int $timefinish End of the first session.
     * @param int $capacity Places of the first session.
     */
    public static function create(\stdClass $course, int $timestart, int $timefinish, int $capacity): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/facetoface/lib.php');

        $context = \context_course::instance($course->id);
        foreach (self::CAPABILITIES as $capability) {
            require_capability($capability, $context);
        }

        // Guest access without password: the session can be booked before enrolling (block_course_explorer).
        $guest = enrol_get_plugin('guest');
        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'guest'], '*', IGNORE_MULTIPLE);
        if (!$instance) {
            $instance = $DB->get_record('enrol', ['id' => $guest->add_instance($course)]);
        }
        $DB->update_record('enrol', (object) ['id' => $instance->id, 'password' => '']);
        $guest->update_status($instance, ENROL_INSTANCE_ENABLED);

        // The activity with the defaults of its form, in section 0 where the course format looks for it.
        $facetoface = (object) [
            'modulename' => 'facetoface',
            'course' => $course->id,
            'section' => 0,
            'visible' => 1,
            'name' => $course->fullname,
            'introeditor' => ['text' => (string) $course->summary, 'format' => $course->summaryformat ?? FORMAT_HTML,
                'itemid' => 0],
            'display' => 6,
            'signuptype' => MOD_FACETOFACE_SIGNUP_SINGLE,
            'showoncalendar' => F2F_CAL_COURSE,
            'usercalentry' => 1,
            'allowcancellationsdefault' => 1,
            'reminderperiod' => facetoface_get_default_reminderperiod(),
            'confirmationmessageformat' => FORMAT_HTML,
        ];
        foreach (
            ['requestsubject', 'requestmessage', 'requestinstrmngr', 'confirmationsubject', 'confirmationmessage',
                'confirmationinstrmngr', 'remindersubject', 'remindermessage', 'reminderinstrmngr', 'waitlistedsubject',
                'waitlistedmessage', 'waitlistedinstrmngr', 'cancellationsubject', 'cancellationmessage',
                'cancellationinstrmngr'] as $message
        ) {
            $facetoface->$message = facetoface_get_default_message($message);
        }
        $facetoface = create_module($facetoface);

        // The first session, as the sessions page of mod_facetoface saves it.
        $session = (object) [
            'facetoface' => $facetoface->instance,
            'visible' => 1,
            'datetimeknown' => 1,
            'capacity' => max(1, $capacity),
            'allowoverbook' => 0,
            'duration' => 0,
            'normalcost' => 0,
            'discountcost' => 0,
            'allowcancellations' => 1,
        ];
        facetoface_add_session($session, [(object) ['timestart' => $timestart, 'timefinish' => $timefinish]]);
    }
}
