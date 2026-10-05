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
 * Course Coach analysis service.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use coding_exception;
use report_coursecoach\check\activity_completion_coverage;
use report_coursecoach\check\checker;
use stdClass;

/**
 * Runs the independent checks and passes their results to the calculator.
 */
final class course_analyser {
    /** @var checker[] Ordered checkers. */
    private array $checkers;

    /** @var string[] Identifiers of checks excluded by site configuration. */
    private array $disabledchecks = [];

    /** @var bool Resolve policy separately for each course when no configuration is supplied. */
    private bool $resolvepolicy;

    /**
     * Constructor.
     *
     * @param checker[]|null $checkers Optional checker list for reuse and testing.
     * @param criteria_config|null $config Optional criteria for the built-in checkers; ignored for an explicit checker list.
     */
    public function __construct(?array $checkers = null, ?criteria_config $config = null) {
        $this->resolvepolicy = $checkers === null && $config === null;
        if ($this->resolvepolicy) {
            $this->checkers = [];
            return;
        }
        if ($checkers === null) {
            $config = $config ?? new criteria_config();
            $checkers = [];
            foreach (criteria_config::get_definitions() as $id => $definition) {
                if (!$config->is_enabled($id)) {
                    $this->disabledchecks[] = $id;
                    continue;
                }
                $classname = $definition['class'];
                $checkers[] = $id === 'activity_completion_coverage'
                    ? new activity_completion_coverage($config->get_activity_completion_minpercent())
                    : new $classname();
            }
        }
        $this->checkers = $checkers;

        foreach ($this->checkers as $checker) {
            if (!$checker instanceof checker) {
                throw new coding_exception('Course Coach analysers require checker instances.');
            }
        }
    }

    /**
     * Analyse a course without modifying it.
     *
     * @param stdClass $course Course record.
     * @return readiness Calculated course readiness.
     */
    public function analyse(stdClass $course): readiness {
        if ($this->resolvepolicy) {
            $config = category_criteria::resolve((int) $course->category);
            return (new self(null, $config))->analyse($course);
        }
        $results = [];
        foreach ($this->checkers as $checker) {
            $results[] = new weighted_result($checker->check($course), $checker->get_weight());
        }

        return (new readiness_calculator())->calculate($results, $this->disabledchecks);
    }
}
