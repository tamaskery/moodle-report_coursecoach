@report @report_coursecoach @javascript
Feature: Configure category readiness criteria
  In order to review different course categories appropriately
  As a site administrator
  I need inherited category policies with unchanged site defaults

  Background:
    Given the following "categories" exist:
      | name     | category | idnumber |
      | Webinars | 0        | WEB      |
      | Series   | WEB      | SERIES   |
    And the following "courses" exist:
      | fullname     | shortname | category |
      | Webinar demo | WEB1      | SERIES   |

  Scenario: A category override applies to descendants and can be removed
    Given I log in as "admin"
    And I navigate to "Plugins > Reports > Course Readiness Coach" in site administration
    And I click on "Manage category readiness criteria" "link"
    When I set the field "Category" to "Webinars"
    And I set the field "Criteria policy" to "Use a category override"
    And I set the field "Enable: Learner feedback" to "0"
    And I set the field "Minimum activity completion coverage" to "50%"
    And I press "Save changes"
    Then the field "Criteria policy" matches value "Use a category override"
    And I save the Course Readiness Coach screenshot as "category-criteria"
    When I am on "Webinar demo" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then I should see "Readiness policy: category Webinars"
    And I should see "Disabled by category configuration"
    And I save the Course Readiness Coach screenshot as "category-report"
    When I navigate to "Plugins > Reports > Course Readiness Coach" in site administration
    And I click on "Manage category readiness criteria" "link"
    And I set the field "Category" to "Webinars"
    And I set the field "Criteria policy" to "Inherit from parent category or site"
    And I press "Save changes"
    Then the field "Criteria policy" matches value "Inherit from parent category or site"
    When I am on "Webinar demo" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then I should see "Readiness policy: site defaults."
    And I should not see "Disabled by category configuration"

  Scenario: Category configuration is restricted to site administrators
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher  | Demo      | Teacher  | teacher@example.com |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | WEB1   | editingteacher |
    And I log in as "teacher"
    When direct Course Readiness Coach category settings access is denied
    And I am on "Webinar demo" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Course Readiness Coach" "link"
    Then I should see "Readiness score"

  Scenario: Invalid sesskey cannot save a category override
    Given I log in as "admin"
    And I navigate to "Plugins > Reports > Course Readiness Coach" in site administration
    And I click on "Manage category readiness criteria" "link"
    And I set the field "Category" to "Webinars"
    And I set the field "Criteria policy" to "Use a category override"
    When I submit Course Readiness Coach category criteria with an invalid sesskey
    Then the field "Criteria policy" matches value "Inherit from parent category or site"
