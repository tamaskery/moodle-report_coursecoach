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
 * Tests for the readiness score calculator.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversClass;
use report_coursecoach\check\result;

/**
 * Tests scoring independently of presentation.
 */
#[CoversClass(\report_coursecoach\readiness_calculator::class)]
final class readiness_calculator_test extends advanced_testcase {
    /**
     * Test configured MVP weights and status credits.
     */
    public function test_weighted_score_and_counts(): void {
        $calculator = new readiness_calculator();
        $results = [
            new weighted_result($this->make_result(result::STATUS_PASSED), 1),
            new weighted_result($this->make_result(result::STATUS_WARNING), 1),
            new weighted_result($this->make_result(result::STATUS_CRITICAL), 3),
        ];

        $readiness = $calculator->calculate($results);

        $this->assertSame(30, $readiness->get_score());
        $this->assertSame(1, $readiness->get_passed_count());
        $this->assertSame(1, $readiness->get_warning_count());
        $this->assertSame(1, $readiness->get_critical_count());
        $this->assertSame(readiness::LABEL_NOT_READY, $readiness->get_label());
    }

    /**
     * Test that non-applicable checks do not lower the score.
     */
    public function test_non_applicable_checks_are_excluded(): void {
        $calculator = new readiness_calculator();
        $results = [
            new weighted_result($this->make_result(result::STATUS_PASSED), 1),
            new weighted_result($this->make_result(result::STATUS_NOT_APPLICABLE), 3),
        ];

        $readiness = $calculator->calculate($results);

        $this->assertSame(100, $readiness->get_score());
        $this->assertSame(readiness::LABEL_READY, $readiness->get_label());
    }

    /**
     * Test the ten-check configuration produces a full readiness score.
     */
    public function test_ten_check_weights_all_pass(): void {
        $calculator = new readiness_calculator();
        $results = [
            new weighted_result($this->make_result(result::STATUS_PASSED), 1),
            new weighted_result($this->make_result(result::STATUS_PASSED), 1),
            new weighted_result($this->make_result(result::STATUS_PASSED), 3),
            new weighted_result($this->make_result(result::STATUS_PASSED), 2),
            new weighted_result($this->make_result(result::STATUS_PASSED), 1),
            new weighted_result($this->make_result(result::STATUS_PASSED), 1),
            new weighted_result($this->make_result(result::STATUS_PASSED), 1),
            new weighted_result($this->make_result(result::STATUS_PASSED), 3),
            new weighted_result($this->make_result(result::STATUS_PASSED), 2),
            new weighted_result($this->make_result(result::STATUS_PASSED), 1),
        ];

        $readiness = $calculator->calculate($results);

        $this->assertSame(100, $readiness->get_score());
        $this->assertSame(10, $readiness->get_passed_count());
        $this->assertSame(readiness::LABEL_READY, $readiness->get_label());
    }

    /**
     * Create a minimal valid result for calculator tests.
     *
     * @param string $status Result status.
     * @return result
     */
    private function make_result(string $status): result {
        $applicable = $status !== result::STATUS_NOT_APPLICABLE;
        $severity = $status === result::STATUS_CRITICAL
            ? result::SEVERITY_CRITICAL
            : result::SEVERITY_RECOMMENDATION;

        return new result($applicable, $status, $severity, 'Title', 'Explanation', 'Recommendation');
    }
}
