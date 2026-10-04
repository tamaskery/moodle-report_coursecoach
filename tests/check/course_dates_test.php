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
 * Tests for the course date checker.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests reliable course date outcomes.
 */
#[CoversClass(\report_coursecoach\check\course_dates::class)]
final class course_dates_test extends advanced_testcase {
    /**
     * Test a valid start and end date.
     */
    public function test_valid_dates_pass(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'startdate' => 1_000,
            'enddate' => 2_000,
        ]);

        $result = (new course_dates(1_500))->check($course);

        $this->assertTrue($result->is_applicable());
        $this->assertSame(result::STATUS_PASSED, $result->get_status());
    }

    /**
     * Test an end date that is not later than the start date.
     */
    public function test_invalid_date_combination_is_critical(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'startdate' => 1_000,
            'enddate' => 2_000,
        ]);
        $course->startdate = 2_000;
        $course->enddate = 1_000;

        $result = (new course_dates(1_500))->check($course);

        $this->assertSame(result::STATUS_CRITICAL, $result->get_status());
        $this->assertSame(result::SEVERITY_CRITICAL, $result->get_severity());
    }

    /**
     * Test a past end date.
     */
    public function test_expired_end_date_warns(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'startdate' => 1_000,
            'enddate' => 2_000,
        ]);

        $result = (new course_dates(3_000))->check($course);

        $this->assertSame(result::STATUS_WARNING, $result->get_status());
    }

    /**
     * Test the supported edge case where neither date is configured.
     */
    public function test_missing_dates_are_not_applicable(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $course->startdate = 0;
        $course->enddate = 0;

        $result = (new course_dates(3_000))->check($course);

        $this->assertFalse($result->is_applicable());
        $this->assertSame(result::STATUS_NOT_APPLICABLE, $result->get_status());
    }
}
