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
 * Validated site criteria and the fixed catalogue of supported checks.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use coding_exception;

/**
 * Resolves site settings without writing defaults or modifying course data.
 */
final class criteria_config {
    /** Default minimum percentage, matching the original all-activities rule. */
    public const DEFAULT_MINPERCENT = 100;

    /** Default enabled state for every check. */
    public const DEFAULT_ENABLED = 1;

    /** @var array Fixed check definitions in report order, keyed by stable identifier. */
    private const DEFINITIONS = [
        'course_visibility' => ['class' => check\course_visibility::class, 'title' => 'check:visibility:title'],
        'course_dates' => ['class' => check\course_dates::class, 'title' => 'check:dates:title'],
        'course_completion' => ['class' => check\course_completion::class, 'title' => 'check:completion:title'],
        'activity_completion_coverage' => [
            'class' => check\activity_completion_coverage::class,
            'title' => 'check:coverage:title',
        ],
        'required_completion_activity_accessibility' => [
            'class' => check\required_completion_activity_accessibility::class,
            'title' => 'check:requiredactivity:title',
        ],
        'quiz_pass_completion' => ['class' => check\quiz_pass_completion::class, 'title' => 'check:quiz:title'],
        'quiz_question_randomisation' => [
            'class' => check\quiz_question_randomisation::class,
            'title' => 'check:randomisation:title',
        ],
        'feedback_presence' => ['class' => check\feedback_presence::class, 'title' => 'check:feedback:title'],
        'incomplete_content' => ['class' => check\incomplete_content::class, 'title' => 'check:incomplete:title'],
        'activity_date_alignment' => ['class' => check\activity_date_alignment::class, 'title' => 'check:activitydates:title'],
    ];

    /** @var bool[] Enabled states keyed by check identifier. */
    private array $enabled = [];

    /** @var int Minimum coverage percentage. */
    private int $minpercent;

    /**
     * Load one configuration snapshot, or validate explicitly supplied settings.
     *
     * @param array|null $settings Plugin settings; null loads Moodle site configuration.
     */
    public function __construct(?array $settings = null) {
        $settings = $settings ?? (array) get_config('report_coursecoach');
        foreach (self::DEFINITIONS as $id => $definition) {
            $value = $settings['enabled_' . $id] ?? self::DEFAULT_ENABLED;
            $this->enabled[$id] = !in_array($value, [0, '0', false], true);
        }

        $value = $settings['activity_completion_coverage_minpercent'] ?? self::DEFAULT_MINPERCENT;
        $valid = (is_int($value) || is_string($value)) && preg_match('/\A(?:[1-9][0-9]?|100)\z/', (string) $value);
        $this->minpercent = $valid ? (int) $value : self::DEFAULT_MINPERCENT;
    }

    /**
     * Return the fixed definitions shared by settings and the analyser.
     *
     * @return array Check definitions keyed by stable identifier.
     */
    public static function get_definitions(): array {
        return self::DEFINITIONS;
    }

    /**
     * Return whether a known check is enabled.
     *
     * @param string $id Stable check identifier.
     * @return bool Enabled state.
     */
    public function is_enabled(string $id): bool {
        if (!array_key_exists($id, $this->enabled)) {
            throw new coding_exception('Unknown Course Coach check identifier.');
        }
        return $this->enabled[$id];
    }

    /**
     * Return the validated completion-coverage target.
     *
     * @return int Minimum percentage from 1 to 100.
     */
    public function get_activity_completion_minpercent(): int {
        return $this->minpercent;
    }
}
