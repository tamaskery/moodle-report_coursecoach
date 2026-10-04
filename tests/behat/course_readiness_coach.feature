@report @report_coursecoach @javascript
Feature: Access and render the Course Readiness Coach report
  In order to prepare a course for learners
  As an authorised course staff member
  I need to access the Course Readiness Coach report

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Editing   | Teacher  | teacher1@example.com |
      | manager1 | Course    | Manager  | manager1@example.com |
      | student1 | Course    | Student  | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "role assigns" exist:
      | user     | role    | contextlevel | reference |
      | manager1 | manager | System       |           |

  Scenario: An editing teacher opens Course Readiness Coach from course navigation
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then I should see "Course Readiness Coach" in the "h1" "css_element"
    And I should see "Readiness score"

  Scenario: A manager opens Course Readiness Coach for a course
    Given I log in as "manager1"
    And I am on "Course 1" course homepage
    When I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then I should see "Course Readiness Coach" in the "h1" "css_element"
    And I should see "Readiness score"

  Scenario: A student cannot see or directly access Course Readiness Coach
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    Then "Reports" "link" should not exist in the ".secondary-navigation" "css_element"
    And "Course Readiness Coach" "link" should not exist
    When direct Course Readiness Coach access for course "C1" is denied

  Scenario: The report renders a deterministic readiness result without debugging errors
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then I should see "Readiness score"
    And I should see "Summary"
    And I should see "Course visibility"
    And I should see "The course is visible to learners."
    And I should not see "Exception"
    And I should not see "Debug info"
