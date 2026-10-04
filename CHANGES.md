# Changelog

## 1.0.1

- Added Moodle 5.3 support while retaining Moodle 4.5 through 5.2 compatibility.
- Updated CI to use PostgreSQL 17, as required by the current Moodle 5.3 core environment checks, with PHP 8.3/MariaDB and PHP 8.4/PostgreSQL validation.
- Added a Moodle 5.2-to-5.3 site-upgrade smoke test, repeated-analysis checks that preserve configuration and learner records, and coverage for Moodle 5.3 quiz due dates.
- Added Boost light/dark screenshots, progress-bar accessibility markup checks, and keyboard settings-link tests. Corrected low-contrast Not applicable and Not assessed badges with an explicit dark foreground.
- Modernised PHPUnit coverage attributes while preserving metadata for older supported Moodle tooling; removed an unnecessary guard from the callback-only lib.php.
- Recorded compatibility evidence and the final-release recheck in the Moodle 5.3 checklist.

## 1.0.0

- Prepared the first stable release of the read-only, 10-check course-readiness report.
- Added administrator and Marketplace-facing documentation, a security policy, and a synthetic screenshot capture plan.
- Corrected the bundled GNU GPL v3 licence text.
- Confirmed automated compatibility coverage for Moodle 4.5 through 5.2 with MariaDB and Moodle 5.2 with PostgreSQL.

## 0.6.1

- Fixed double-escaped activity and section names while preserving normal Mustache escaping.
- Migrated the plugin from `local_coursecoach` to the course report component `report_coursecoach` and capability `report/coursecoach:view`.
- Expanded automated coverage to Moodle 4.5, 5.0, 5.1, and 5.2, with MariaDB and PostgreSQL validation.
- Added focused Behat smoke tests for editing-teacher and manager access, student denial, and report rendering.

## 0.6.0

- Completed the initial 10-check, read-only course-readiness report and moved maturity to Beta.
- Added Moodle 4.5 and 5.2 compatibility validation, including MariaDB and PostgreSQL coverage.
- Strengthened release-package validation and included the complete GNU GPL v3 licence.
