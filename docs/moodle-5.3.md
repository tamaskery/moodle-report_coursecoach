# Moodle 5.3 upgrade preparation

Prepared on 3 October 2026. Moodle's [release notes](https://moodledev.io/general/releases/5.3) still describe 5.3 as unreleased, with a target release date of 5 October 2026. Preparation does not certify compatibility: `version.php` continues to declare Moodle 4.5 through 5.2, and the plugin remains version 1.0.0.

## Core review

The review used the official `MOODLE_503_STABLE` branch at commit [`42622298fe06f9626d988d60b2bf589bd8f850e8`](https://github.com/moodle/moodle/tree/42622298fe06f9626d988d60b2bf589bd8f850e8). Recheck against the final release before publishing support.

| Area | Finding and preparation |
| --- | --- |
| Server requirements | The published requirements allow PHP 8.3 and 8.4, MariaDB 10.11 or newer, and PostgreSQL 16 or newer. CI adds PHP 8.3/MariaDB 11 and PHP 8.4/PostgreSQL 16 jobs. The plugin's minimum Moodle version stays at 4.5. |
| Completion | The inspected `completion_info` methods `is_enabled_for_site()`, `is_enabled()`, `has_criteria()`, and `get_criteria()` remain available. Existing checker tests must pass on 5.3 without developer debugging notices. |
| Quiz structure | The inspected `quiz_settings::get_structure()`, `structure::get_slots()`, `get_question_type_for_slot()`, and `add_random_questions()` remain available. The new quiz due date does not require expanding this report's existing open/close date check. |
| Navigation | Core navigation markup changed in 5.3. Browser tests now use `I navigate to "Reports" in current page administration`, which exists in both 4.5 and 5.3 core, instead of selecting `.moremenu.navigation`. Student checks use the enclosing `.secondary-navigation` container and retain direct-access denial coverage. |
| Presentation | Core's upgrade notes introduce experimental Boost dark mode. The report uses native cards, lists, badges, and text utilities, with no custom stylesheet or navigation DOM manipulation. Verify readable status colours, text, and settings links in both modes before release. |
| Security and privacy | Preparation makes no runtime changes. Course-context capability checks, normal Mustache escaping, the null privacy provider, and the ten read-only checks remain in place. |

Sources: [core upgrade notes](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/lib/UPGRADING.md), [course upgrade notes](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/course/UPGRADING.md), [quiz upgrade notes](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/mod/quiz/UPGRADING.md), [completion implementation](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/lib/completionlib.php), [quiz structure](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/mod/quiz/classes/structure.php), and [Behat navigation helper](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/lib/tests/behat/behat_navigation.php).

## Validation before declaring support

- [ ] Run the updated GitHub Actions workflow on the final candidate commit. Require all existing Moodle 4.5–5.2 jobs and both new 5.3 quality jobs to pass. The workflow can also be started manually.
- [ ] Require the Moodle 4.5, 5.2, and 5.3 Behat jobs to pass, including teacher and manager navigation, student navigation exclusion and direct-access denial, and report rendering without debugging errors.
- [ ] Verify PHP lint, Moodle coding standards, PHPDoc, plugin metadata, savepoints, Mustache lint, PHP Mess Detector, PHPUnit, and package-layout validation. Do not treat configured CI jobs as completed verification.
- [ ] Install plugin 1.0.0 on a Moodle 5.2 test site, then upgrade that site to the final Moodle 5.3 release. Confirm report access and all ten checks, and confirm that report use leaves course, activity, quiz, grade, and completion configuration unchanged.
- [ ] Check Boost light and experimental dark modes, keyboard navigation, readable status labels, heading hierarchy, score announcements, and settings links. Capture synthetic screenshots for the release review.
- [ ] Recheck core upgrade notes, deprecated APIs, privacy, capabilities, language strings, accessibility, and Marketplace requirements against the final 5.3 release.

For an existing Moodle test checkout, run from its root:

```text
vendor/bin/phpunit report/coursecoach/tests
vendor/bin/phpcs --standard=moodle report/coursecoach
```

Moodle 5.1 and later place plugin code under `public/report/coursecoach`; use the checkout's PHPUnit configuration and public plugin path where required. The repository's Moodle Plugin CI workflow manages these layout differences.

## Release after validation

1. Record the final core revision, CI run, database/PHP combinations, upgrade smoke-test outcome, and screenshots.
2. Change `$plugin->supported` from `[405, 502]` to `[405, 503]`. Keep `$plugin->requires = 2024100700` so Moodle 4.5 remains supported.
3. Increase `$plugin->version` using the actual release date and choose the next plugin release number. No database upgrade step is needed solely for a support-range change because the plugin has no tables.
4. Update README, Marketplace copy, security policy, repository guidance, and changelog consistently to include verified Moodle 5.3 support. Build and validate the installable `coursecoach` package and publish only after the checks above pass.

## Verification status at preparation

The official 5.3 branch and relevant core API definitions were inspected. The workflow YAML parsed successfully, its seven quality and three Behat matrix entries were checked, the four access/rendering scenarios were retained, and `git diff --check` passed. PHP, Composer, and a Moodle test checkout were not found in the inspected host/WSL environment; Docker access was unavailable to the WSL user. PHPUnit, Behat, Moodle code-quality tools, rendered UI checks, and CI results remain pending.
