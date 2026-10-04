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
 * Compare the audited 1.0.1 plugin with the configurable release in a disposable CI site.
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
if (!in_array($phase, ['prepare', 'verify'], true)) {
    cli_error('Expected prepare or verify.');
}
if ($phase === 'prepare') {
    set_config('enablecompletion', 1);
    $category = $DB->get_record('course_categories', ['parent' => 0], '*', MUST_EXIST);
    $fixtures = [
        ['visible' => 1, 'enablecompletion' => 1, 'startdate' => 2000000000, 'enddate' => 2100000000],
        ['visible' => 0, 'enablecompletion' => 0, 'startdate' => 0, 'enddate' => 0],
        ['visible' => 1, 'enablecompletion' => 1, 'startdate' => 2100000000, 'enddate' => 2000000000],
    ];
    foreach ($fixtures as $index => $fixture) {
        create_course((object) ($fixture + [
            'category' => $category->id,
            'shortname' => 'coursecoach-policy-' . $index,
            'fullname' => 'Synthetic policy upgrade course ' . $index,
            'newsitems' => 0,
        ]));
    }
}
$snapshot = [];
for ($index = 0; $index < 3; $index++) {
    $course = $DB->get_record('course', ['shortname' => 'coursecoach-policy-' . $index], '*', MUST_EXIST);
    $configbefore = $DB->get_records('config_plugins', ['plugin' => 'report_coursecoach'], 'id');
    $readiness = (new \report_coursecoach\course_analyser())->analyse($course);
    $results = [];
    foreach ($readiness->get_results() as $weighted) {
        $result = $weighted->get_result();
        $results[] = [
            'applicable' => $result->is_applicable(),
            'status' => $result->get_status(),
            'severity' => $result->get_severity(),
            'title' => $result->get_title(),
            'explanation' => $result->get_explanation(),
            'recommendation' => $result->get_recommendation(),
            'url' => $result->get_settings_url()?->out(false),
            'action' => $result->get_action_label(),
            'weight' => $weighted->get_weight(),
        ];
    }
    $snapshot[] = [
        'course' => $course,
        'results' => $results,
        'score' => $readiness->get_score(),
        'label' => $readiness->get_label(),
        'counts' => [$readiness->get_passed_count(), $readiness->get_warning_count(), $readiness->get_critical_count()],
    ];
    if ($course != $DB->get_record('course', ['id' => $course->id], '*', MUST_EXIST)) {
        cli_error('Analysis changed course configuration.');
    }
    if ($configbefore != $DB->get_records('config_plugins', ['plugin' => 'report_coursecoach'], 'id')) {
        cli_error('Analysis wrote plugin configuration.');
    }
}
$path = $CFG->dataroot . '/coursecoach-plugin-upgrade.json';
$json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
if ($phase === 'prepare') {
    file_put_contents($path, $json);
} else if (file_get_contents($path) !== $json) {
    cli_error('Default criteria changed the audited plugin results.');
}
cli_writeln('PASS: plugin ' . $phase . ' preserved results and read-only behaviour.');
