@local @local_chuspasocial
Feature: Access to the ChuspaSocial wall
  In order to use the social wall
  As a student
  I need to be able to open the ChuspaSocial page

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |

  Scenario: A student visits the wall page
    Given I log in as "student1"
    When I visit "/local/chuspasocial/index.php"
    Then I should see "ChuspaSocial"

  Scenario: A student filters the wall posts by clicking on a tag
    Given I log in as "student1"
    And I visit "/local/chuspasocial/index.php"
    And I set the field "Message" to "Publicacion sobre literatura"
    And I set the field "Tags" to "literatura"
    And I press "Publish"
    And I set the field "Message" to "Publicacion sobre ciencia"
    And I set the field "Tags" to "ciencia"
    And I press "Publish"
    And I should see "Publicacion sobre literatura"
    And I should see "Publicacion sobre ciencia"
    When I click on "literatura" "link"
    Then I should see "Publicacion sobre literatura"
    And I should not see "Publicacion sobre ciencia"