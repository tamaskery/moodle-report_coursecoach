# Moodle 5.3 compatibility

Plugin 1.0.1 declares support for Moodle 4.5 through 5.3. Validation on 4 October 2026 used the current `MOODLE_503_STABLE` branch at [`42622298fe06f9626d988d60b2bf589bd8f850e8`](https://github.com/moodle/moodle/tree/42622298fe06f9626d988d60b2bf589bd8f850e8). Moodle's [published release notes](https://moodledev.io/general/releases/5.3) still describe 5.3 as unreleased, with a target release date of 5 October 2026. Recheck the final core release before tagging or publishing the plugin release.

## Core review and changes

| Area | Finding and compatibility work |
| --- | --- |
| Server requirements | Core's `admin/environment.xml` requires MariaDB 11.4 or newer and PostgreSQL 17 or newer for 5.3; the published release notes still list older database minima. CI uses MariaDB 11 and PostgreSQL 17, with PHP 8.3 and 8.4 respectively for 5.3. The plugin retains its Moodle 4.5 minimum. |
| Completion | The inspected `completion_info` methods remain available. An integration test runs all ten checks twice and verifies that course/activity configuration, grades, attempts, completion criteria, and existing learner completion records remain unchanged. |
| Quiz structure and dates | The quiz structure methods used by the randomisation checker remain available. A 5.3-specific schema-aware regression test verifies that the new due date does not replace the existing open/close boundary check or cause configuration changes. Older Moodle versions skip that one test. |
| Navigation | Moodle 5.3 replaces the legacy navigation markup. Tests use Moodle's navigation helper, retain course-context teacher/manager access and student denial checks, and run on 4.5, 5.2, and 5.3. |
| Presentation | 5.3-only browser scenarios exercise Boost light/dark modes, progress-bar accessibility attributes, and keyboard activation of the course settings link. Full-page synthetic screenshots are saved as workflow artifacts. Review found pale-grey Not applicable badges with white text; an explicit dark foreground now pairs with that background, also for the Not assessed badge. The native card/list layout and heading hierarchy remain intact. |
| PHPUnit and coding standards | Coverage attributes are used by newer PHPUnit versions, with legacy declarations retained for older supported tooling. An unnecessary `MOODLE_INTERNAL` guard was removed from the callback-only `lib.php`; no protected request handler or capability enforcement was removed. |
| Security and privacy | The report remains read-only with exactly ten independent checks. Course-context capability enforcement, normal Mustache escaping, the null privacy provider, and the absence of plugin tables or external processing remain unchanged. |

Authoritative sources: [environment checks](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/admin/environment.xml), [core upgrade notes](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/lib/UPGRADING.md), [course upgrade notes](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/course/UPGRADING.md), [quiz upgrade notes](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/mod/quiz/UPGRADING.md), [completion implementation](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/lib/completionlib.php), [quiz structure](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/mod/quiz/classes/structure.php), and [Behat navigation helper](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/lib/tests/behat/behat_navigation.php).

## Validation evidence

The [full compatibility run](https://github.com/tamaskery/moodle-report_coursecoach/actions/runs/37196056018) passed all eleven jobs before the final support metadata and badge-colour update. Subsequent pushes rerun the same suite; check the [latest workflow results](https://github.com/tamaskery/moodle-report_coursecoach/actions/workflows/moodle-plugin-ci.yml) for the release candidate commit.

- Seven quality jobs cover Moodle 4.5 through 5.3 with MariaDB and Moodle 5.2/5.3 with PostgreSQL 17.
- Checks include PHP lint, Moodle coding standards, PHPDoc, plugin metadata, savepoints, Mustache lint, PHP Mess Detector, and PHPUnit. PHP Mess Detector reports existing, non-blocking complexity/class-size findings; these are not a claim of zero static-analysis findings.
- Three Behat jobs cover navigation, capability denial and report rendering on 4.5, 5.2 and 5.3. The 5.3 job includes light/dark, keyboard and progress-bar scenarios and stores screenshots.
- The 5.3 MariaDB job installs a disposable Moodle 5.2 site, creates a synthetic course, runs the report, upgrades the same site/database to 5.3, and reruns all ten checks. Course configuration is compared before and after each analysis. PHPUnit separately verifies activity and learner-record preservation on every supported branch.
- Package validation verifies the installable `coursecoach` layout and excludes development files and the CI-only upgrade helper.
- Full-page light/dark screenshots were visually reviewed. This focused review and keyboard/markup coverage do not constitute a full accessibility certification.

The host has no usable local PHP/Moodle test environment, so runtime verification uses GitHub Actions. `git diff --check` is also run locally.

## Final-release checklist

1. Recheck the final Moodle 5.3 revision and upgrade notes against the audited core revision. Rerun CI if core changes affect the APIs or requirements above.
2. Require all eleven jobs on the plugin release candidate to pass and review its light/dark screenshot artifacts.
3. Confirm README, Marketplace copy, security policy, repository guidance and changelog agree with `version.php`: Moodle 4.5 through 5.3, plugin 1.0.1, and the unchanged Moodle 4.5 minimum.
4. Build and validate the installable `coursecoach` package before tagging or publishing. No plugin database upgrade step is needed because this change introduces no tables or schema changes.

For local development, use the checkout's PHPUnit configuration and actual plugin path. On Moodle 4.5/5.0 the plugin path is `report/coursecoach`; on 5.1 and later it is `public/report/coursecoach`. Moodle's site installation and upgrade CLI scripts remain under root `admin/cli`.
