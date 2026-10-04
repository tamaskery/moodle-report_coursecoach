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
 * Course Coach report renderable.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\output;

use report_coursecoach\check\result;
use report_coursecoach\criteria_config;
use report_coursecoach\readiness;
use renderable;
use renderer_base;
use templatable;

/**
 * Prepares readiness results for the Mustache report template.
 */
final class report implements renderable, templatable {
    /** @var readiness Calculated course readiness. */
    private readiness $readiness;

    /** @var criteria_config|null Policy used by the analyser, when available. */
    private ?criteria_config $config;

    /**
     * Constructor.
     *
     * @param readiness $readiness Calculated course readiness.
     * @param criteria_config|null $config Effective policy used for this report.
     */
    public function __construct(readiness $readiness, ?criteria_config $config = null) {
        $this->readiness = $readiness;
        $this->config = $config;
    }

    /**
     * Export report data for the template.
     *
     * @param renderer_base $output Renderer instance.
     * @return array Template data.
     */
    public function export_for_template(renderer_base $output): array {
        unset($output);

        $criticalchecks = [];
        $warningchecks = [];
        $passedchecks = [];
        $notapplicablechecks = [];
        $assessedcount = 0;
        foreach ($this->readiness->get_results() as $index => $weightedresult) {
            $result = $weightedresult->get_result();
            $settingsurl = $result->get_settings_url();
            $actionlabel = $result->get_action_label();
            $isissue = in_array($result->get_status(), [result::STATUS_CRITICAL, result::STATUS_WARNING], true);
            if ($result->is_applicable()) {
                $assessedcount++;
            }
            $check = [
                'index' => $index,
                'applicability' => $result->is_applicable(),
                'status' => $result->get_status(),
                'severity' => $result->get_severity(),
                'statuslabel' => get_string('status:' . $result->get_status(), 'report_coursecoach'),
                'statusclass' => $this->get_status_class($result->get_status()),
                'title' => $result->get_title(),
                'explanation' => $result->get_explanation(),
                'recommendation' => $result->get_recommendation(),
                'showrecommendation' => $isissue,
                'hassettingsurl' => $isissue && $settingsurl !== null && $actionlabel !== null,
                'settingsurl' => $settingsurl ? $settingsurl->out(false) : '',
                'actionlabel' => $actionlabel ?? '',
            ];

            switch ($result->get_status()) {
                case result::STATUS_CRITICAL:
                    $criticalchecks[] = $check;
                    break;
                case result::STATUS_WARNING:
                    $warningchecks[] = $check;
                    break;
                case result::STATUS_PASSED:
                    $passedchecks[] = $check;
                    break;
                case result::STATUS_NOT_APPLICABLE:
                    $notapplicablechecks[] = $check;
                    break;
            }
        }

        $score = $this->readiness->get_score();
        $totalcount = count($this->readiness->get_results());
        $definitions = criteria_config::get_definitions();
        $disabledchecks = [];
        foreach ($this->readiness->get_disabled_checks() as $id) {
            $disabledchecks[] = ['title' => get_string($definitions[$id]['title'], 'report_coursecoach')];
        }
        $assessedstring = $disabledchecks ? 'assessedenabledchecks' : 'assessedchecks';
        return [
            'policysource' => $this->get_policy_source(),
            'disabledheading' => get_string(
                $this->config && $this->config->get_source_category_id() ? 'disabledcategoryheading' : 'disabledchecksheading',
                'report_coursecoach'
            ),
            'hasdisabledchecks' => !empty($disabledchecks),
            'alldisabled' => $totalcount === 0 && !empty($disabledchecks),
            'disabledchecks' => $disabledchecks,
            'disabledcount' => count($disabledchecks),
            'score' => $score,
            'readinesslabel' => $this->get_readiness_label(),
            'readinessclass' => $this->get_readiness_class(),
            'passedcount' => $this->readiness->get_passed_count(),
            'warningcount' => $this->readiness->get_warning_count(),
            'criticalcount' => $this->readiness->get_critical_count(),
            'assessedcount' => $assessedcount,
            'totalcount' => $totalcount,
            'assessedchecks' => get_string($assessedstring, 'report_coursecoach', (object) [
                'assessed' => $assessedcount,
                'total' => $totalcount,
            ]),
            'hasissues' => !empty($criticalchecks) || !empty($warningchecks),
            'issues' => array_merge($criticalchecks, $warningchecks),
            'haspassedchecks' => !empty($passedchecks),
            'passedchecks' => $passedchecks,
            'hasnotapplicablechecks' => !empty($notapplicablechecks),
            'notapplicablechecks' => $notapplicablechecks,
        ];
    }

    /**
     * Return a Moodle-native badge class for a check status.
     *
     * @param string $status Check status.
     * @return string CSS classes.
     */
    private function get_status_class(string $status): string {
        switch ($status) {
            case result::STATUS_PASSED:
                return 'bg-success';
            case result::STATUS_WARNING:
                return 'bg-warning text-dark';
            case result::STATUS_CRITICAL:
                return 'bg-danger';
            default:
                return 'bg-secondary text-dark';
        }
    }

    /**
     * Describe the actual policy without adding administration links for teachers.
     *
     * @return string Plain text for escaped template output.
     */
    private function get_policy_source(): string {
        global $DB;
        if ($this->config === null) {
            return '';
        }
        $categoryid = $this->config->get_source_category_id();
        if (!$categoryid) {
            return get_string('policysourcesite', 'report_coursecoach');
        }
        $name = $DB->get_field('course_categories', 'name', ['id' => $categoryid], MUST_EXIST);
        $name = format_string($name, true, ['context' => \context_coursecat::instance($categoryid)]);
        $name = html_entity_decode(strip_tags($name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return get_string('policysourcecategory', 'report_coursecoach', $name);
    }

    /**
     * Return the localised readiness label.
     *
     * @return string
     */
    private function get_readiness_label(): string {
        switch ($this->readiness->get_label()) {
            case readiness::LABEL_READY:
                return get_string('ready', 'report_coursecoach');
            case readiness::LABEL_NEEDS_ATTENTION:
                return get_string('needsattention', 'report_coursecoach');
            case readiness::LABEL_NOT_READY:
                return get_string('notready', 'report_coursecoach');
            default:
                return get_string('notassessed', 'report_coursecoach');
        }
    }

    /**
     * Return a Moodle-native badge class for the readiness label.
     *
     * @return string CSS classes.
     */
    private function get_readiness_class(): string {
        switch ($this->readiness->get_label()) {
            case readiness::LABEL_READY:
                return 'bg-success';
            case readiness::LABEL_NEEDS_ATTENTION:
                return 'bg-warning text-dark';
            case readiness::LABEL_NOT_READY:
                return 'bg-danger';
            default:
                return 'bg-secondary text-dark';
        }
    }
}
