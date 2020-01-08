Feature:
  In order to prove that accounts are only accessed by logged users
  As a user
  I want to check this is true

  Background: User is logged
    Given I go to "/login"
    When I fill in "inputEmail" with "client4@example.com"
    And  I fill in "inputPassword" with "password"
    And I press "Sign in"
    Then I should be on "/"

  Scenario: User gets info json
    Given I go to "/api/account"
    Then print last response
    Then the response should be in JSON
    And the JSON node "email" should contain "client4@example.com"

  Scenario: User creation
    Given I follow "Editar"
    Then I should see "Edita Tu perfil"
