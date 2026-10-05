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
 * Native form for a complete category criteria override.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\form;

use report_coursecoach\criteria_config;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Uses Moodle form validation and sesskey handling.
 */
final class category_criteria extends \moodleform {
    /**
     * Define category override fields.
     */
    protected function definition(): void {
        $form = $this->_form;
        $form->addElement('hidden', 'categoryid', $this->_customdata['categoryid']);
        $form->setType('categoryid', PARAM_INT);
        $form->addElement('select', 'override', get_string('categorypolicymode', 'report_coursecoach'), [
            0 => get_string('categoryinherit', 'report_coursecoach'),
            1 => get_string('categoryoverride', 'report_coursecoach'),
        ]);
        $form->setType('override', PARAM_INT);
        foreach (criteria_config::get_definitions() as $id => $definition) {
            $key = 'enabled_' . $id;
            $form->addElement(
                'advcheckbox',
                $key,
                get_string('enablecheck', 'report_coursecoach', get_string($definition['title'], 'report_coursecoach'))
            );
            $form->disabledIf($key, 'override', 'eq', 0);
        }
        $percentages = [];
        foreach (range(1, 100) as $percentage) {
            $percentages[$percentage] = get_string('criteriapercent', 'report_coursecoach', $percentage);
        }
        $form->addElement(
            'select',
            'activity_completion_coverage_minpercent',
            get_string('coverageminpercent', 'report_coursecoach'),
            $percentages
        );
        $form->setType('activity_completion_coverage_minpercent', PARAM_INT);
        $form->disabledIf('activity_completion_coverage_minpercent', 'override', 'eq', 0);
        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Validate the fixed set of policy choices before saving.
     *
     * @param array $data Submitted fields.
     * @param array $files Submitted files.
     * @return array Field errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (!in_array($data['override'], [0, 1, '0', '1'], true)) {
            $errors['override'] = get_string('invalidcriteria', 'report_coursecoach');
        } else if ($data['override']) {
            $policy = array_intersect_key($data, (new criteria_config([]))->to_array());
            if (!\report_coursecoach\category_criteria::is_valid($policy)) {
                $errors['activity_completion_coverage_minpercent'] = get_string('invalidcriteria', 'report_coursecoach');
            }
        }
        return $errors;
    }
}
