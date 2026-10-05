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
 * English language strings for the Course Coach plugin.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['action:activitycompletion'] = 'Configure activity completion';
$string['action:activitydates'] = 'Review activity dates';
$string['action:completion'] = 'Configure course completion';
$string['action:dates'] = 'Edit course dates';
$string['action:feedback'] = 'Edit Feedback visibility';
$string['action:quizcompletion'] = 'Configure quiz completion';
$string['action:quizquestions'] = 'Edit quiz questions';
$string['action:requiredactivity'] = 'Edit required activity visibility';
$string['action:section'] = 'Edit course section';
$string['action:visibility'] = 'Edit course visibility';
$string['alldisabled'] = 'No readiness checks are enabled. This course has not been assessed.';
$string['assessedchecks'] = '{$a->assessed} of {$a->total} checks assessed';
$string['assessedenabledchecks'] = '{$a->assessed} of {$a->total} enabled checks assessed';
$string['categorycriteria'] = 'Category readiness criteria';
$string['categorycriteria_desc'] = 'Select a category to configure its readiness policy. Without an override, courses inherit the nearest configured parent category, then site defaults. An override saves all ten check switches and the coverage target as one policy; later parent or site changes do not alter it. Choose Inherit and save to remove an override. These settings affect reports only, never course configuration or access.';
$string['categoryinherit'] = 'Inherit from parent category or site';
$string['categoryoverride'] = 'Use a category override';
$string['categorypolicymode'] = 'Criteria policy';
$string['check:activitydates:notapplicable:explanation'] = 'No visible Quiz or Assignment has a user-independent date that can be compared with a configured course boundary.';
$string['check:activitydates:notapplicable:recommendation'] = 'No activity date change is needed for this check.';
$string['check:activitydates:passed:explanation'] = 'The evaluated Quiz and Assignment dates contain no high-confidence course timeline mismatch.';
$string['check:activitydates:passed:recommendation'] = 'No activity date change is needed.';
$string['check:activitydates:title'] = 'Activity date alignment';
$string['check:activitydates:warning:explanation'] = 'Some activities are scheduled entirely outside the configured course timeline: {$a}.';
$string['check:activitydates:warning:recommendation'] = 'Review the activity dates and course dates to confirm that the schedule is intentional.';
$string['check:completion:configured:explanation'] = 'Course completion is enabled and has usable completion criteria.';
$string['check:completion:configured:recommendation'] = 'Review the criteria to confirm that they match the intended course requirements.';
$string['check:completion:disabled:explanation'] = 'Course completion is disabled, so Moodle cannot determine when a learner has completed this course.';
$string['check:completion:disabled:recommendation'] = 'Enable course completion in the course settings, then configure appropriate completion criteria.';
$string['check:completion:nocriteria:explanation'] = 'Course completion is enabled, but no course completion criteria are configured.';
$string['check:completion:nocriteria:recommendation'] = 'Add at least one meaningful course completion criterion.';
$string['check:completion:sitedisabled:explanation'] = 'Completion tracking is disabled for the site, so this course cannot use course completion.';
$string['check:completion:sitedisabled:recommendation'] = 'Ask a site administrator to enable completion tracking before configuring this course.';
$string['check:completion:title'] = 'Course completion';
$string['check:coverage:disabled:explanation'] = 'Course completion tracking is disabled, so activity completion coverage cannot be assessed.';
$string['check:coverage:disabled:recommendation'] = 'Enable course completion tracking before configuring activity completion.';
$string['check:coverage:none:explanation'] = 'There are no visible activities with Moodle-defined completion behaviour to assess.';
$string['check:coverage:none:recommendation'] = 'No activity completion configuration is needed for this check.';
$string['check:coverage:passed:explanation'] = 'All {$a} meaningful visible learner activities have completion configured.';
$string['check:coverage:passed:recommendation'] = 'No activity completion change is needed.';
$string['check:coverage:threshold:passed'] = '{$a->configured} of {$a->total} meaningful visible learner activities have completion configured, meeting the minimum of {$a->required}%.';
$string['check:coverage:threshold:warning'] = '{$a->configured} of {$a->total} meaningful visible learner activities have completion configured, below the minimum of {$a->required}%. Activities without completion: {$a->activities}.';
$string['check:coverage:title'] = 'Activity completion coverage';
$string['check:coverage:warning:explanation'] = '{$a->count} learner activities do not have activity completion configured: {$a->activities}.';
$string['check:coverage:warning:recommendation'] = 'Configure manual or automatic completion for the listed activities when completion is meaningful.';
$string['check:dates:expired:explanation'] = 'The course end date ({$a}) has passed. Moodle may treat the course as concluded in course listings and reports.';
$string['check:dates:expired:recommendation'] = 'Review the course dates and update the end date if the course is still being prepared or delivered.';
$string['check:dates:invalid:explanation'] = 'The course end date ({$a->enddate}) is not later than its start date ({$a->startdate}).';
$string['check:dates:invalid:recommendation'] = 'Set an end date that is later than the course start date.';
$string['check:dates:notapplicable:explanation'] = 'No course start or end date is configured, so there are no dates to assess.';
$string['check:dates:notapplicable:recommendation'] = 'Add course dates if they are required for this course.';
$string['check:dates:passed:explanation'] = 'The configured course dates contain no obvious conflicts.';
$string['check:dates:passed:recommendation'] = 'No date change is needed.';
$string['check:dates:title'] = 'Course dates';
$string['check:feedback:passed:explanation'] = 'A visible Moodle Feedback activity is present in this course.';
$string['check:feedback:passed:recommendation'] = 'No feedback availability change is needed.';
$string['check:feedback:title'] = 'Learner feedback';
$string['check:feedback:warning:explanation'] = 'No visible Moodle Feedback activity was found in this course.';
$string['check:feedback:warning:recommendation'] = 'Consider collecting learner feedback if evaluation is part of your course process.';
$string['check:incomplete:passed:explanation'] = 'No visible non-general course sections are empty.';
$string['check:incomplete:passed:recommendation'] = 'No content change is needed.';
$string['check:incomplete:title'] = 'Incomplete course content';
$string['check:incomplete:warning:explanation'] = 'Visible course sections contain no activities or resources: {$a}.';
$string['check:incomplete:warning:recommendation'] = 'Add the intended content, or hide or remove the empty section.';
$string['check:quiz:disabled:explanation'] = 'Course completion tracking is disabled, so quiz completion configuration cannot be assessed.';
$string['check:quiz:disabled:recommendation'] = 'Enable course completion tracking before configuring quiz completion.';
$string['check:quiz:none:explanation'] = 'No quizzes are used as course completion criteria.';
$string['check:quiz:none:recommendation'] = 'No quiz pass configuration is needed for this check.';
$string['check:quiz:nopasscompletion:issue'] = '{$a} has a passing grade, but activity completion does not require that grade.';
$string['check:quiz:nopassgrade:issue'] = '{$a} is part of the course completion pathway but has no passing grade configured.';
$string['check:quiz:passed:explanation'] = 'Each quiz used for course completion has a passing grade and requires it for activity completion.';
$string['check:quiz:passed:recommendation'] = 'No quiz completion change is needed.';
$string['check:quiz:title'] = 'Quiz pass and completion';
$string['check:quiz:warning:recommendation'] = 'Set a passing grade and require that grade for activity completion if successful assessment is intended.';
$string['check:randomisation:notapplicable:explanation'] = 'No quizzes are used as course completion criteria.';
$string['check:randomisation:notapplicable:recommendation'] = 'No quiz question randomisation is needed for this check.';
$string['check:randomisation:passed:explanation'] = 'Each quiz used for course completion includes random question selection.';
$string['check:randomisation:passed:recommendation'] = 'No quiz question randomisation change is needed.';
$string['check:randomisation:title'] = 'Quiz question randomisation';
$string['check:randomisation:warning:explanation'] = '{$a} uses a fixed question set for every attempt.';
$string['check:randomisation:warning:recommendation'] = 'Consider using random questions or a larger question pool if assessment integrity is important.';
$string['check:requiredactivity:disabled:explanation'] = 'Course completion tracking is disabled, so required activity availability cannot be assessed.';
$string['check:requiredactivity:disabled:recommendation'] = 'Enable course completion tracking before assessing required activities.';
$string['check:requiredactivity:hidden:explanation'] = '{$a} is required for course completion but is currently hidden from learners.';
$string['check:requiredactivity:hidden:recommendation'] = 'Make the listed required activity available to learners, or remove it from the course completion criteria.';
$string['check:requiredactivity:none:explanation'] = 'No activity-based course completion criteria are configured.';
$string['check:requiredactivity:none:recommendation'] = 'No required activity availability is needed for this check.';
$string['check:requiredactivity:passed:explanation'] = 'All activities required for course completion are visible to learners.';
$string['check:requiredactivity:passed:recommendation'] = 'No required activity availability change is needed.';
$string['check:requiredactivity:title'] = 'Required activity visibility';
$string['check:visibility:hidden:explanation'] = 'The course is hidden and is not currently visible to learners.';
$string['check:visibility:hidden:recommendation'] = 'When preparation is complete, make the course visible in the course settings.';
$string['check:visibility:passed:explanation'] = 'The course is visible to learners.';
$string['check:visibility:passed:recommendation'] = 'No visibility change is needed.';
$string['check:visibility:title'] = 'Course visibility';
$string['coursecoach:view'] = 'View the Course Readiness Coach report';
$string['coverageminpercent'] = 'Minimum activity completion coverage';
$string['coverageminpercent_desc'] = 'Minimum percentage of eligible visible activities that must have manual or automatic completion configured. Default: 100%. Activities are eligible when Moodle reports completion rules or grading support. No eligible activities, or disabled course completion, remains Not applicable. Disable the coverage check to impose no requirement.';
$string['criteria'] = 'Readiness criteria';
$string['criteria_activity_completion_coverage_desc'] = 'Warn when the configured percentage of meaningful visible activities is not met. Course completion must be enabled. Default: enabled, with 100% coverage.';
$string['criteria_activity_date_alignment_desc'] = 'Warn about high-confidence Quiz and Assignment opening or closing dates outside course boundaries. Relative Assignment dates, due dates and learner-specific availability are excluded. Default: enabled.';
$string['criteria_course_completion_desc'] = 'Report Critical when site or course completion is disabled, Warning when no course completion criteria exist, and Passed when criteria exist. Disabling this check does not disable other completion-related checks. Default: enabled.';
$string['criteria_course_dates_desc'] = 'Report Critical when the end date is not later than the start date, or Warning when the end date has passed. Dates are not required; no dates means Not applicable. Default: enabled.';
$string['criteria_course_visibility_desc'] = 'Warn when the course is hidden. Disable this check if courses should remain hidden during readiness review. Default: enabled.';
$string['criteria_desc'] = 'These settings apply to courses without a category override. All checks are enabled by default and use their existing outcomes and weights. Passed earns full credit, Warning half credit, and Critical no credit. Any Critical result makes the course Not ready; warnings mean Needs attention. Disabled checks are not run or scored and are listed separately in the report. Saving these settings changes site readiness policy, not course configuration or access.';
$string['criteria_feedback_presence_desc'] = 'Warn when no visible standard Moodle Feedback activity exists. Other feedback tools and external surveys are not assessed. Default: enabled.';
$string['criteria_incomplete_content_desc'] = 'Warn about visible non-general sections containing no activities or resources. Disable this check to allow empty sections. Default: enabled.';
$string['criteria_quiz_pass_completion_desc'] = 'Warn when a quiz used as a course completion criterion lacks a positive passing grade or automatic completion requiring that grade. Default: enabled.';
$string['criteria_quiz_question_randomisation_desc'] = 'Warn when a quiz used as a course completion criterion has no random-question selection. Disable this check when fixed question sets are intentional. Default: enabled.';
$string['criteria_required_activity_visibility_desc'] = 'Report Critical when an existing activity referenced by course completion criteria is hidden. Learner-specific restrictions are not evaluated. Default: enabled.';
$string['criteriapercent'] = '{$a}%';
$string['criticalissues'] = 'Critical issues';
$string['disabledcategoryheading'] = 'Disabled by category configuration';
$string['disabledchecks_desc'] = 'These checks were not run and do not contribute to the readiness score or result counts.';
$string['disabledchecksheading'] = 'Disabled by site configuration';
$string['enablecheck'] = 'Enable: {$a}';
$string['invalidcriteria'] = 'Choose valid check settings and a coverage percentage from 1 to 100.';
$string['managecategorycriteria'] = 'Manage category readiness criteria';
$string['navigationlink'] = 'Course Readiness Coach';
$string['needsattention'] = 'Needs attention';
$string['needsattentionheading'] = 'Needs attention';
$string['notapplicablechecksheading'] = 'Not applicable';
$string['notassessed'] = 'Not assessed';
$string['notready'] = 'Not ready';
$string['passed'] = 'Passed';
$string['passedchecksheading'] = 'Checks passed';
$string['pluginname'] = 'Course Readiness Coach';
$string['policysourcecategory'] = 'Readiness policy: category {$a} (also applies to descendants without an override).';
$string['policysourcesite'] = 'Readiness policy: site defaults.';
$string['privacy:metadata'] = 'The Course Readiness Coach plugin does not store personal data.';
$string['readinessscore'] = 'Readiness score';
$string['ready'] = 'Ready';
$string['recommendation'] = 'Recommendation';
$string['reportintro'] = 'Check whether your Moodle course is ready for learners.';
$string['reporttitle'] = 'Course Readiness Coach';
$string['scoreexplanation'] = 'The score includes applicable checks only. Passed earns full credit, Warning half credit, and Critical no credit. Any Critical issue makes the course Not ready.';
$string['status:critical'] = 'Critical';
$string['status:not_applicable'] = 'Not applicable';
$string['status:passed'] = 'Passed';
$string['status:warning'] = 'Warning';
$string['summary'] = 'Summary';
$string['warnings'] = 'Warnings';
