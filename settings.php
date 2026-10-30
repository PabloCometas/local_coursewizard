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
 * Admin settings.
 *
 * @package     local_coursewizard
 * @copyright   2026 cometas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_coursewizard', new lang_string('pluginname', 'local_coursewizard'));
    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configcheckbox(
            'local_coursewizard/askdates',
            new lang_string('askdates', 'local_coursewizard'),
            new lang_string('askdates_desc', 'local_coursewizard'),
            0
        ));
    }
}
