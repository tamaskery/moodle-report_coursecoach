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
 * Course Readiness Coach report page.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($course->id);
$url = new moodle_url('/report/coursecoach/index.php', ['id' => $course->id]);

$PAGE->set_url($url);
$PAGE->set_context($context);
require_login($course);
require_capability('report/coursecoach:view', $context);

$title = get_string('reporttitle', 'report_coursecoach');
$PAGE->set_pagelayout('report');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title, $url);

$config = \report_coursecoach\category_criteria::resolve((int) $course->category);
$analyser = new \report_coursecoach\course_analyser(null, $config);
$readiness = $analyser->analyse($course);
$report = new \report_coursecoach\output\report($readiness, $config);
$renderer = $PAGE->get_renderer('report_coursecoach');

echo $OUTPUT->header();
echo $renderer->render($report);
echo $OUTPUT->footer();
