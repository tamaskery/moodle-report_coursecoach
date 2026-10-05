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
 * Course readiness analysis value object.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

/**
 * Contains calculated readiness and the underlying checker results.
 */
final class readiness {
    /** Ready label. */
    public const LABEL_READY = 'ready';

    /** Needs attention label. */
    public const LABEL_NEEDS_ATTENTION = 'needs_attention';

    /** Not ready label. */
    public const LABEL_NOT_READY = 'not_ready';

    /** Not assessed label. */
    public const LABEL_NOT_ASSESSED = 'not_assessed';

    /** @var weighted_result[] Weighted checker results. */
    private array $results;

    /** @var int Score from 0 to 100. */
    private int $score;

    /** @var string One of the LABEL_* constants. */
    private string $label;

    /** @var int Number of passed checks. */
    private int $passedcount;

    /** @var int Number of warnings. */
    private int $warningcount;

    /** @var int Number of critical issues. */
    private int $criticalcount;

    /** @var string[] Disabled check identifiers, separate from evaluated results. */
    private array $disabledchecks;

    /**
     * Constructor.
     *
     * @param weighted_result[] $results Weighted checker results.
     * @param int $score Score from 0 to 100.
     * @param string $label One of the LABEL_* constants.
     * @param int $passedcount Number of passed checks.
     * @param int $warningcount Number of warnings.
     * @param int $criticalcount Number of critical issues.
     * @param string[] $disabledchecks Disabled check identifiers.
     */
    public function __construct(
        array $results,
        int $score,
        string $label,
        int $passedcount,
        int $warningcount,
        int $criticalcount,
        array $disabledchecks = []
    ) {
        $this->results = $results;
        $this->score = $score;
        $this->label = $label;
        $this->passedcount = $passedcount;
        $this->warningcount = $warningcount;
        $this->criticalcount = $criticalcount;
        $this->disabledchecks = $disabledchecks;
    }

    /**
     * Return checks excluded by site configuration.
     *
     * @return string[] Disabled check identifiers.
     */
    public function get_disabled_checks(): array {
        return $this->disabledchecks;
    }

    /**
     * Return the weighted checker results.
     *
     * @return weighted_result[] Weighted checker results.
     */
    public function get_results(): array {
        return $this->results;
    }

    /**
     * Return the readiness score.
     *
     * @return int Score from 0 to 100.
     */
    public function get_score(): int {
        return $this->score;
    }

    /**
     * Return the readiness label identifier.
     *
     * @return string Readiness label identifier.
     */
    public function get_label(): string {
        return $this->label;
    }

    /**
     * Return the number of passed checks.
     *
     * @return int Number of passed checks.
     */
    public function get_passed_count(): int {
        return $this->passedcount;
    }

    /**
     * Return the number of warnings.
     *
     * @return int Number of warnings.
     */
    public function get_warning_count(): int {
        return $this->warningcount;
    }

    /**
     * Return the number of critical issues.
     *
     * @return int Number of critical issues.
     */
    public function get_critical_count(): int {
        return $this->criticalcount;
    }
}
