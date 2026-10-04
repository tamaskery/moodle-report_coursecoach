@report @report_coursecoach @javascript
Feature: Configure site readiness criteria
  In order to apply site readiness policy
  As a site administrator
  I need native settings that preserve defaults and disclose excluded checks

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Editing   | Teacher  | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | course | name       | completion |
      | assign   | C1     | Configured | 1          |
      | assign   | C1     | Missing    | 0          |

  Scenario: An administrator saves criteria and teachers see their effect
    Given I log in as "admin"
    And I navigate to "Plugins > Reports > Course Readiness Coach" in site administration
    Then the field "Enable: Course visibility" matches value "1"
    And the field "Minimum activity completion coverage" matches value "100%"
    When I set the field "Enable: Learner feedback" to "0"
    And I set the field "Minimum activity completion coverage" to "50%"
    And I press "Save changes"
    And I navigate to "Plugins > Reports > Course Readiness Coach" in site administration
    Then the field "Enable: Learner feedback" matches value "0"
    And the field "Minimum activity completion coverage" matches value "50%"
    And I save the Course Readiness Coach screenshot as "criteria-settings"
    When I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then I should see "Disabled by site configuration"
    And I should see "Learner feedback" in the "section[aria-labelledby='report-coursecoach-disabled-heading']" "css_element"
    And I should see "1 of 2 meaningful visible learner activities have completion configured, meeting the minimum of 50%."
    And I save the Course Readiness Coach screenshot as "configured-report"

  Scenario: Course report permission does not grant criteria administration
    Given I log in as "teacher1"
    When direct Course Readiness Coach settings access is denied
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then I should see "Readiness score"

  Scenario: Saving without a valid sesskey does not change criteria
    Given I log in as "admin"
    And I navigate to "Plugins > Reports > Course Readiness Coach" in site administration
    When I set the field "Minimum activity completion coverage" to "50%"
    And I invalidate the Course Readiness Coach settings sesskey
    And I press "Save changes"
    Then the field "Minimum activity completion coverage" matches value "100%"

  Scenario: Disabling every check leaves the course not assessed
    Given I log in as "admin"
    And I navigate to "Plugins > Reports > Course Readiness Coach" in site administration
    When I set the following fields to these values:
      | Enable: Course visibility            | 0 |
      | Enable: Course dates                 | 0 |
      | Enable: Course completion            | 0 |
      | Enable: Activity completion coverage | 0 |
      | Enable: Required activity visibility | 0 |
      | Enable: Quiz pass and completion     | 0 |
      | Enable: Quiz question randomisation  | 0 |
      | Enable: Learner feedback             | 0 |
      | Enable: Incomplete course content    | 0 |
      | Enable: Activity date alignment      | 0 |
    And I press "Save changes"
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then I should see "Not assessed"
    And I should see "No readiness checks are enabled. This course has not been assessed."
    And I should see "0 of 0 enabled checks assessed"
    And I should see "Disabled by site configuration"
    And I should not see "Checks passed"
