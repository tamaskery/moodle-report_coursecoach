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
 * Tests for required completion activity accessibility.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests deterministic hidden-state checks for completion criteria activities.
 */
#[CoversClass(\report_coursecoach\check\required_completion_activity_accessibility::class)]
final class required_completion_activity_accessibility_test extends advanced_testcase {
    /**
     * Enable completion tracking for each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
    }

    /**
     * Test a visible required activity passes.
     */
    public function test_visible_required_activity_passes(): void {
        $course = $this->get_course_with_required_page(true);

        $result = (new required_completion_activity_accessibility())->check($course);

        $this->assertTrue($result->is_applicable());
        $this->assertSame(result::STATUS_PASSED, $result->get_status());
    }

    /**
     * Test a hidden required activity is critical.
     */
    public function test_hidden_required_activity_is_critical(): void {
        $course = $this->get_course_with_required_page(false);

        $result = (new required_completion_activity_accessibility())->check($course);

        $this->assertSame(result::STATUS_CRITICAL, $result->get_status());
        $this->assertStringContainsString('Required page', $result->get_explanation());
    }

    /**
     * Test a hidden activity outside completion criteria does not trigger this check.
     */
    public function test_hidden_activity_that_is_not_required_is_not_applicable(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $this->getDataGenerator()->create_module('page', ['course' => $course->id, 'visible' => 0]);

        $result = (new required_completion_activity_accessibility())->check($course);

        $this->assertSame(result::STATUS_NOT_APPLICABLE, $result->get_status());
    }

    /**
     * Test no activity criterion is not applicable.
     */
    public function test_no_required_activities_is_not_applicable(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);

        $result = (new required_completion_activity_accessibility())->check($course);

        $this->assertSame(result::STATUS_NOT_APPLICABLE, $result->get_status());
    }

    /**
     * Create a course with one page set as a completion criterion.
     *
     * @param bool $visible Whether the page is visible to learners.
     * @return \stdClass Course record.
     */
    private function get_course_with_required_page(bool $visible): \stdClass {
        global $CFG;

        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $page = $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'name' => 'Required page',
            'visible' => $visible,
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);
        $data = (object) [
            'id' => $course->id,
            'criteria_activity' => [$page->cmid => 1],
        ];
        (new \completion_criteria_activity())->update_config($data);

        return $course;
    }
}
