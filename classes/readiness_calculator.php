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
 * Reusable weighted readiness calculator.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use coding_exception;
use report_coursecoach\check\result;

/**
 * Calculates a readiness score from applicable weighted results.
 */
final class readiness_calculator {
    /** @var float[] Score credit for each applicable status. */
    private const STATUS_CREDITS = [
        result::STATUS_PASSED => 1.0,
        result::STATUS_WARNING => 0.5,
        result::STATUS_CRITICAL => 0.0,
    ];

    /**
     * Calculate readiness.
     *
     * @param weighted_result[] $weightedresults Weighted checker results.
     * @param string[] $disabledchecks Disabled check identifiers for report disclosure only.
     * @return readiness Calculated readiness.
     */
    public function calculate(array $weightedresults, array $disabledchecks = []): readiness {
        $availableweight = 0;
        $earnedweight = 0.0;
        $counts = [
            result::STATUS_PASSED => 0,
            result::STATUS_WARNING => 0,
            result::STATUS_CRITICAL => 0,
        ];

        foreach ($weightedresults as $weightedresult) {
            if (!$weightedresult instanceof weighted_result) {
                throw new coding_exception('The readiness calculator requires weighted results.');
            }

            $result = $weightedresult->get_result();
            if (!$result->is_applicable()) {
                continue;
            }

            $weight = $weightedresult->get_weight();
            $status = $result->get_status();
            $availableweight += $weight;
            $earnedweight += $weight * self::STATUS_CREDITS[$status];
            $counts[$status]++;
        }

        if ($availableweight === 0) {
            return new readiness(
                $weightedresults,
                0,
                readiness::LABEL_NOT_ASSESSED,
                $counts[result::STATUS_PASSED],
                $counts[result::STATUS_WARNING],
                $counts[result::STATUS_CRITICAL],
                $disabledchecks
            );
        }

        $score = (int) round(($earnedweight / $availableweight) * 100);
        $label = $this->get_label($score, $counts);

        return new readiness(
            $weightedresults,
            $score,
            $label,
            $counts[result::STATUS_PASSED],
            $counts[result::STATUS_WARNING],
            $counts[result::STATUS_CRITICAL],
            $disabledchecks
        );
    }

    /**
     * Determine the overall readiness label.
     *
     * @param int $score Readiness score.
     * @param int[] $counts Result counts keyed by status.
     * @return string One of the readiness LABEL_* constants.
     */
    private function get_label(int $score, array $counts): string {
        if ($counts[result::STATUS_CRITICAL] > 0) {
            return readiness::LABEL_NOT_READY;
        }
        if ($counts[result::STATUS_WARNING] > 0 || $score < 100) {
            return readiness::LABEL_NEEDS_ATTENTION;
        }
        return readiness::LABEL_READY;
    }
}
