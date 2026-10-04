# Moodle Marketplace submission copy

## Plugin name

Course Readiness Coach

## Tagline

Open your course with greater confidence.

## Short description

Spot common course setup issues before learners do. Course Readiness Coach brings ten focused checks, a clear readiness score, and practical next steps into one Moodle report—with configurable site defaults and category-specific criteria.

## Full description

### A clearer picture before your course opens

A hidden activity. Missing completion settings. Course dates that no longer make sense. Small configuration oversights can get in the way of a smooth course launch.

**Course Readiness Coach helps teachers and course managers find common setup issues before learners encounter them.** Open the report from your course to see what passed, what needs attention, and where to go next.

### Turn findings into next steps

The report brings together a readiness score, an overall status, and plain-language explanations. Findings include practical recommendations and links to relevant Moodle settings where available, helping you move from reviewing an issue to addressing it.

Critical findings and warnings appear in the Needs attention section. Passed, not-applicable, and disabled checks remain visible, so you can understand what the report assessed.

### Set criteria that fit your site

New in version 1.1, administrators can enable or disable each check and set the minimum activity completion coverage from 1% to 100%. Use one consistent policy across your Moodle site, with all ten checks enabled and coverage set to 100% by default.

For example, disable the feedback check if your institution collects evaluations elsewhere, or disable quiz randomisation checks where fixed question sets are intentional. Disabled checks are clearly disclosed and excluded from the score.

### Different categories, different expectations

- **Course visibility:** is the course visible to learners?
- **Course dates:** do configured dates contain obvious conflicts or an expired end date?
- **Course completion:** is completion enabled, with course completion criteria configured?
- **Activity completion coverage:** do enough eligible visible activities have completion configured?
- **Required activity visibility:** are activities referenced by course completion requirements hidden?
- **Quiz pass and completion:** do quizzes used for course completion have passing-grade and completion settings?
- **Quiz question randomisation:** do those quizzes use random-question selection?
- **Learner feedback:** is a standard Moodle Feedback activity visible?
- **Incomplete course content:** are visible, non-general sections empty?
- **Activity date alignment:** do selected Quiz and Assignment dates conflict with course boundaries?

### You stay in control

Course Readiness Coach is read-only. It never changes course content, settings, grades, or learner completion records, and it does not prevent a course from opening. You decide which recommendations to act on.

The plugin uses deterministic Moodle checks. It requires no AI service, external account, API key, or third-party plugin. It sends no data outside Moodle and stores no report history or personal data. Site criteria are saved through Moodle's standard plugin configuration APIs.

### Designed for course preparation

Editing teachers and managers can access the report by default; administrators can control access through Moodle capabilities. The interface uses Moodle's native presentation and supports Boost light and dark modes where available.

The readiness score describes the configuration checked by this plugin. A Ready result means all applicable, enabled checks passed; it is not a certification of teaching quality, accessibility, security, or every aspect of course readiness. Check weights and outcome severity remain fixed. Per-course profiles and custom rules are not included.

**Before your next course opens, give its setup a focused review.**

## Version 1.1.0 release notes

- Choose which of the ten readiness checks apply across your site, with optional category-specific overrides and parent-category inheritance.
- Set the activity completion coverage target from 1% to 100%.
- See disabled checks separately, with an explicit Not assessed state when nothing can be assessed.
- Keep existing behaviour on upgrade: all checks stay enabled and coverage remains 100% by default.
- Retain the original scoring, permissions and read-only course analysis, with expanded configuration and upgrade tests.

No plugin database schema migration is required. See [CHANGES.md](../CHANGES.md) for the full version history.

## Listing fields and supporting links

- Component: `report_coursecoach`
- Category/type: Reports
- Licence: GNU GPL v3 or later
- Source: https://github.com/tamaskery/moodle-report_coursecoach
- Support and bug tracker: https://github.com/tamaskery/moodle-report_coursecoach/issues
- User and administrator guide: [user-guide.md](user-guide.md)
- Screenshots, captions and provenance: [screenshot gallery](screenshots/README.md)

## Installation and compatibility

Install the release ZIP through Moodle's plugin installer where available, then complete **Site administration > Notifications**. For manual installation, extract `coursecoach` to `report/coursecoach` on Moodle 4.5/5.0 or `public/report/coursecoach` on Moodle 5.1 and later. Authorised users open the report from the course's **More > Reports** area in the standard navigation.

Tested versions: Moodle 4.5, 5.0, 5.1, 5.2 and the 5.3 pre-release stable branch. **Publisher note:** the 4 October 2026 tests precede Moodle 5.3's scheduled final release. Recheck the final release before listing final 5.3 compatibility. Replace documentation links with public links to the released version when completing the listing.

## Reviewer notes

- Access: `report/coursecoach:view` in course context; native site configuration requires system `moodle/site:config`.
- Storage: no plugin tables, personal data, or persisted report results; site criteria use plugin configuration.
- Privacy: null Privacy API provider; no external processing. Moodle core may log normal page access independently.
- Validation: 11 passing CI jobs on implementation commit `f03bc079e1c11c94ec647bec07f2b7b776a2a220`, including PHPUnit, browser tests, packaging and upgrade checks. MariaDB and PostgreSQL tested.
- Evidence: https://github.com/tamaskery/moodle-report_coursecoach/actions/runs/37230012946
- Screenshots: authentic output from a synthetic acceptance-test site; see gallery for capture details and limitations.
