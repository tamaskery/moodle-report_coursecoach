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
}
