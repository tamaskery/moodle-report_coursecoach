# Submission screenshots

These six original PNGs show Course Readiness Coach 1.1.0 on a synthetic Moodle 5.3 acceptance-test site using Boost. They were visually reviewed and copied without editing, compositing or generated content. Names such as Course 1, Editing Teacher and Admin User belong to test fixtures, not real learners.

## Suggested order and captions

| File | Caption |
| --- | --- |
| [01-readiness-overview.png](01-readiness-overview.png) | See your course's readiness score, critical findings and practical next steps in one report. |
| [02-configured-report.png](02-configured-report.png) | Apply your site's criteria: this example uses a 50% completion target and clearly discloses the disabled feedback check. |
| [03-readiness-criteria.png](03-readiness-criteria.png) | Choose which checks apply across your Moodle site and set the activity completion coverage target. |
| [04-dark-mode.png](04-dark-mode.png) | Review the same findings in Moodle Boost dark mode. |
| [05-category-criteria.png](05-category-criteria.png) | Give a category its own policy, with inheritance for subcategories. |
| [06-category-report.png](06-category-report.png) | See which category policy applies and which checks it excludes. |

## Capture provenance

- Plugin: 1.1.0, version 2026100402.
- Implementation commit: `a5772e90ac4ef35e4ac698afd256446fb060b090`.
- Moodle: 5.3 pre-release stable branch; settings screenshot shows Build 20261005. This is not evidence of final-release availability.
- Capture date: 4 October 2026.
- Workflow: https://github.com/tamaskery/moodle-report_coursecoach/actions/runs/37231932815
- Artifact name: `moodle-5.3-report-screenshots`, downloadable from the workflow run.
- Source mapping: light.png, configured-report.png, criteria-settings.png, dark.png, category-criteria.png, category-report.png respectively.
- Resolution: 1432 × 3053 pixels each.

## Submission use

Use the first image as the overview, the second to illustrate configurable criteria, and the third for the administrator experience. Use the fifth and sixth to show category configuration and inheritance. The fourth is optional.

These are full-page CI captures: they include the acceptance-test site name, generic course names, page footers and unused space. The administrator images include Moodle's developer footer. They are accurate supporting screenshots; a later capture on a polished synthetic demo site would improve presentation. There is no captured all-passing Ready example in this set.

If replacing them, use genuine output with synthetic data only. Exclude personal information, credentials and institutional branding, keep findings accurate, and record the capture versions. Do not fabricate or composite a Ready result.
