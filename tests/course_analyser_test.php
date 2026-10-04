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
 * Integration tests for read-only course analysis.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;
use completion_info;

/**
 * Verifies that the complete report leaves configuration and learner data unchanged.
 */
#[CoversClass(\report_coursecoach\course_analyser::class)]
final class course_analyser_test extends advanced_testcase {
    /**
     * Test repeated analysis preserves course settings and existing completion data.
     */
    public function test_analysis_preserves_configuration_and_learner_records(): void {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecompletion', 1);
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');

        $generator = $this->getDataGenerator();
        $course = $generator->create_course([
            'enablecompletion' => 1,
            'newsitems' => 0,
            'startdate' => 2_000_000_000,
            'enddate' => 2_100_000_000,
        ]);
        $quiz = $generator->create_module('quiz', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);
        $page = $generator->create_module('page', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);
        $generator->create_module('assign', ['course' => $course->id]);
        $generator->create_module('feedback', ['course' => $course->id]);
        (new \completion_criteria_activity())->update_config((object) [
            'id' => $course->id,
            'criteria_activity' => [$quiz->cmid => 1],
        ]);
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $cm = get_fast_modinfo($course)->get_cm($page->cmid);
        (new completion_info($course))->update_state($cm, COMPLETION_COMPLETE, $student->id);

        $before = $this->snapshot();
        $analyser = new course_analyser();
        $first = $analyser->analyse($course);
        $second = $analyser->analyse($course);

        $this->assertCount(10, $first->get_results());
        $this->assertEquals($first, $second);
        $this->assertEquals($before, $this->snapshot());
    }

    /**
     * Snapshot configuration, grades, attempts and completion records in the isolated test database.
     *
     * @return array Records keyed by table name.
     */
    private function snapshot(): array {
        global $DB;

        $records = [];
        foreach (
            [
                'course', 'course_sections', 'course_modules', 'quiz', 'quiz_slots', 'assign', 'page', 'feedback',
                'grade_items', 'grade_grades', 'quiz_attempts', 'course_completion_criteria',
                'course_completion_aggr_methd', 'course_completions', 'course_completion_crit_compl',
                'course_modules_completion',
            ] as $table
        ) {
            $records[$table] = $DB->get_records($table, null, 'id');
        }
        return $records;
    }
}
