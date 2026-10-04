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
 * Tests for the feedback presence check.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests the feedback presence checker.
 *
 * @covers \report_coursecoach\check\feedback_presence
 */
#[CoversClass(\report_coursecoach\check\feedback_presence::class)]
final class feedback_presence_test extends advanced_testcase {
    /**
     * Test visible Feedback passes.
     */
    public function test_visible_feedback_passes(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['newsitems' => 0]);
        $this->getDataGenerator()->create_module('feedback', ['course' => $course->id]);

        $this->assertSame(result::STATUS_PASSED, (new feedback_presence())->check($course)->get_status());
    }

    /**
     * Test no Feedback warns.
     */
    public function test_no_feedback_warns(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['newsitems' => 0]);

        $this->assertSame(result::STATUS_WARNING, (new feedback_presence())->check($course)->get_status());
    }

    /**
     * Test hidden Feedback does not count as available.
     */
    public function test_hidden_feedback_warns(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['newsitems' => 0]);
        $this->getDataGenerator()->create_module('feedback', ['course' => $course->id, 'visible' => 0]);

        $this->assertSame(result::STATUS_WARNING, (new feedback_presence())->check($course)->get_status());
    }
}
