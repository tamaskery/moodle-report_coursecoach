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
 * Tests for the course visibility checker.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests course visibility outcomes.
 */
#[CoversClass(\report_coursecoach\check\course_visibility::class)]
final class course_visibility_test extends advanced_testcase {
    /**
     * Test a visible course.
     */
    public function test_visible_course_passes(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['visible' => 1]);

        $result = (new course_visibility())->check($course);

        $this->assertTrue($result->is_applicable());
        $this->assertSame(result::STATUS_PASSED, $result->get_status());
        $this->assertNotNull($result->get_settings_url());
    }

    /**
     * Test a hidden course.
     */
    public function test_hidden_course_warns(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['visible' => 0]);

        $result = (new course_visibility())->check($course);

        $this->assertTrue($result->is_applicable());
        $this->assertSame(result::STATUS_WARNING, $result->get_status());
        $this->assertSame(result::SEVERITY_IMPORTANT, $result->get_severity());
    }
}
