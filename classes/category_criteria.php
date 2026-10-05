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
 * Category policy storage and nearest-ancestor resolution.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

/**
 * Stores complete category policies using Moodle plugin configuration.
 */
final class category_criteria {
    /**
     * Resolve the nearest valid policy without writing configuration.
     *
     * @param int $categoryid Course category, or zero for the site policy.
     * @return criteria_config Effective policy snapshot.
     */
    public static function resolve(int $categoryid): criteria_config {
        global $DB;
        $settings = (array) get_config('report_coursecoach');
        if ($categoryid > 0) {
            $path = $DB->get_field('course_categories', 'path', ['id' => $categoryid]);
            $ancestors = array_reverse(explode('/', trim((string) $path, '/')));
            foreach ($ancestors as $ancestor) {
                $id = (int) $ancestor;
                $data = json_decode($settings['category_' . $id] ?? '', true);
                if (is_array($data) && self::is_valid($data)) {
                    return new criteria_config($data, $id);
                }
            }
        }
        return new criteria_config($settings);
    }

    /**
     * Validate the complete fixed policy, rejecting partial or arbitrary rules.
     *
     * @param array $data Submitted settings.
     * @return bool Whether every setting has a supported value.
     */
    public static function is_valid(array $data): bool {
        $expected = (new criteria_config([]))->to_array();
        if (array_diff_key($data, $expected) || array_diff_key($expected, $data)) {
            return false;
        }
        foreach (array_keys(criteria_config::get_definitions()) as $id) {
            if (!in_array($data['enabled_' . $id], [0, 1, '0', '1'], true)) {
                return false;
            }
        }
        $value = $data['activity_completion_coverage_minpercent'];
        return (is_int($value) || is_string($value)) && preg_match('/\A(?:[1-9][0-9]?|100)\z/', (string) $value);
    }

    /**
     * Save a complete override, or remove it to restore inheritance.
     *
     * The page must additionally validate its sesskey before calling this method.
     *
     * @param int $categoryid Existing category identifier.
     * @param array|null $data Complete settings, or null to inherit.
     */
    public static function save(int $categoryid, ?array $data): void {
        global $DB;
        require_capability('moodle/site:config', \context_system::instance());
        $DB->get_record('course_categories', ['id' => $categoryid], 'id', MUST_EXIST);
        if ($data === null) {
            unset_config('category_' . $categoryid, 'report_coursecoach');
            return;
        }
        if (!self::is_valid($data)) {
            throw new \invalid_parameter_exception('Invalid Course Coach category criteria.');
        }
        set_config('category_' . $categoryid, json_encode((new criteria_config($data))->to_array()), 'report_coursecoach');
    }
}
