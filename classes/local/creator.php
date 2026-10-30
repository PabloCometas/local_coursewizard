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
 * Capabilities the creator of a new course will have in it.
 *
 * @package     local_coursewizard
 * @copyright   2026 cometas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursewizard\local;

/**
 * Before a course exists, its capabilities can only be checked in its category. Course creators
 * usually get course capabilities only in the new course, through the role they are enrolled
 * with ($CFG->creatornewroleid).
 */
class creator {
    /**
     * Whether the current user has a capability in the category or will have it in the new course.
     *
     * @param string $capability
     * @param \context $context Context of the category of the new course.
     * @return bool
     */
    public static function can(string $capability, \context $context): bool {
        global $CFG, $DB;

        return has_capability($capability, $context) || (!empty($CFG->creatornewroleid) &&
            $DB->record_exists('role_capabilities', ['roleid' => $CFG->creatornewroleid, 'capability' => $capability,
                'permission' => CAP_ALLOW, 'contextid' => \context_system::instance()->id]));
    }
}
