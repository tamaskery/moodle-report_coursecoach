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
 * Tests for course navigation integration.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversFunction;
use context_course;
use context_system;
use navigation_node;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Verifies that the course report navigation honours its capability.
 */
#[CoversFunction('report_coursecoach_extend_navigation_course')]
final class navigation_test extends advanced_testcase {
    /**
     * Test editing teachers and managers see the report while students do not.
     */
    public function test_navigation_access_by_role(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $this->assert_navigation_visible($course, $context);

        $manager = $this->getDataGenerator()->create_user();
        $managerroles = get_archetype_roles('manager');
        $this->assertNotEmpty($managerroles);
        $managerrole = reset($managerroles);
        role_assign($managerrole->id, $manager->id, context_system::instance()->id);
        $this->setUser($manager);
        $this->assert_navigation_visible($course, $context);

        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);
        $navigation = navigation_node::create('Course');
        report_coursecoach_extend_navigation_course($navigation, $course, $context);

        $this->assertFalse($navigation->find('report_coursecoach', navigation_node::TYPE_SETTING));
    }

    /**
     * Assert that an authorised user receives the course report navigation node.
     *
     * @param \stdClass $course Course record.
     * @param context_course $context Course context.
     * @return void
     */
    private function assert_navigation_visible(\stdClass $course, context_course $context): void {
        $navigation = navigation_node::create('Course');
        report_coursecoach_extend_navigation_course($navigation, $course, $context);

        $node = $navigation->find('report_coursecoach', navigation_node::TYPE_SETTING);
        $this->assertInstanceOf(navigation_node::class, $node);
        $this->assertSame(
            (new \moodle_url('/report/coursecoach/index.php', ['id' => $course->id]))->out(false),
            $node->action->out(false)
        );
    }
}
