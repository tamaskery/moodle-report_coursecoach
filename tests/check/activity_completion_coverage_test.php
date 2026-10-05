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
 * Tests for activity completion coverage.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests Moodle-defined activity completion applicability and configuration.
 *
 * @covers \report_coursecoach\check\activity_completion_coverage
 */
#[CoversClass(\report_coursecoach\check\activity_completion_coverage::class)]
final class activity_completion_coverage_test extends advanced_testcase {
    /**
     * Enable completion tracking for each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecompletion', 1);
    }

    /**
     * Test configured completion for a completion-relevant activity passes.
     */
    public function test_configured_activity_completion_passes(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);

        $result = (new activity_completion_coverage())->check($course);

        $this->assertTrue($result->is_applicable());
        $this->assertSame(result::STATUS_PASSED, $result->get_status());
    }

    /**
     * Test an applicable assessed activity without completion is reported.
     */
    public function test_unconfigured_activity_completion_warns(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $assignment = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Reading & Writing',
        ]);

        $result = (new activity_completion_coverage())->check($course);

        $this->assertSame(result::STATUS_WARNING, $result->get_status());
        $this->assertStringContainsString($assignment->name, $result->get_explanation());
        $this->assertStringNotContainsString('Reading &amp; Writing', $result->get_explanation());
    }

    /**
     * Test passive resources are not flagged solely because they track views.
     */
    public function test_passive_resources_do_not_create_warning(): void {
        foreach (['page', 'url', 'folder', 'resource'] as $modname) {
            $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
            $this->getDataGenerator()->create_module($modname, ['course' => $course->id]);

            $result = (new activity_completion_coverage())->check($course);

            $this->assertFalse($result->is_applicable(), $modname);
            $this->assertSame(result::STATUS_NOT_APPLICABLE, $result->get_status(), $modname);
        }
    }

    /**
     * Test a course without meaningful activities is not applicable.
     */
    public function test_no_meaningful_activities_is_not_applicable(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);

        $result = (new activity_completion_coverage())->check($course);

        $this->assertFalse($result->is_applicable());
        $this->assertSame(result::STATUS_NOT_APPLICABLE, $result->get_status());
    }

    /**
     * Test a hidden activity without completion does not create a warning.
     */
    public function test_hidden_activity_does_not_create_warning(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'visible' => 0,
        ]);

        $result = (new activity_completion_coverage())->check($course);

        $this->assertSame(result::STATUS_NOT_APPLICABLE, $result->get_status());
    }
    /**
     * Test exact coverage boundaries without rounded-percentage decisions.
     */
    public function test_configured_percentage_boundaries(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id, 'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => 1,
        ]);
        $missing = $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'name' => 'Missing & needed']);
        foreach ([1, 50, 66] as $threshold) {
            $result = (new activity_completion_coverage($threshold))->check($course);
            $this->assertSame(result::STATUS_PASSED, $result->get_status());
            $this->assertStringContainsString('2 of 3', $result->get_explanation());
            $this->assertStringContainsString($threshold . '%', $result->get_explanation());
            $this->assertNull($result->get_settings_url());
        }
        foreach ([67, 80, 100] as $threshold) {
            $result = (new activity_completion_coverage($threshold))->check($course);
            $this->assertSame(result::STATUS_WARNING, $result->get_status());
            $this->assertSame(result::SEVERITY_IMPORTANT, $result->get_severity());
            $this->assertStringContainsString('Missing & needed', $result->get_explanation());
            $this->assertSame((string) $missing->cmid, $result->get_settings_url()->get_param('update'));
        }
        $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $this->assertSame(result::STATUS_PASSED, (new activity_completion_coverage(75))->check($course)->get_status());
        $this->assertSame(result::STATUS_WARNING, (new activity_completion_coverage(76))->check($course)->get_status());
    }

    /**
     * Test configuration does not change applicability or include hidden eligible activities.
     */
    public function test_threshold_preserves_applicability_and_hidden_exclusion(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $this->assertSame(result::STATUS_NOT_APPLICABLE, (new activity_completion_coverage(1))->check($course)->get_status());
        $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'visible' => 0]);
        $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $this->assertSame(result::STATUS_PASSED, (new activity_completion_coverage(100))->check($course)->get_status());
        $course->enablecompletion = 0;
        $this->assertSame(result::STATUS_NOT_APPLICABLE, (new activity_completion_coverage(1))->check($course)->get_status());
    }

    /**
     * Test invalid injected thresholds fail before evaluation.
     */
    public function test_invalid_threshold_is_rejected(): void {
        $this->expectException(\coding_exception::class);
        new activity_completion_coverage(0);
    }
}
