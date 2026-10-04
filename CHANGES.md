# Changelog

## Unreleased

- Prepared Moodle 5.3 validation with PHP 8.3/MariaDB and PHP 8.4/PostgreSQL CI jobs and manual workflow dispatch.
- Extended Behat smoke-test CI to Moodle 4.5, 5.2, and 5.3 and replaced legacy navigation markup selectors with Moodle's navigation helper.
- Added the Moodle 5.3 API review and release checklist; declared support remains Moodle 4.5 through 5.2 pending validation.

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
