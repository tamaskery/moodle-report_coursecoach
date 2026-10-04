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
 * Tests for validated criteria configuration and native administration settings.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use advanced_testcase;
use coding_exception;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Verifies strict configuration loading and legacy defaults.
 *
 * @covers \report_coursecoach\criteria_config
 */
#[CoversClass(criteria_config::class)]
final class criteria_config_test extends advanced_testcase {
    /**
     * Test the exact catalogue and defaults without saved settings.
     */
    public function test_legacy_defaults_and_catalogue(): void {
        $config = new criteria_config([]);
        $this->assertSame([
            'course_visibility', 'course_dates', 'course_completion', 'activity_completion_coverage',
            'required_activity_visibility', 'quiz_pass_completion', 'quiz_question_randomisation',
            'feedback_presence', 'incomplete_content', 'activity_date_alignment',
        ], array_keys(criteria_config::get_definitions()));
        foreach (criteria_config::get_definitions() as $id => $definition) {
            $this->assertTrue($config->is_enabled($id));
            $this->assertTrue(is_subclass_of($definition['class'], check\checker::class));
            $this->assertTrue(get_string_manager()->string_exists($definition['title'], 'report_coursecoach'));
        }
        $this->assertSame(100, $config->get_activity_completion_minpercent());
    }

    /**
     * Test persisted settings and a configuration snapshot without writes.
     */
    public function test_site_settings_are_loaded_without_writes(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('enabled_course_visibility', 0, 'report_coursecoach');
        set_config('activity_completion_coverage_minpercent', 80, 'report_coursecoach');
        $before = $DB->get_records('config_plugins', ['plugin' => 'report_coursecoach'], 'id');
        $config = new criteria_config();
        $this->assertFalse($config->is_enabled('course_visibility'));
        $this->assertTrue($config->is_enabled('course_dates'));
        $this->assertSame(80, $config->get_activity_completion_minpercent());
        $this->assertEquals($before, $DB->get_records('config_plugins', ['plugin' => 'report_coursecoach'], 'id'));
        set_config('activity_completion_coverage_minpercent', 90, 'report_coursecoach');
        $this->assertSame(80, $config->get_activity_completion_minpercent());
        $this->assertSame(90, (new criteria_config())->get_activity_completion_minpercent());
    }

    /**
     * Test explicit off values and malformed values without permissive casts.
     */
    public function test_enabled_values_are_validated(): void {
        foreach ([0, '0', false] as $value) {
            $this->assertFalse((new criteria_config(['enabled_course_dates' => $value]))->is_enabled('course_dates'));
        }
        foreach ([1, '1', true, null, '', 'false', 'off', -1, 0.0, [], 'unknown'] as $value) {
            $this->assertTrue((new criteria_config(['enabled_course_dates' => $value]))->is_enabled('course_dates'));
        }
    }

    /**
     * Test percentages are whole numbers within the supported range.
     */
    public function test_percentages_are_validated(): void {
        foreach ([1, '1', 67, '80', 100, '100'] as $value) {
            $config = new criteria_config(['activity_completion_coverage_minpercent' => $value]);
            $this->assertSame((int) $value, $config->get_activity_completion_minpercent());
        }
        foreach ([0, '0', 101, -1, '67.5', '80x', ' 80', '080', false, null, [], 80.0] as $value) {
            $config = new criteria_config(['activity_completion_coverage_minpercent' => $value]);
            $this->assertSame(100, $config->get_activity_completion_minpercent());
        }
    }

    /**
     * Test arbitrary persisted keys cannot introduce a checker.
     */
    public function test_unknown_identifier_is_rejected(): void {
        $config = new criteria_config(['enabled_arbitrary' => 1]);
        $this->expectException(coding_exception::class);
        $config->is_enabled('arbitrary');
    }

    /**
     * Test Moodle registers settings with matching defaults and restricted access.
     */
    public function test_native_settings_defaults_validation_and_access(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->libdir . '/adminlib.php');
        $admin = admin_get_root(true, true);
        $page = $admin->locate('reportcoursecoach');
        $this->assertInstanceOf(\admin_settingpage::class, $page);
        $this->assertTrue($page->check_access());
        foreach (array_keys(criteria_config::get_definitions()) as $id) {
            $setting = $page->settings->{'report_coursecoachenabled_' . $id};
            $this->assertSame(criteria_config::DEFAULT_ENABLED, $setting->get_defaultsetting());
        }
        $threshold = $page->settings->{'report_coursecoachactivity_completion_coverage_minpercent'};
        $this->assertSame(criteria_config::DEFAULT_MINPERCENT, $threshold->get_defaultsetting());
        $threshold->write_setting('80');
        $this->assertSame('80', get_config('report_coursecoach', 'activity_completion_coverage_minpercent'));
        foreach (['0', '101', '80x', '80.5'] as $invalid) {
            $threshold->write_setting($invalid);
            $this->assertSame('80', get_config('report_coursecoach', 'activity_completion_coverage_minpercent'));
        }
        $teacher = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $this->assertTrue(has_capability('report/coursecoach:view', \context_course::instance($course->id)));
        $this->assertFalse($page->check_access());
    }
}
