# Course Readiness Coach — User and administrator guide

For version 1.1.0 (`report_coursecoach`).

## Install or upgrade

1. Back up your Moodle installation and try the release on a test site first.
2. Install the release ZIP through **Site administration > Plugins > Install plugins**, where permitted. For manual installation, place the package's `coursecoach` directory at `report/coursecoach` on Moodle 4.5/5.0, or `public/report/coursecoach` on Moodle 5.1 and later.
3. Visit **Site administration > Notifications** and complete Moodle's prompts.
4. Open a course report to confirm access and review the site criteria below.

Upgrading `report_coursecoach` from 1.0.1 with default criteria preserves check outcomes and scoring. No plugin database tables or schema migration are needed. For the older, separate `local_coursecoach` component, see the migration note in the [README](../README.md).

Testing covers Moodle 4.5–5.3. The 5.3 tests used the pre-release stable branch on 4 October 2026; see the [release checklist](moodle-5.3.md) before claiming final-release compatibility.

## Review a course

1. Open your course as an editing teacher, manager, or another authorised user.
2. Open **More > Reports > Course Readiness Coach** in the standard course navigation. Navigation may vary with the theme.
3. Read the overall status, score, and assessed-check count.
4. Start with **Needs attention**. Read each explanation and recommendation. Where available, a button opens the relevant Moodle settings.
5. Make any intended changes in Moodle, then reload the report to evaluate the updated configuration.

Report access requires `report/coursecoach:view` in the course context. Editing teachers and managers have this capability by default; students do not. Settings links still require the permissions for their destination page.

## Understand the results

| Overall status | Meaning |
| --- | --- |
| Ready | Every applicable, enabled check passed. |
| Needs attention | A warning exists, or the score is below 100 without a critical finding. |
| Not ready | At least one critical finding exists, regardless of the score. |
| Not assessed | No enabled check was applicable, including when every check is disabled. |

The score uses fixed check weights. Passed earns full credit, Warning earns half, and Critical earns none. Not-applicable and disabled checks are excluded. A Not assessed report displays 0; this is not a measured failure score.

**Not applicable** means the course does not meet a check's evaluation conditions. **Disabled by site configuration** means an administrator switched the check off. Neither means the check passed.

A Ready result supports course preparation. It does not certify teaching quality, accessibility, security, or suitability for a particular programme. The plugin never changes a course or blocks course access.

## Configure site criteria

1. Open **Site administration > Plugins > Reports > Course Readiness Coach**.
2. Under **Readiness criteria**, enable the checks that fit your site's course preparation process.
3. Choose the **Minimum activity completion coverage** percentage if needed.
4. Select **Save changes**. Reopen a course report to see the effect.

This page requires system-level `moodle/site:config`. Site settings are the fallback for all courses; site administrators can override them by category. Teachers cannot override them per course. All ten checks are enabled by default, and coverage defaults to 100%.

| Check | What it looks for | Available setting |
| --- | --- | --- |
| Course visibility | Whether the course is visible to learners | Enable/disable |
| Course dates | Invalid date order or an expired end date; dates are not mandatory | Enable/disable |
| Course completion | Site/course completion enabled and course completion criteria configured | Enable/disable |
| Activity completion coverage | Completion configured for eligible visible activities | Enable/disable and minimum 1–100% |
| Required activity visibility | Hidden activities referenced by course completion requirements | Enable/disable |
| Quiz pass and completion | A positive passing grade and automatic pass-grade completion for quizzes used in course completion | Enable/disable |
| Quiz question randomisation | Random-question selection in quizzes used in course completion | Enable/disable |
| Learner feedback | A visible standard Moodle Feedback activity | Enable/disable |
| Incomplete course content | Visible, empty sections other than the general section | Enable/disable |
| Activity date alignment | Selected Quiz/Assignment opening and closing date conflicts with course boundaries | Enable/disable |

Coverage counts visible activities for which Moodle reports completion rules or grading support. Manual and automatic completion both count. Passive resources are not automatically included. One of two eligible activities with completion meets a 50% target but not a 100% target. The comparison uses the exact ratio. No eligible activities, or disabled course completion, makes this check Not applicable.

Disable a check when it does not fit your site policy: for example, quiz randomisation where fixed question sets are intentional, or learner feedback where evaluation is collected elsewhere. Disabling a check changes what the score represents. Related checks remain independent and use the actual course configuration.

To restore the original policy, enable all ten checks and set coverage to 100%. Severity, weights and overall status rules are fixed in 1.1.0. There are no readiness profiles or custom rules.

## Override criteria for a category

- **Missing report or access denied:** check `report/coursecoach:view` in the course context and confirm installation completed.
- **Completion checks are Not applicable:** review site/course completion settings and the relevant completion criteria.
- **Feedback tool not recognised:** the check looks for a visible standard Moodle Feedback activity, not external surveys or other activity types.
- **Score changed after saving criteria:** site policy affects subsequent reports. Review the disabled-check list and coverage target.
- **Date or availability issue not reported:** date checks deliberately exclude relative Assignment dates, due dates and learner-specific availability.

## Privacy and support

The report analyses course configuration rather than learner performance. It stores no report history or personal data, sends no information externally and needs no AI service or API key. Administrator criteria use Moodle's plugin configuration APIs. Moodle core may record normal page access in its own logs.

Use the [public issue tracker](https://github.com/tamaskery/moodle-report_coursecoach/issues) for support. Include Moodle/plugin versions, steps to reproduce, expected and actual behaviour, and a screenshot with personal information removed.

See the [changelog](../CHANGES.md) and [screenshot gallery](screenshots/README.md).
