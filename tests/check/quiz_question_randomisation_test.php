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
 * Tests for quiz question randomisation.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;
use mod_quiz\quiz_settings;

/**
 * Tests the quiz question randomisation checker.
 *
 * @covers \report_coursecoach\check\quiz_question_randomisation
 */
#[CoversClass(\report_coursecoach\check\quiz_question_randomisation::class)]
final class quiz_question_randomisation_test extends advanced_testcase {
    /** @var \stdClass|null Quiz created for the current test. */
    private ?\stdClass $quiz = null;

    /**
     * Set up completion tracking.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecompletion', 1);
    }

    /**
     * Test a completion quiz with a random question passes.
     */
    public function test_random_completion_quiz_passes(): void {
        $course = $this->create_course_with_quiz();
        $quiz = $this->quiz;
        $category = $this->getDataGenerator()->get_plugin_generator('core_question')->create_question_category();
        $this->getDataGenerator()->get_plugin_generator('core_question')->create_question('truefalse', null, [
            'category' => $category->id,
        ]);
        quiz_settings::create($quiz->id)->get_structure()->add_random_questions(0, 1, [
            'filter' => ['category' => [
                'jointype' => \core_question\local\bank\condition::JOINTYPE_DEFAULT,
                'values' => [$category->id],
                'filteroptions' => ['includesubcategories' => false],
            ]],
        ]);

        $this->assertSame(result::STATUS_PASSED, (new quiz_question_randomisation())->check($course)->get_status());
    }

    /**
     * Test a completion quiz with only fixed questions warns.
     */
    public function test_fixed_completion_quiz_warns(): void {
        $course = $this->create_course_with_quiz();

        $this->assertSame(result::STATUS_WARNING, (new quiz_question_randomisation())->check($course)->get_status());
    }

    /**
     * Test an unrelated quiz is not assessed.
     */
    public function test_unrelated_quiz_is_not_applicable(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);

        $this->assertSame(result::STATUS_NOT_APPLICABLE, (new quiz_question_randomisation())->check($course)->get_status());
    }

    /**
     * Test no completion-relevant quiz is not applicable.
     */
    public function test_no_relevant_quiz_is_not_applicable(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);

        $this->assertSame(result::STATUS_NOT_APPLICABLE, (new quiz_question_randomisation())->check($course)->get_status());
    }

    /**
     * Create a course and a quiz required for course completion.
     *
     * @return \stdClass Course record.
     */
    private function create_course_with_quiz(): \stdClass {
        global $CFG;

        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'newsitems' => 0]);
        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);
        $this->quiz = $quiz;
        $data = (object) ['id' => $course->id, 'criteria_activity' => [$quiz->cmid => 1]];
        (new \completion_criteria_activity())->update_config($data);

        return $course;
    }
}
