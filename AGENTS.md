# Repository Guidelines

## Scope and Sources of Truth

This repository contains the Moodle report plugin `report_coursecoach`, targeting Moodle 4.5 through 5.3. Follow the [official Moodle developer documentation](https://moodledev.io/) and Moodle core implementation; treat both as authoritative. When implementing unfamiliar Moodle functionality, inspect the relevant core code before designing an alternative. Check every change across the supported version range, avoid deprecated APIs when supported replacements exist, and follow Moodle coding standards and Frankenstyle naming throughout.

The product goal is: "Before opening a Moodle course to learners, Course Readiness Coach tells the teacher whether the course is technically ready and explains what should be fixed." The initial MVP is strictly read-only: it must never modify course configuration. Do not expand functionality beyond the requested scope.

## Structure and Architecture

Keep `lib.php` limited to required Moodle callbacks. Put application logic in autoloaded classes under `classes/`, tests in `tests/`, Mustache templates in `templates/`, and English strings in `lang/en/report_coursecoach.php`.

Implement each course-quality check as an independent checker class under `classes/check/`. Every check must return the same result contract:

- `applicability`
- `status`
- `severity`
- `title`
- `explanation`
- `recommendation`
- optional Moodle settings or fix URL

Individual check logic must not appear in report pages, renderers, output classes, or templates. The report layer only coordinates checkers and consumes their results.

## Moodle Implementation Standards

Prefer Moodle APIs over custom implementations whenever an appropriate API exists. Use `moodle_url` for internal URLs, language strings for all user-facing text, and the Output API plus Mustache templates for presentation. Avoid external UI frameworks unless a compelling requirement is documented. Keep presentation logic out of templates and domain logic out of renderers.

Use the `report_coursecoach` namespace and Frankenstyle component names consistently for classes, capabilities, strings, configuration, and database tables. Ensure distributed plugin contents and metadata meet current Moodle Marketplace/plugin contribution requirements.

## Security, Access, and Privacy

Use Moodle capabilities at the correct context; do not substitute role or administrator checks. Call `require_login()` where appropriate and enforce capabilities both before displaying protected features and while handling requests. Read input through `required_param()` or `optional_param()` with the narrowest suitable `PARAM_*` type. Protect every state-changing action with a sesskey, including any future action outside the read-only MVP.

Use Moodle database APIs and parameterised queries; never interpolate request data into SQL. Prefer not to store personal or learner-level data. If personal data is stored, processed, exported, or deleted, implement the applicable Moodle Privacy API provider requirements.

## Efficient development

Use repository context efficiently while maintaining code quality.

- Inspect only the files and Moodle APIs relevant to the current task before broadening the search.
- Do not repeatedly re-analyse the entire repository when the relevant architecture is already established.
- Reuse existing abstractions and patterns rather than recreating them.
- Keep changes narrowly scoped to the requested feature.
- Do not rewrite working code without a concrete reason.
- Run targeted tests during implementation and the broader relevant validation suite before completion.
- Prefer deterministic verification through tests, Moodle APIs and static analysis over extended speculative reasoning.
- Never sacrifice security, Moodle compatibility, coding standards or correctness to reduce model usage.

## Testing and Completion Gate

Add PHPUnit coverage for checker logic, including applicable, non-applicable, passing, warning, and failure paths as relevant. Tests must use Moodle generators and remain deterministic. Run the tests and quality tools available in the host Moodle checkout, typically:

- `vendor/bin/phpunit report/coursecoach/tests`
- `vendor/bin/phpcs --standard=moodle report/coursecoach`

Before declaring work complete, review Moodle coding standards, capabilities and security, Moodle 4.5-5.3 compatibility, deprecated API usage, language strings, accessibility, and Marketplace requirements. Run available tests plus Moodle code-quality/static-analysis tools, and report any checks that could not be run. Keep commits focused; pull requests must describe scope, verification, compatibility impact, and screenshots for visible UI changes.
