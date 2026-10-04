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
 * Category policy inheritance, isolation and access tests.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Verifies category policy resolution without changing course data.
 *
 * @covers \report_coursecoach\category_criteria
 * @covers \report_coursecoach\course_analyser
 */
#[CoversClass(category_criteria::class)]
#[CoversClass(course_analyser::class)]
final class category_criteria_test extends \advanced_testcase {
    /**
     * Test nearest ancestor, independent branches, removal and category moves.
     */
    public function test_inheritance_and_moves(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $parent = $this->getDataGenerator()->create_category();
        $child = $this->getDataGenerator()->create_category(['parent' => $parent->id]);
        $leaf = $this->getDataGenerator()->create_category(['parent' => $child->id]);
        $other = $this->getDataGenerator()->create_category();
        set_config('activity_completion_coverage_minpercent', 90, 'report_coursecoach');
        $this->assertSame(90, category_criteria::resolve($leaf->id)->get_activity_completion_minpercent());
        $policy = (new criteria_config([]))->to_array();
        $policy['activity_completion_coverage_minpercent'] = 50;
        $policy['enabled_feedback_presence'] = 0;
        category_criteria::save($parent->id, $policy);
        $inherited = category_criteria::resolve($leaf->id);
        $this->assertSame((int) $parent->id, $inherited->get_source_category_id());
        $this->assertSame(50, $inherited->get_activity_completion_minpercent());
        $this->assertFalse($inherited->is_enabled('feedback_presence'));
        $this->assertSame(0, category_criteria::resolve($other->id)->get_source_category_id());
        $policy['activity_completion_coverage_minpercent'] = 75;
        category_criteria::save($child->id, $policy);
        $this->assertSame(75, category_criteria::resolve($leaf->id)->get_activity_completion_minpercent());
        category_criteria::save($child->id, null);
        $this->assertSame(50, category_criteria::resolve($leaf->id)->get_activity_completion_minpercent());
        $child->change_parent($other->id);
        $this->assertSame(90, category_criteria::resolve($leaf->id)->get_activity_completion_minpercent());
        $this->assertSame(50, $inherited->get_activity_completion_minpercent());
        category_criteria::save($parent->id, null);
        $this->assertFalse(get_config('report_coursecoach', 'category_' . $parent->id));
    }

    /**
     * Test invalid persisted overrides fall back to the nearest valid ancestor.
     */
    public function test_invalid_data_falls_back_without_writes(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $parent = $this->getDataGenerator()->create_category();
        $child = $this->getDataGenerator()->create_category(['parent' => $parent->id]);
        $policy = (new criteria_config([]))->to_array();
        $policy['enabled_course_dates'] = 0;
        category_criteria::save($parent->id, $policy);
        foreach (['broken', 'null', '[]', '{"enabled_course_dates":0}'] as $invalid) {
            set_config('category_' . $child->id, $invalid, 'report_coursecoach');
            $before = $DB->get_records('config_plugins', ['plugin' => 'report_coursecoach'], 'id');
            $resolved = category_criteria::resolve($child->id);
            $this->assertSame((int) $parent->id, $resolved->get_source_category_id());
            $this->assertFalse($resolved->is_enabled('course_dates'));
            $this->assertEquals($before, $DB->get_records('config_plugins', ['plugin' => 'report_coursecoach'], 'id'));
        }
        $this->assertSame(0, category_criteria::resolve(0)->get_source_category_id());
        $this->assertSame(0, category_criteria::resolve(999999)->get_source_category_id());
    }

    /**
     * Test validation rejects malformed thresholds, switches and unknown keys.
     */
    public function test_validation(): void {
        $policy = (new criteria_config([]))->to_array();
        $this->assertTrue(category_criteria::is_valid($policy));
        foreach ([0, 101, '80x', '80.5', '080', null, []] as $invalid) {
            $data = $policy;
            $data['activity_completion_coverage_minpercent'] = $invalid;
            $this->assertFalse(category_criteria::is_valid($data));
        }
        foreach ([-1, 2, 'false', [], null] as $invalid) {
            $data = $policy;
            $data['enabled_course_dates'] = $invalid;
            $this->assertFalse(category_criteria::is_valid($data));
        }
        $this->assertFalse(category_criteria::is_valid($policy + ['unknown' => 1]));
        $this->assertFalse(category_criteria::is_valid([]));
    }

    /**
     * Test a reused analyser resolves each course and follows course moves.
     */
    public function test_analysis_uses_current_category_without_mutation(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $category = $this->getDataGenerator()->create_category();
        $other = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $policy = array_fill_keys(array_keys((new criteria_config([]))->to_array()), 0);
        $policy['activity_completion_coverage_minpercent'] = 50;
        category_criteria::save($category->id, $policy);
        $before = $DB->get_record('course', ['id' => $course->id]);
        $analyser = new course_analyser();
        $actual = $analyser->analyse($course);
        $this->assertSame(readiness::LABEL_NOT_ASSESSED, $actual->get_label());
        $this->assertCount(10, $actual->get_disabled_checks());
        $this->assertEquals($before, $DB->get_record('course', ['id' => $course->id]));
        require_once($GLOBALS['CFG']->dirroot . '/course/lib.php');
        move_courses([$course->id], $other->id);
        $this->assertCount(10, $analyser->analyse(get_course($course->id))->get_results());
    }

    /**
     * Test course permission does not grant category policy writes.
     */
    public function test_teacher_cannot_save_policy(): void {
        $this->resetAfterTest();
        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'editingteacher');
        $this->setUser($user);
        $this->assertTrue(has_capability('report/coursecoach:view', \context_course::instance($course->id)));
        $this->expectException(\required_capability_exception::class);
        category_criteria::save($category->id, (new criteria_config([]))->to_array());
    }

    /**
     * Test invalid settings cannot be persisted even by administrators.
     */
    public function test_invalid_save_is_rejected(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $category = $this->getDataGenerator()->create_category();
        $this->expectException(\invalid_parameter_exception::class);
        category_criteria::save($category->id, []);
    }

    /**
     * Test policy disclosure escapes category names through the normal template.
     */
    public function test_policy_source_output(): void {
        global $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        $category = $this->getDataGenerator()->create_category(['name' => 'Research & Training']);
        $readiness = (new readiness_calculator())->calculate([]);
        $renderer = $PAGE->get_renderer('report_coursecoach');
        $report = new output\report($readiness, new criteria_config([], (int) $category->id));
        $data = $report->export_for_template($renderer);
        $this->assertStringContainsString('Research & Training', $data['policysource']);
        $this->assertSame('Disabled by category configuration', $data['disabledheading']);
        $html = $renderer->render($report);
        $this->assertStringContainsString('Research &amp; Training', $html);
        $this->assertStringNotContainsString('Research &amp;amp; Training', $html);
    }
}
