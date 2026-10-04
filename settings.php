<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Site-wide readiness criteria, saved by Moodle's standard admin settings controller.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig && $ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'report_coursecoach/criteria',
        get_string('criteria', 'report_coursecoach'),
        get_string('criteria_desc', 'report_coursecoach')
    ));
    foreach (\report_coursecoach\criteria_config::get_definitions() as $id => $definition) {
        $settings->add(new admin_setting_configcheckbox(
            'report_coursecoach/enabled_' . $id,
            get_string('enablecheck', 'report_coursecoach', get_string($definition['title'], 'report_coursecoach')),
            get_string('criteria_' . $id . '_desc', 'report_coursecoach'),
            \report_coursecoach\criteria_config::DEFAULT_ENABLED
        ));
        if ($id === 'activity_completion_coverage') {
            $percentages = [];
            foreach (range(1, 100) as $percentage) {
                $percentages[$percentage] = get_string('criteriapercent', 'report_coursecoach', $percentage);
            }
            $settings->add(new admin_setting_configselect(
                'report_coursecoach/activity_completion_coverage_minpercent',
                get_string('coverageminpercent', 'report_coursecoach'),
                get_string('coverageminpercent_desc', 'report_coursecoach'),
                \report_coursecoach\criteria_config::DEFAULT_MINPERCENT,
                $percentages
            ));
        }
    }
}
