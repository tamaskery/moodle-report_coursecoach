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
 * Administrator-only category readiness criteria.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$url = new moodle_url('/report/coursecoach/category.php', ['categoryid' => $categoryid]);
admin_externalpage_setup('reportcoursecoachcategories', '', null, $url);

$form = null;
if ($categoryid) {
    $category = core_course_category::get($categoryid, MUST_EXIST, true);
    $config = \report_coursecoach\category_criteria::resolve($categoryid);
    $form = new \report_coursecoach\form\category_criteria($url, ['categoryid' => $categoryid]);
    $data = $config->to_array();
    $data['override'] = (int) ($config->get_source_category_id() === $categoryid);
    $form->set_data($data);
    if ($form->is_cancelled()) {
        redirect(new moodle_url('/report/coursecoach/category.php'));
    }
    if ($submitted = $form->get_data()) {
        require_sesskey();
        $policy = null;
        if ($submitted->override) {
            $policy = array_intersect_key((array) $submitted, $config->to_array());
        }
        \report_coursecoach\category_criteria::save($categoryid, $policy);
        redirect($url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('categorycriteria', 'report_coursecoach'));
echo $OUTPUT->box(get_string('categorycriteria_desc', 'report_coursecoach'));
$selector = new single_select(
    new moodle_url('/report/coursecoach/category.php'),
    'categoryid',
    core_course_category::make_categories_list(),
    $categoryid,
    ['' => get_string('choose')]
);
$selector->set_label(get_string('category'));
echo $OUTPUT->render($selector);
if ($form) {
    echo $OUTPUT->heading($category->get_formatted_name(), 3);
    $sourceid = $config->get_source_category_id();
    $source = $sourceid
        ? get_string(
            'policysourcecategory',
            'report_coursecoach',
            core_course_category::get($sourceid, MUST_EXIST, true)->get_formatted_name()
        )
        : get_string('policysourcesite', 'report_coursecoach');
    echo $OUTPUT->box($source);
    $form->display();
}
echo $OUTPUT->footer();
