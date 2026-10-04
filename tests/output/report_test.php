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
 * Tests for report output preparation.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\output;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;
use report_coursecoach\check\result;
use report_coursecoach\readiness_calculator;
use report_coursecoach\weighted_result;
use moodle_url;
use renderer_base;

/**
 * Verifies deterministic template grouping, counts, and presentation flags.
 *
 *
 * @covers \report_coursecoach\output\report
 */
#[CoversClass(\report_coursecoach\output\report::class)]
final class report_test extends advanced_testcase {
    /**
     * Test mixed results are grouped once with stable priority and checker order.
     */
    public function test_mixed_results_are_grouped_in_display_order_once(): void {
        $data = $this->export([
            $this->make_result('Warning one', result::STATUS_WARNING),
            $this->make_result('Passed one', result::STATUS_PASSED),
            $this->make_result('Critical one', result::STATUS_CRITICAL),
            $this->make_result('Warning two', result::STATUS_WARNING),
            $this->make_result('Not applicable one', result::STATUS_NOT_APPLICABLE),
            $this->make_result('Critical two', result::STATUS_CRITICAL),
            $this->make_result('Passed two', result::STATUS_PASSED),
        ]);

        $this->assertTrue($data['hasissues']);
        $this->assertTrue($data['haspassedchecks']);
        $this->assertTrue($data['hasnotapplicablechecks']);
        $this->assertSame(
            ['Critical one', 'Critical two', 'Warning one', 'Warning two'],
            array_column($data['issues'], 'title')
        );
        $this->assertSame(['Passed one', 'Passed two'], array_column($data['passedchecks'], 'title'));
        $this->assertSame(['Not applicable one'], array_column($data['notapplicablechecks'], 'title'));
        $this->assertSame([2, 5, 0, 3], array_column($data['issues'], 'index'));

        $titles = array_merge(
            array_column($data['issues'], 'title'),
            array_column($data['passedchecks'], 'title'),
            array_column($data['notapplicablechecks'], 'title')
        );
        $this->assertCount(7, $titles);
        $this->assertCount(7, array_unique($titles));
    }

    /**
     * Test compact results retain data but suppress recommendations and actions.
     */
    public function test_compact_results_suppress_recommendations_and_actions(): void {
        $url = new moodle_url('/course/edit.php', ['id' => 7]);
        $data = $this->export([
            $this->make_result('Passed', result::STATUS_PASSED, $url, 'Edit course'),
            $this->make_result('Not applicable', result::STATUS_NOT_APPLICABLE, $url, 'Edit course'),
        ]);

        foreach ([$data['passedchecks'][0], $data['notapplicablechecks'][0]] as $check) {
            $this->assertSame('Recommendation for ' . $check['title'], $check['recommendation']);
            $this->assertFalse($check['showrecommendation']);
            $this->assertFalse($check['hassettingsurl']);
        }
    }

    /**
     * Test only issue results with a complete deterministic action expose it.
     */
    public function test_issue_action_visibility_requires_url_and_label(): void {
        $url = new moodle_url('/course/edit.php', ['id' => 8]);
        $data = $this->export([
            $this->make_result('Warning with action', result::STATUS_WARNING, $url, 'Edit course'),
            $this->make_result('Critical without action', result::STATUS_CRITICAL),
        ]);

        $checksbytitle = array_column($data['issues'], null, 'title');
        $this->assertTrue($checksbytitle['Warning with action']['showrecommendation']);
        $this->assertTrue($checksbytitle['Warning with action']['hassettingsurl']);
        $this->assertStringContainsString('/course/edit.php', $checksbytitle['Warning with action']['settingsurl']);
        $this->assertSame('Edit course', $checksbytitle['Warning with action']['actionlabel']);
        $this->assertTrue($checksbytitle['Critical without action']['showrecommendation']);
        $this->assertFalse($checksbytitle['Critical without action']['hassettingsurl']);
    }

    /**
     * Test mixed applicability produces correct assessed and total counts.
     */
    public function test_mixed_applicability_summary_is_correct(): void {
        $data = $this->export([
            $this->make_result('Passed', result::STATUS_PASSED),
            $this->make_result('Warning', result::STATUS_WARNING),
            $this->make_result('Critical', result::STATUS_CRITICAL),
            $this->make_result('Not applicable', result::STATUS_NOT_APPLICABLE),
        ]);

        $this->assertSame(3, $data['assessedcount']);
        $this->assertSame(4, $data['totalcount']);
        $this->assertSame('3 of 4 checks assessed', $data['assessedchecks']);
        $this->assertSame(1, $data['passedcount']);
        $this->assertSame(1, $data['warningcount']);
        $this->assertSame(1, $data['criticalcount']);
    }

    /**
     * Test all-applicable Passed results suppress the issue and skipped sections.
     */
    public function test_all_applicable_ten_check_summary_has_no_issue_section(): void {
        $results = [];
        for ($index = 1; $index <= 10; $index++) {
            $results[] = $this->make_result('Passed ' . $index, result::STATUS_PASSED);
        }
        $data = $this->export($results);

        $this->assertFalse($data['hasissues']);
        $this->assertSame([], $data['issues']);
        $this->assertTrue($data['haspassedchecks']);
        $this->assertFalse($data['hasnotapplicablechecks']);
        $this->assertSame(10, $data['assessedcount']);
        $this->assertSame(10, $data['totalcount']);
        $this->assertSame('10 of 10 checks assessed', $data['assessedchecks']);
    }

    /**
     * Test normal Mustache variables safely perform the final HTML escaping.
     */
    public function test_report_template_safely_escapes_semantic_text(): void {
        global $PAGE;

        $this->resetAfterTest();
        $PAGE->set_url(new moodle_url('/'));
        $renderer = $PAGE->get_renderer('core');
        $data = $this->export([
            $this->make_result('Research "<Draft>" & Review', result::STATUS_WARNING),
        ]);

        $html = $renderer->render_from_template('report_coursecoach/report', $data);

        $this->assertStringContainsString('Research &quot;&lt;Draft&gt;&quot; &amp; Review', $html);
        $this->assertStringNotContainsString('Research "<Draft>" & Review', $html);
        $this->assertStringNotContainsString('&amp;amp;', $html);
    }

    /**
     * Test disabled checks remain separate from applicability and score counts.
     */
    public function test_disabled_checks_are_disclosed_separately(): void {
        $results = [
            new weighted_result($this->make_result('Passed', result::STATUS_PASSED), 1),
            new weighted_result($this->make_result('Not applicable', result::STATUS_NOT_APPLICABLE), 1),
        ];
        $readiness = (new readiness_calculator())->calculate($results, ['course_visibility']);
        $data = (new report($readiness))->export_for_template($this->createStub(renderer_base::class));
        $this->assertSame(100, $data['score']);
        $this->assertSame(1, $data['assessedcount']);
        $this->assertSame(2, $data['totalcount']);
        $this->assertSame(1, $data['disabledcount']);
        $this->assertSame('1 of 2 enabled checks assessed', $data['assessedchecks']);
        $this->assertSame([['title' => 'Course visibility']], $data['disabledchecks']);
        $this->assertTrue($data['hasdisabledchecks']);
        $this->assertFalse($data['alldisabled']);
        $this->assertCount(1, $data['notapplicablechecks']);
        $this->assertCount(1, $data['passedchecks']);
    }

    /**
     * Test the all-disabled report does not imply successful evaluation.
     */
    public function test_all_disabled_report_is_not_assessed(): void {
        global $PAGE;
        $this->resetAfterTest();
        $PAGE->set_url(new moodle_url('/'));
        $renderer = $PAGE->get_renderer('core');
        $ids = array_keys(\report_coursecoach\criteria_config::get_definitions());
        $readiness = (new readiness_calculator())->calculate([], $ids);
        $data = (new report($readiness))->export_for_template($renderer);
        $this->assertSame('Not assessed', $data['readinesslabel']);
        $this->assertTrue($data['alldisabled']);
        $this->assertFalse($data['hasnotapplicablechecks']);
        $this->assertFalse($data['haspassedchecks']);
        $html = $renderer->render_from_template('report_coursecoach/report', $data);
        $this->assertStringContainsString('Disabled by site configuration', $html);
        $this->assertStringContainsString('This course has not been assessed.', $html);
        $this->assertStringContainsString('0 of 0 enabled checks assessed', $html);
    }
    /**
     * Export template data for supplied results.
     *
     * @param result[] $results Check results in analyser order.
     * @return array Template data.
     */
    private function export(array $results): array {
        $weightedresults = array_map(
            static fn(result $result): weighted_result => new weighted_result($result, 1),
            $results
        );
        $readiness = (new readiness_calculator())->calculate($weightedresults);
        $renderer = $this->createStub(renderer_base::class);

        return (new report($readiness))->export_for_template($renderer);
    }

    /**
     * Create a deterministic check result for output tests.
     *
     * @param string $title Result title.
     * @param string $status Result status.
     * @param moodle_url|null $settingsurl Optional settings URL.
     * @param string|null $actionlabel Optional action label.
     * @return result Check result.
     */
    private function make_result(
        string $title,
        string $status,
        ?moodle_url $settingsurl = null,
        ?string $actionlabel = null
    ): result {
        $applicable = $status !== result::STATUS_NOT_APPLICABLE;
        $severity = $status === result::STATUS_CRITICAL
            ? result::SEVERITY_CRITICAL
            : result::SEVERITY_RECOMMENDATION;

        return new result(
            $applicable,
            $status,
            $severity,
            $title,
            'Explanation for ' . $title,
            'Recommendation for ' . $title,
            $settingsurl,
            $actionlabel
        );
    }
}
