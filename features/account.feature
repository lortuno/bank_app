# This file contains a user story for demonstration only.
# Learn how to get started with Behat and BDD on Behat's website:
# http://behat.org/en/latest/quick_start.html

Feature:
  In order to prove that accounts are only accessed by logged users
  As a user
  I want to check this is true

  Scenario: User not logged is redirected
    Given I go to "/phpinfo"
    When I should see "Login"
    Then I am on "/login"

  Scenario: User try to log with a wrong password
    Given I go to "/login"
    When I fill in "inputEmail" with "client4@example.com"
    And  I fill in "inputPassword" with "wrongpassword"
    And I press "Sign in"
    Then I should see "Invalid credentials"

  Scenario: User logged is redirected to home
    Given I go to "/phpinfo"
    When I fill in "inputEmail" with "client4@example.com"
    And  I fill in "inputPassword" with "password"
    And I press "Sign in"
    Then I go to "/"
    And I should see "Bienvenid@"
    And I should not see "Login"
    And I should see "Logout"

