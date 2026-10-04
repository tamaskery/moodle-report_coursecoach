# Course Readiness Coach

> Check whether your Moodle course is ready for learners.

Course Readiness Coach is a read-only, course-level report for Moodle. It helps editing teachers, course managers, administrators, and other authorised users identify common course-configuration issues before learners use a course. Its deterministic checks support course preparation; they do not certify pedagogical quality, accessibility, security, or an error-free course.

The plugin type is **Report**, its component is `report_coursecoach`, and version **1.1.0** supports Moodle 4.5 through 5.3.

## Installation

See the [user and administrator guide](docs/user-guide.md), [screenshots](docs/screenshots/README.md), and [changelog](CHANGES.md). Submission descriptions and release notes are available in the [Marketplace copy](docs/marketplace.md).

1. Download the plugin package and extract its `coursecoach` directory into your Moodle installation at `report/coursecoach`.
2. Sign in as a site administrator and visit **Site administration > Notifications**.
3. Follow Moodle's prompts to complete installation, then purge caches if Moodle requests it.

The plugin creates no database tables. Earlier GitHub releases used the separate component `local_coursecoach` at `local/coursecoach`. When migrating from one of those releases, uninstall and remove that local plugin before installing `report_coursecoach` to avoid duplicate navigation entries.

## Access and permissions

Authorised users can open **Course Readiness Coach** from the course navigation, normally under **More > Reports** in Moodle's standard course interface. Direct report access also requires the course-context capability:

`report/coursecoach:view`

Editing teachers and managers receive this capability by default. Administrators may grant it to other roles when appropriate; students do not receive it by default.

## Readiness checks

The report offers 10 checks, all enabled by default:

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

## Site readiness criteria

Open **Site administration > Plugins > Reports > Course Readiness Coach** and use the **Readiness criteria** settings. Only users with the system capability `moodle/site:config` can change this site-wide policy. Course report access still requires `report/coursecoach:view` in the course context.

Each check can be enabled or disabled. Activity completion coverage also accepts an integer minimum percentage from 1 to 100, defaulting to 100. The denominator remains visible activities with Moodle-defined completion rules or grading support; manual and automatic completion count equally. Coverage comparisons use the exact ratio, not a rounded percentage. No eligible activities, or disabled course completion, remains Not applicable.

Disabled checks are not run or scored and are listed separately as **Disabled by site configuration**. Disabling one check does not disable related checks: their applicability still follows the actual Moodle course settings. If every check is disabled, the course is **Not assessed**, with score 0. Changing settings can change scores and readiness labels across all courses; it never changes a course or prevents publishing it.

Upgrading from 1.0.1 with the defaults preserves the existing checks, results, weights and scoring. Missing or malformed settings fall back to the original defaults. No database schema migration is required. Settings are stored through Moodle config APIs; the report does not write settings or store results. A report uses a configuration snapshot; saved settings take effect on the next report request.

Use **Manage category readiness criteria** on the settings page to override the full policy for a category. Courses inherit the nearest configured category, then site defaults. Choose **Inherit from parent category or site** and save to remove an override. Category and course moves use the new ancestry on the next report request. Existing installations have no overrides, so upgrade defaults are unchanged. The report identifies the policy source. Only site administrators can edit overrides; category managers do not gain configuration permissions automatically. Configurable severity, readiness profiles and new check types are outside this release.

## Score and overall status

Only applicable checks contribute to the 0–100 score. Each check has a defined weight: **Passed** earns full credit, **Warning** earns half credit, and **Critical** earns no credit. Not-applicable checks are excluded from the denominator.

- **Ready** means every applicable check passed.
- **Needs attention** means there is a warning or the score is below 100 without a critical result.
- **Not ready** means at least one critical result exists, regardless of the numerical score.
- **Not assessed** means no check was applicable.

The score is a configuration-readiness indicator only. A score of 100 does not certify teaching quality, accessibility compliance, security compliance, or suitability for a particular programme.

## Privacy and read-only behaviour

Course Readiness Coach analyses course configuration, not learner performance. It:

- creates no plugin database tables and stores no personal data or report history; site criteria use Moodle plugin configuration;
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

Automated CI covers Moodle 4.5, 5.0, 5.1, 5.2, and 5.3 with MariaDB, plus Moodle 5.2 and 5.3 with PostgreSQL 17. Moodle 5.3 is tested with PHP 8.3/MariaDB and PHP 8.4/PostgreSQL. Browser smoke tests cover Moodle 4.5, 5.2, and 5.3; the 5.3 tests also cover Boost light/dark modes, keyboard settings-link access, and synthetic screenshots. CI compares an actual plugin upgrade from audited 1.0.1 commit `8a7eec02ce932e34b37011599f4601991c8b4dd2` with default settings, then exercises a Moodle 5.2-to-5.3 site upgrade and checks that all ten report checks still run without changing course configuration.

Moodle 5.3 validation used the current `MOODLE_503_STABLE` branch on 4 October 2026, ahead of the scheduled final release. See the [compatibility evidence and release checklist](docs/moodle-5.3.md) for validation scope and the final-release recheck. The report retains its ten read-only checks.

## Support and licence

- Source: [GitHub repository](https://github.com/tamaskery/moodle-report_coursecoach)
- Bugs and support requests: [GitHub Issues](https://github.com/tamaskery/moodle-report_coursecoach/issues)
- Licence: [GNU GPL v3 or later](LICENSE)
