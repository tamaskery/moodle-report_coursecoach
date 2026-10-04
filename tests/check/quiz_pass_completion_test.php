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
 * Tests for quiz pass and completion configuration.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;
use grade_item;

/**
 * Tests completion-path quizzes using Moodle generators and grade settings.
 *
 * @covers \report_coursecoach\check\quiz_pass_completion
 */
#[CoversClass(\report_coursecoach\check\quiz_pass_completion::class)]
final class quiz_pass_completion_test extends advanced_testcase {
    /**
     * Enable completion tracking for each test.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        $this->resetAfterTest();
        $CFG->enablecompletion = true;
    }

    /**
     * Test a quiz with passing completion passes.
     */
    public function test_quiz_with_passing_completion_passes(): void {
        $course = $this->get_course_with_completion_quiz(70, true);

        $result = (new quiz_pass_completion())->check($course);

        $this->assertTrue($result->is_applicable());
        $this->assertSame(result::STATUS_PASSED, $result->get_status(), $result->get_explanation());
    }

    /**
     * Test a required quiz without a passing grade warns.
     */
    public function test_quiz_without_passing_grade_warns(): void {
        $course = $this->get_course_with_completion_quiz(0, true);

        $result = (new quiz_pass_completion())->check($course);

        $this->assertSame(result::STATUS_WARNING, $result->get_status());
        $this->assertStringContainsString('no passing grade', $result->get_explanation());
    }

    /**
     * Test a quiz with a grade to pass but no passing completion condition warns.
     */
    public function test_quiz_without_passing_completion_warns(): void {
        $course = $this->get_course_with_completion_quiz(70, false);

        $result = (new quiz_pass_completion())->check($course);

        $this->assertSame(result::STATUS_WARNING, $result->get_status());
        $this->assertStringContainsString('does not require', $result->get_explanation());
    }

    /**
     * Test an unrelated quiz is not incorrectly assessed.
     */
    public function test_quiz_not_used_for_course_completion_is_not_applicable(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);

        $result = (new quiz_pass_completion())->check($course);

        $this->assertSame(result::STATUS_NOT_APPLICABLE, $result->get_status());
    }

    /**
     * Test no quiz completion criterion is not applicable.
     */
    public function test_no_completion_quiz_is_not_applicable(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);

        $result = (new quiz_pass_completion())->check($course);

        $this->assertSame(result::STATUS_NOT_APPLICABLE, $result->get_status());
    }

    /**
     * Create a course with a quiz used as an activity completion criterion.
     *
     * @param int $gradepass Quiz grade to pass.
     * @param bool $requirepass Whether activity completion requires the pass grade.
     * @return \stdClass Course record.
     */
    private function get_course_with_completion_quiz(int $gradepass, bool $requirepass): \stdClass {
        global $CFG;

        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');
        require_once($CFG->libdir . '/gradelib.php');

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'name' => 'Final quiz',
            'completion' => $requirepass ? COMPLETION_TRACKING_AUTOMATIC : COMPLETION_TRACKING_MANUAL,
            'completionusegrade' => $requirepass ? 1 : 0,
            'completionpassgrade' => $requirepass ? 1 : 0,
        ]);
        $gradeitem = grade_item::fetch([
            'courseid' => $course->id,
            'itemtype' => 'mod',
            'itemmodule' => 'quiz',
            'iteminstance' => $quiz->id,
            'itemnumber' => 0,
        ]);
        $gradeitem->gradepass = $gradepass;
        $gradeitem->update();
        $data = (object) [
            'id' => $course->id,
            'criteria_activity' => [$quiz->cmid => 1],
        ];
        (new \completion_criteria_activity())->update_config($data);

        return $course;
    }
}
