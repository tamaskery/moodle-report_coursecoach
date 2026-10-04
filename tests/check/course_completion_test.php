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
 * Tests for the course completion checker.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests completion configuration through Moodle's completion API.
 *
 * @covers \report_coursecoach\check\course_completion
 */
#[CoversClass(\report_coursecoach\check\course_completion::class)]
final class course_completion_test extends advanced_testcase {
    /**
     * Test course completion disabled.
     */
    public function test_completion_disabled_is_critical(): void {
        global $CFG;

        $this->resetAfterTest();
        $CFG->enablecompletion = 1;
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 0]);

        $result = (new course_completion())->check($course);

        $this->assertSame(result::STATUS_CRITICAL, $result->get_status());
        $this->assertNotNull($result->get_settings_url());
    }

    /**
     * Test completion enabled without criteria.
     */
    public function test_completion_without_criteria_warns(): void {
        global $CFG;

        $this->resetAfterTest();
        $CFG->enablecompletion = 1;
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);

        $result = (new course_completion())->check($course);

        $this->assertSame(result::STATUS_WARNING, $result->get_status());
        $this->assertNotNull($result->get_settings_url());
    }

    /**
     * Test completion enabled with a valid core completion criterion.
     */
    public function test_completion_with_criteria_passes(): void {
        global $CFG;

        $this->resetAfterTest();
        $CFG->enablecompletion = 1;
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_self.php');

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $criterion = new \completion_criteria_self();
        $data = (object) [
            'id' => $course->id,
            'criteria_self' => 1,
        ];
        $criterion->update_config($data);

        $result = (new course_completion())->check($course);

        $this->assertSame(result::STATUS_PASSED, $result->get_status());
    }
    /**
     * Test site-disabled completion remains Critical without a course settings action.
     */
    public function test_site_completion_disabled_is_critical(): void {
        $this->resetAfterTest();
        set_config('enablecompletion', 0);
        $course = $this->getDataGenerator()->create_course();
        $result = (new course_completion())->check($course);
        $this->assertSame(result::STATUS_CRITICAL, $result->get_status());
        $this->assertNull($result->get_settings_url());
    }
}
