# Course Readiness Coach

> Check whether your Moodle course is ready for learners.

Course Readiness Coach is a read-only, course-level report for Moodle. It helps editing teachers, course managers, administrators, and other authorised users identify common course-configuration issues before learners use a course. Its deterministic checks support course preparation; they do not certify pedagogical quality, accessibility, security, or an error-free course.

The plugin type is **Report**, its component is `report_coursecoach`, and version **1.0.0** supports Moodle 4.5 through 5.2.

## Installation

1. Download the plugin package and extract its `coursecoach` directory into your Moodle installation at `report/coursecoach`.
2. Sign in as a site administrator and visit **Site administration > Notifications**.
3. Follow Moodle's prompts to complete installation, then purge caches if Moodle requests it.

The plugin creates no database tables. Earlier GitHub releases used the separate component `local_coursecoach` at `local/coursecoach`. When migrating from one of those releases, uninstall and remove that local plugin before installing `report_coursecoach` to avoid duplicate navigation entries.

## Access and permissions

Authorised users can open **Course Readiness Coach** from the course navigation, normally under **More > Reports** in Moodle's standard course interface. Direct report access also requires the course-context capability:

`report/coursecoach:view`

Editing teachers and managers receive this capability by default. Administrators may grant it to other roles when appropriate; students do not receive it by default.

## Readiness checks

The report runs exactly 10 checks:

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

Results identify passed, warning, critical, and not-applicable checks and provide Moodle settings links where a deterministic action is available.

## Score and overall status

Only applicable checks contribute to the 0–100 score. Each check has a defined weight: **Passed** earns full credit, **Warning** earns half credit, and **Critical** earns no credit. Not-applicable checks are excluded from the denominator.

- **Ready** means every applicable check passed.
- **Needs attention** means there is a warning or the score is below 100 without a critical result.
- **Not ready** means at least one critical result exists, regardless of the numerical score.
- **Not assessed** means no check was applicable.

The score is a configuration-readiness indicator only. A score of 100 does not certify teaching quality, accessibility compliance, security compliance, or suitability for a particular programme.

## Privacy and read-only behaviour

Course Readiness Coach analyses course configuration, not learner performance. It:

- creates no plugin database tables and stores no personal data or report history;
- sends no data outside Moodle and makes no AI or external API calls;
- implements Moodle's Privacy API as a null provider;
- does not modify course configuration, activities, quiz settings, or grades; and
- does not create or alter learner completion records.

Moodle core may still record ordinary access information in its standard logs; those logs are not plugin-owned storage.

## Limitations

Course Readiness Coach intentionally uses conservative, deterministic checks and does not replace teacher review or institutional quality assurance.

- Activity completion coverage does not assume passive, view-trackable resources require completion.
- Required-activity visibility checks deterministic course-completion requirements, not learner-specific restrictions.
- Quiz randomisation checks Moodle random-question selection, not broader assessment design.
- Learner feedback checks for a visible standard Moodle Feedback activity.
- Incomplete content checks visible, empty, non-general course sections.
- Activity date alignment checks high-confidence Quiz and Assignment timeline mismatches. It skips relative Assignment dates and learner-specific availability.
- The plugin does not crawl external links or inspect their availability.

## Development and verification

From the Moodle root, run:

```text
vendor/bin/phpunit report/coursecoach/tests
vendor/bin/phpcs --standard=moodle report/coursecoach
```

Automated CI covers Moodle 4.5, 5.0, 5.1, and 5.2 with MariaDB, plus Moodle 5.2 with PostgreSQL. The repository also contains focused PHPUnit and Behat coverage.

Moodle 5.3 preparation adds CI jobs on `MOODLE_503_STABLE` with PHP 8.3/MariaDB and PHP 8.4/PostgreSQL, plus Behat smoke tests on Moodle 4.5, 5.2, and 5.3. These jobs are configured; their results must be verified before claiming Moodle 5.3 support. See the [Moodle 5.3 upgrade checklist](docs/moodle-5.3.md) for the API review, validation requirements, and release steps. The declared support range remains Moodle 4.5 through 5.2 during preparation.

## Support and licence

- Source: [GitHub repository](https://github.com/tamaskery/moodle-report_coursecoach)
- Bugs and support requests: [GitHub Issues](https://github.com/tamaskery/moodle-report_coursecoach/issues)
- Licence: [GNU GPL v3 or later](LICENSE)
