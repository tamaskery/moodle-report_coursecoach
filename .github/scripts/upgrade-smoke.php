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
 * Exercise the read-only report before and after a disposable CI site upgrade.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require(getcwd() . '/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');

\core\session\manager::set_user(get_admin());
$phase = $argv[1] ?? '';
if ($phase === 'prepare') {
    $category = $DB->get_record('course_categories', ['parent' => 0], '*', MUST_EXIST);
    create_course((object) [
        'category' => $category->id,
        'shortname' => 'coursecoach-upgrade',
        'fullname' => 'Course Coach upgrade test',
        'enablecompletion' => 1,
        'newsitems' => 0,
    ]);
} else if ($phase !== 'verify') {
    cli_error('Expected prepare or verify.');
}
$course = $DB->get_record('course', ['shortname' => 'coursecoach-upgrade'], '*', MUST_EXIST);
$before = clone $course;
$readiness = (new \report_coursecoach\course_analyser())->analyse($course);
if (count($readiness->get_results()) !== 10) {
    cli_error('The upgraded report did not run all ten checks.');
}
if ($before != $DB->get_record('course', ['id' => $course->id], '*', MUST_EXIST)) {
    cli_error('Course configuration changed during report analysis.');
}
cli_writeln('PASS: ' . $phase . ' ran all ten checks without changing course configuration.');
