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
 * Tests for checker result name formatting.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Verifies that name formatting produces semantic text for the output layer.
 *
 * @covers \report_coursecoach\check\name_formatter
 */
#[CoversClass(\report_coursecoach\check\name_formatter::class)]
final class name_formatter_test extends advanced_testcase {
    /**
     * Test activity formatting preserves context and defers HTML escaping.
     */
    public function test_activity_name_is_semantic_text(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['newsitems' => 0]);
        $activity = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Research & Review',
        ]);
        $cm = get_fast_modinfo($course)->get_cm($activity->cmid);

        $name = name_formatter::activity($cm);

        $this->assertSame('Research & Review', $name);
        $this->assertStringNotContainsString('&amp;', $name);
    }
}
