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
 * Tests for incomplete course content.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests the incomplete content checker.
 */
#[CoversClass(\report_coursecoach\check\incomplete_content::class)]
final class incomplete_content_test extends advanced_testcase {
    /**
     * Test a visible empty non-general section warns.
     */
    public function test_visible_empty_section_warns(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['newsitems' => 0, 'numsections' => 1]);
        $section = get_fast_modinfo($course)->get_section_info(1);
        course_update_section($course->id, $section, ['name' => 'Research & Writing']);

        $result = (new incomplete_content())->check($course);

        $this->assertSame(result::STATUS_WARNING, $result->get_status());
        $this->assertStringContainsString('Research & Writing', $result->get_explanation());
        $this->assertStringNotContainsString('Research &amp; Writing', $result->get_explanation());
    }

    /**
     * Test a section containing an activity passes.
     */
    public function test_section_with_content_passes(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['newsitems' => 0, 'numsections' => 1]);
        $this->getDataGenerator()->create_course_section(['course' => $course->id, 'section' => 1]);
        $this->getDataGenerator()->create_module('page', ['course' => $course->id, 'section' => 1]);

        $this->assertSame(result::STATUS_PASSED, (new incomplete_content())->check($course)->get_status());
    }

    /**
     * Test a hidden empty section is ignored.
     */
    public function test_hidden_empty_section_is_ignored(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['newsitems' => 0, 'numsections' => 1]);
        $section = $this->getDataGenerator()->create_course_section(['course' => $course->id, 'section' => 1]);
        course_update_section($course->id, $section, ['visible' => 0]);

        $this->assertSame(result::STATUS_PASSED, (new incomplete_content())->check($course)->get_status());
    }

    /**
     * Test the general section is not treated as incomplete.
     */
    public function test_empty_general_section_does_not_warn(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['newsitems' => 0, 'numsections' => 0]);

        $this->assertSame(result::STATUS_PASSED, (new incomplete_content())->check($course)->get_status());
    }
}
