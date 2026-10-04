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
 * Tests for activity date alignment.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests conservative Quiz and Assignment date comparisons.
 */
#[CoversClass(\report_coursecoach\check\activity_date_alignment::class)]
final class activity_date_alignment_test extends advanced_testcase {
    /**
     * Reset Moodle state before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Test activities without relevant dates are not applicable.
     */
    public function test_no_relevant_dates_is_not_applicable(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 0, 'timeclose' => 0]);
        $this->create_assignment($course, [
            'allowsubmissionsfromdate' => 0,
            'duedate' => 0,
            'cutoffdate' => 0,
        ]);

        $result = (new activity_date_alignment())->check($course);

        $this->assertFalse($result->is_applicable());
        $this->assertSame(result::STATUS_NOT_APPLICABLE, $result->get_status());
    }

    /**
     * Test an evaluated Quiz date inside the course timeline passes.
     */
    public function test_quiz_date_inside_timeline_passes(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 1_500, 'timeclose' => 0]);

        $this->assertSame(result::STATUS_PASSED, (new activity_date_alignment())->check($course)->get_status());
    }

    /**
     * Test the Moodle 5.3 quiz due date does not replace the access-window boundary.
     */
    public function test_quiz_due_date_does_not_replace_close_date(): void {
        global $DB;

        if (!$DB->get_manager()->field_exists(new \xmldb_table('quiz'), new \xmldb_field('duedate'))) {
            $this->markTestSkipped('Quiz due dates are unavailable on this Moodle version.');
        }
        $course = $this->create_course();
        $quiz = $this->create_quiz($course, ['timeopen' => 1_500, 'timeclose' => 0]);
        $DB->set_field('quiz', 'duedate', 2_500, ['id' => $quiz->id]);

        $this->assertSame(result::STATUS_PASSED, (new activity_date_alignment())->check($course)->get_status());
        $this->assertSame(2_500, (int) $DB->get_field('quiz', 'duedate', ['id' => $quiz->id]));
    }

    /**
     * Test a Quiz opening after the course end date warns.
     */
    public function test_quiz_open_after_course_end_warns(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 2_500, 'timeclose' => 0]);

        $this->assert_warning((new activity_date_alignment())->check($course));
    }

    /**
     * Test a Quiz opening exactly at the course end date warns.
     */
    public function test_quiz_open_at_course_end_warns(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 2_000, 'timeclose' => 0]);

        $this->assert_warning((new activity_date_alignment())->check($course));
    }

    /**
     * Test a Quiz closing before the course start date warns.
     */
    public function test_quiz_close_before_course_start_warns(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 0, 'timeclose' => 500]);

        $this->assert_warning((new activity_date_alignment())->check($course));
    }

    /**
     * Test a Quiz closing exactly at the course start date warns.
     */
    public function test_quiz_close_at_course_start_warns(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 0, 'timeclose' => 1_000]);

        $this->assert_warning((new activity_date_alignment())->check($course));
    }

    /**
     * Test a Quiz closing after the course end date does not warn.
     */
    public function test_quiz_close_after_course_end_does_not_warn(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 0, 'timeclose' => 2_500]);

        $this->assertSame(result::STATUS_PASSED, (new activity_date_alignment())->check($course)->get_status());
    }

    /**
     * Test a Quiz opening before the course start date does not warn.
     */
    public function test_quiz_open_before_course_start_does_not_warn(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 500, 'timeclose' => 0]);

        $this->assertSame(result::STATUS_PASSED, (new activity_date_alignment())->check($course)->get_status());
    }

    /**
     * Test an Assignment accepting submissions after the course end date warns.
     */
    public function test_assignment_open_after_course_end_warns(): void {
        $course = $this->create_course();
        $this->create_assignment($course, [
            'allowsubmissionsfromdate' => 2_500,
            'duedate' => 3_000,
            'cutoffdate' => 3_500,
        ]);

        $this->assert_warning((new activity_date_alignment())->check($course));
    }

    /**
     * Test an Assignment cut-off before the course start date warns.
     */
    public function test_assignment_cutoff_before_course_start_warns(): void {
        $course = $this->create_course();
        $this->create_assignment($course, [
            'allowsubmissionsfromdate' => 0,
            'duedate' => 400,
            'cutoffdate' => 500,
        ]);

        $this->assert_warning((new activity_date_alignment())->check($course));
    }

    /**
     * Test a due date after the course end date is deliberately ignored.
     */
    public function test_assignment_due_after_course_end_does_not_warn(): void {
        $course = $this->create_course();
        $this->create_assignment($course, [
            'allowsubmissionsfromdate' => 0,
            'duedate' => 2_500,
            'cutoffdate' => 0,
        ]);

        $this->assertSame(
            result::STATUS_NOT_APPLICABLE,
            (new activity_date_alignment())->check($course)->get_status()
        );
    }

    /**
     * Test a due date before the course start date is deliberately ignored.
     */
    public function test_assignment_due_before_course_start_does_not_warn(): void {
        $course = $this->create_course();
        $this->create_assignment($course, [
            'allowsubmissionsfromdate' => 0,
            'duedate' => 500,
            'cutoffdate' => 0,
        ]);

        $this->assertSame(
            result::STATUS_NOT_APPLICABLE,
            (new activity_date_alignment())->check($course)->get_status()
        );
    }

    /**
     * Test hidden misaligned activities are ignored.
     */
    public function test_hidden_misaligned_activities_are_ignored(): void {
        $course = $this->create_course();
        $this->create_quiz($course, [
            'timeopen' => 2_500,
            'timeclose' => 0,
            'visible' => 0,
        ]);
        $this->create_assignment($course, [
            'allowsubmissionsfromdate' => 2_500,
            'duedate' => 3_000,
            'cutoffdate' => 3_500,
            'visible' => 0,
        ]);

        $this->assertSame(
            result::STATUS_NOT_APPLICABLE,
            (new activity_date_alignment())->check($course)->get_status()
        );
    }

    /**
     * Test invalid in-memory course dates are left to the course date checker.
     */
    public function test_invalid_course_dates_are_not_applicable(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 2_500, 'timeclose' => 0]);
        $course->startdate = 2_000;
        $course->enddate = 1_000;

        $this->assertSame(
            result::STATUS_NOT_APPLICABLE,
            (new activity_date_alignment())->check($course)->get_status()
        );
    }

    /**
     * Test multiple affected activities produce one useful warning.
     */
    public function test_multiple_affected_activities_produce_one_warning(): void {
        $course = $this->create_course();
        $quiz = $this->create_quiz($course, [
            'name' => 'Late final quiz',
            'timeopen' => 2_500,
            'timeclose' => 0,
        ]);
        $assignment = $this->create_assignment($course, [
            'name' => 'Late final assignment',
            'allowsubmissionsfromdate' => 2_500,
            'duedate' => 3_000,
            'cutoffdate' => 3_500,
        ]);

        $result = (new activity_date_alignment())->check($course);

        $this->assert_warning($result);
        $this->assertStringContainsString($quiz->name, $result->get_explanation());
        $this->assertStringContainsString($assignment->name, $result->get_explanation());
        $this->assertSame((int) $quiz->cmid, (int) $result->get_settings_url()->get_param('update'));
    }

    /**
     * Test Assignment dates are skipped in relative-date courses.
     */
    public function test_relative_date_assignment_is_not_applicable(): void {
        set_config('enablecourserelativedates', 1);
        $course = $this->create_course(['relativedatesmode' => 1]);
        $this->create_assignment($course, [
            'allowsubmissionsfromdate' => 2_500,
            'duedate' => 3_000,
            'cutoffdate' => 3_500,
        ]);

        $this->assertSame(
            result::STATUS_NOT_APPLICABLE,
            (new activity_date_alignment())->check($course)->get_status()
        );
    }

    /**
     * Test a course without usable boundaries is not applicable.
     */
    public function test_no_course_boundaries_is_not_applicable(): void {
        $course = $this->create_course();
        $this->create_quiz($course, ['timeopen' => 2_500, 'timeclose' => 500]);
        $course->startdate = 0;
        $course->enddate = 0;

        $this->assertSame(
            result::STATUS_NOT_APPLICABLE,
            (new activity_date_alignment())->check($course)->get_status()
        );
    }

    /**
     * Test the check retains the required score weight.
     */
    public function test_weight_is_one(): void {
        $this->assertSame(1, (new activity_date_alignment())->get_weight());
    }

    /**
     * Create a course with deterministic boundaries.
     *
     * @param array $data Course overrides.
     * @return \stdClass Course record.
     */
    private function create_course(array $data = []): \stdClass {
        return $this->getDataGenerator()->create_course(array_merge([
            'startdate' => 1_000,
            'enddate' => 2_000,
            'newsitems' => 0,
        ], $data));
    }

    /**
     * Create a Quiz in the supplied course.
     *
     * @param \stdClass $course Course record.
     * @param array $data Quiz overrides.
     * @return \stdClass Quiz record.
     */
    private function create_quiz(\stdClass $course, array $data): \stdClass {
        return $this->getDataGenerator()->create_module('quiz', array_merge([
            'course' => $course->id,
        ], $data));
    }

    /**
     * Create an Assignment in the supplied course.
     *
     * @param \stdClass $course Course record.
     * @param array $data Assignment overrides.
     * @return \stdClass Assignment record.
     */
    private function create_assignment(\stdClass $course, array $data): \stdClass {
        return $this->getDataGenerator()->create_module('assign', array_merge([
            'course' => $course->id,
        ], $data));
    }

    /**
     * Assert a conservative warning result.
     *
     * @param result $result Check result.
     */
    private function assert_warning(result $result): void {
        $this->assertTrue($result->is_applicable());
        $this->assertSame(result::STATUS_WARNING, $result->get_status());
        $this->assertSame(result::SEVERITY_RECOMMENDATION, $result->get_severity());
    }
}
