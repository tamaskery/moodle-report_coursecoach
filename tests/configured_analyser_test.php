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
 * Tests selection, defaults and configured analysis without course mutation.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Verifies configuration changes only the requested checks.
 *
 * @covers \report_coursecoach\course_analyser
 */
#[CoversClass(course_analyser::class)]
final class configured_analyser_test extends advanced_testcase {
    /**
     * Test default analysis equals the original explicitly ordered engine.
     */
    public function test_default_results_preserve_original_engine(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecompletion', 1);
        $course = $this->getDataGenerator()->create_course([
            'startdate' => 2000000000, 'enddate' => 2100000000, 'enablecompletion' => 1, 'newsitems' => 0,
        ]);
        $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $legacy = new course_analyser([
            new check\course_visibility(), new check\course_dates(), new check\course_completion(),
            new check\activity_completion_coverage(), new check\required_completion_activity_accessibility(),
            new check\quiz_pass_completion(), new check\quiz_question_randomisation(), new check\feedback_presence(),
            new check\incomplete_content(), new check\activity_date_alignment(),
        ]);
        $expected = $legacy->analyse($course);
        $this->assertEquals($expected, (new course_analyser())->analyse($course));
        $this->assertEquals($expected, (new course_analyser(null, new criteria_config([])))->analyse($course));
        $this->assertSame([1, 1, 3, 2, 3, 2, 1, 1, 1, 1], array_map(
            static fn(weighted_result $item): int => $item->get_weight(),
            $expected->get_results()
        ));
        $this->assertSame([], $expected->get_disabled_checks());
    }

    /**
     * Test each switch excludes exactly that result and its score contribution.
     */
    public function test_each_check_can_be_disabled_independently(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecompletion', 1);
        $course = $this->getDataGenerator()->create_course([
            'enablecompletion' => 1, 'startdate' => 2000000000, 'enddate' => 2100000000,
        ]);
        $baseline = (new course_analyser(null, new criteria_config([])))->analyse($course)->get_results();
        foreach (array_keys(criteria_config::get_definitions()) as $index => $id) {
            $config = new criteria_config(['enabled_' . $id => 0]);
            $actual = (new course_analyser(null, $config))->analyse($course);
            $expectedresults = $baseline;
            array_splice($expectedresults, $index, 1);
            $expected = (new readiness_calculator())->calculate($expectedresults, [$id]);
            $this->assertEquals($expected, $actual, $id);
        }
    }

    /**
     * Test all-disabled analysis invokes no course-dependent checker code.
     */
    public function test_all_disabled_does_not_read_a_course(): void {
        $settings = [];
        foreach (array_keys(criteria_config::get_definitions()) as $id) {
            $settings['enabled_' . $id] = 0;
        }
        // An empty object cannot be evaluated by the built-in checkers.
        $actual = (new course_analyser(null, new criteria_config($settings)))->analyse(new \stdClass());
        $this->assertSame([], $actual->get_results());
        $this->assertCount(10, $actual->get_disabled_checks());
        $this->assertSame(0, $actual->get_score());
        $this->assertSame(0, $actual->get_passed_count());
        $this->assertSame(0, $actual->get_warning_count());
        $this->assertSame(0, $actual->get_critical_count());
        $this->assertSame(readiness::LABEL_NOT_ASSESSED, $actual->get_label());
    }

    /**
     * Test supplied checker instances bypass site selection and configuration.
     */
    public function test_explicit_checker_list_is_preserved(): void {
        $this->resetAfterTest();
        set_config('enabled_course_visibility', 0, 'report_coursecoach');
        $result = new check\result(true, check\result::STATUS_PASSED, check\result::SEVERITY_RECOMMENDATION, 'X', 'Y', 'Z');
        $checker = $this->createMock(check\checker::class);
        $checker->expects($this->once())->method('check')->willReturn($result);
        $checker->expects($this->once())->method('get_weight')->willReturn(1);
        $actual = (new course_analyser([$checker]))->analyse(new \stdClass());
        $this->assertSame($result, $actual->get_results()[0]->get_result());
        $this->assertSame([], $actual->get_disabled_checks());
        $this->assertSame([], (new course_analyser([]))->analyse(new \stdClass())->get_results());
    }

    /**
     * Test site threshold reaches the checker without implicitly disabling related checks.
     */
    public function test_site_threshold_changes_only_coverage(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecompletion', 1);
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'completion' => 1]);
        $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $baseline = (new course_analyser(null, new criteria_config([])))->analyse($course);
        set_config('activity_completion_coverage_minpercent', 50, 'report_coursecoach');
        set_config('enabled_course_completion', 0, 'report_coursecoach');
        $configured = (new course_analyser())->analyse($course);
        $this->assertSame(check\result::STATUS_WARNING, $baseline->get_results()[3]->get_result()->get_status());
        $this->assertSame(check\result::STATUS_PASSED, $configured->get_results()[2]->get_result()->get_status());
        $this->assertCount(9, $configured->get_results());
        $this->assertSame(['course_completion'], $configured->get_disabled_checks());
    }
}
