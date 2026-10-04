# Moodle Marketplace Copy

## Plugin name

Course Readiness Coach

## Component

`report_coursecoach`

## Category/type

Reports

## Short description

A read-only course report that helps Moodle teachers and managers identify common configuration issues before learners use a course. It provides deterministic findings, recommendations, and a configuration-readiness score.

## Full description

Course Readiness Coach helps editing teachers, course managers, administrators, and other authorised roles review whether a Moodle course is technically configured for learner use. From one course-level report, users can review course visibility and dates, completion configuration, required activity visibility, assessment configuration, learner feedback presence, incomplete sections, and selected activity-date conflicts.

The report offers 10 deterministic checks, all enabled by default, and groups the results as passed, warnings, critical findings, or not applicable. Applicable checks contribute to a weighted readiness score, and findings link to relevant Moodle settings where appropriate. The score indicates configuration readiness only; it does not certify pedagogical quality, accessibility, security, or programme suitability.

Course Readiness Coach is read-only. It does not change course settings, activities, grades, quizzes, or learner completion records. It stores no report results or personal data, sends no information outside Moodle, and does not use AI or external APIs.

Administrators can enable or disable each check and configure the minimum activity completion coverage percentage. Defaults preserve the original results. Disabled checks are disclosed separately and excluded from scoring. Settings are site-wide, stored through Moodle configuration APIs, and do not change course configuration. Weights and outcome severity remain fixed.

The current checks are:

1. Course visibility
2. Course dates
3. Course completion
4. Activity completion coverage
5. Required activity visibility
6. Quiz pass and completion
7. Quiz question randomisation
8. Learner feedback
9. Incomplete course content
10. Activity date alignment

## Key features

- Ten focused course-configuration checks.
- Issue-first report with readiness score and overall status.
- Deterministic explanations and recommendations.
- Moodle settings links for applicable findings.
- Course-context capability control for authorised roles.
- Read-only operation with no plugin database tables.
- Moodle 4.5 through 5.3 support.

## Privacy statement

The plugin analyses course configuration rather than learner performance. It creates no plugin database tables, stores no personal data or report history, and sends no data to external services. Its Moodle Privacy API implementation is a null provider. Moodle core may independently record normal page access in standard logs.

## Installation

Extract the package's `coursecoach` directory to `report/coursecoach`, then visit **Site administration > Notifications** as an administrator and complete Moodle's standard plugin installation. Authorised users can then open Course Readiness Coach from a course's **More > Reports** area.

## Supported Moodle versions

Moodle 4.5 through Moodle 5.3.

## Support and source

- Source repository: https://github.com/tamaskery/moodle-report_coursecoach
- Support and bug tracker: https://github.com/tamaskery/moodle-report_coursecoach/issues
- Licence: GNU GPL v3 or later

## Reviewer notes

- Plugin type: Report
- Component: `report_coursecoach`
- Install path: `report/coursecoach`
- Context: course context
- Capability: `report/coursecoach:view`
- Operation: read-only; no course, activity, grade, quiz, or completion-record changes
- Storage: no plugin database tables, personal data, or persisted report results
- External processing: no external data transfer, API calls, or AI use
- Implementation: standard Moodle capabilities, course context, APIs, Output API, and Mustache templates
- Automated compatibility CI: Moodle 4.5, 5.0, 5.1, 5.2, and 5.3
- Databases tested: MariaDB and PostgreSQL 17
- Automated tests: focused PHPUnit suite and Behat access/rendering smoke tests
