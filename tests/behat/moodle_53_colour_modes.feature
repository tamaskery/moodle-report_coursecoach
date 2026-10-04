@report @report_coursecoach @report_coursecoach_53 @javascript
Feature: Display Course Readiness Coach in Moodle 5.3 colour modes
  In order to review course readiness in my chosen colour mode
  As an editing teacher
  I need readable report content and working settings links

  Scenario Outline: An editing teacher reads the report in Boost colour modes
    Given the following config values are set as admin:
      | enablecolourmodes  | 1      | theme_boost |
      | defaultcolourmode | <mode> | theme_boost |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Editing   | Teacher  | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | visible |
      | Course 1 | C1        | 0        | 0                | 0       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then "html[data-bs-theme='<mode>']" "css_element" should exist
    And I should see "Readiness score"
    And I should see "Not ready"
    And I should see "Course visibility"
    And I should see "Course completion"
    And "div[role='progressbar'][aria-valuemin='0'][aria-valuemax='100']" "css_element" should exist
    And I save the Course Readiness Coach screenshot as "<mode>"
    When I set the focus on the "Edit course visibility" "link"
    And I press the enter key
    Then "#id_fullname" "css_element" should exist

    Examples:
      | mode  |
      | light |
      | dark  |
