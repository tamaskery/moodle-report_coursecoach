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
 * Activity completion coverage check.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use completion_info;
use coding_exception;
use moodle_url;
use report_coursecoach\criteria_config;
use stdClass;

/**
 * Finds visible activities with Moodle-defined completion rules or grading.
 */
final class activity_completion_coverage implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 2;

    /** @var int Minimum configured activity percentage. */
    private int $minpercent;

    /**
     * Constructor.
     *
     * @param int $minpercent Minimum percentage from 1 to 100; defaults to the original rule.
     */
    public function __construct(int $minpercent = criteria_config::DEFAULT_MINPERCENT) {
        if ($minpercent < 1 || $minpercent > 100) {
            throw new coding_exception('Course Coach coverage must be between 1 and 100 percent.');
        }
        $this->minpercent = $minpercent;
    }

    /**
     * Analyse activity completion configuration without changing it.
     *
     * @param stdClass $course Course record.
     * @return result Check result.
     */
    public function check(stdClass $course): result {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $title = get_string('check:coverage:title', 'report_coursecoach');
        $completioninfo = new completion_info($course);
        if (!$completioninfo->is_enabled()) {
            return $this->not_applicable($title, 'check:coverage:disabled');
        }

        $applicable = [];
        $unconfigured = [];
        foreach (get_fast_modinfo($course)->get_cms() as $cm) {
            if (!$cm->visible || !$this->supports_meaningful_completion($cm->modname)) {
                continue;
            }

            $applicable[] = $cm;
            if ($completioninfo->is_enabled($cm) === COMPLETION_TRACKING_NONE) {
                $unconfigured[] = $cm;
            }
        }

        if (!$applicable) {
            return $this->not_applicable($title, 'check:coverage:none');
        }

        $coverage = (object) [
            'configured' => count($applicable) - count($unconfigured),
            'total' => count($applicable),
            'required' => $this->minpercent,
            'activities' => $this->activity_names($unconfigured),
        ];
        if (100 * $coverage->configured < $this->minpercent * $coverage->total) {
            return new result(
                true,
                result::STATUS_WARNING,
                result::SEVERITY_IMPORTANT,
                $title,
                $this->get_explanation(false, $coverage),
                get_string('check:coverage:warning:recommendation', 'report_coursecoach'),
                new moodle_url('/course/modedit.php', ['update' => $unconfigured[0]->id, 'return' => 0]),
                get_string('action:activitycompletion', 'report_coursecoach')
            );
        }

        return new result(
            true,
            result::STATUS_PASSED,
            result::SEVERITY_RECOMMENDATION,
            $title,
            $this->get_explanation(true, $coverage),
            get_string('check:coverage:passed:recommendation', 'report_coursecoach')
        );
    }

    /**
     * Describe the actual coverage while preserving the original default wording.
     *
     * @param bool $passed Whether the threshold is met.
     * @param stdClass $coverage Counts, required percentage and unconfigured activity names.
     * @return string Localised explanation.
     */
    private function get_explanation(bool $passed, stdClass $coverage): string {
        if ($this->minpercent !== 100) {
            $identifier = $passed ? 'check:coverage:threshold:passed' : 'check:coverage:threshold:warning';
            return get_string($identifier, 'report_coursecoach', $coverage);
        }
        if ($passed) {
            return get_string('check:coverage:passed:explanation', 'report_coursecoach', $coverage->total);
        }
        return get_string('check:coverage:warning:explanation', 'report_coursecoach', (object) [
            'count' => $coverage->total - $coverage->configured,
            'activities' => $coverage->activities,
        ]);
    }

    /**
     * Return the score weight.
     *
     * @return int
     */
    public function get_weight(): int {
        return self::WEIGHT;
    }

    /**
     * Determine whether a module has completion-relevant Moodle capabilities.
     *
     * @param string $modname Activity module name.
     * @return bool Whether the module is completion-relevant.
     */
    private function supports_meaningful_completion(string $modname): bool {
        return (bool) plugin_supports('mod', $modname, FEATURE_COMPLETION_HAS_RULES) ||
            (bool) plugin_supports('mod', $modname, FEATURE_GRADE_HAS_GRADE);
    }

    /**
     * Return a concise list of formatted activity names.
     *
     * @param array $activities Course-module information objects.
     * @return string Activity names.
     */
    private function activity_names(array $activities): string {
        $names = [];
        foreach ($activities as $cm) {
            $names[] = name_formatter::activity($cm);
        }

        return implode(', ', $names);
    }

    /**
     * Create a not-applicable result.
     *
     * @param string $title Result title.
     * @param string $identifier Explanation and recommendation string prefix.
     * @return result
     */
    private function not_applicable(string $title, string $identifier): result {
        return new result(
            false,
            result::STATUS_NOT_APPLICABLE,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string($identifier . ':explanation', 'report_coursecoach'),
            get_string($identifier . ':recommendation', 'report_coursecoach')
        );
    }
}
