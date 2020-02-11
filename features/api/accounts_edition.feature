Feature:
  In order to prove that accounts are managed correctly
  As a user
  I want to send requests that give me a proper answer

  Background: User is logged
    Given I go to "/login"
    When I fill in "inputEmail" with "test_client1@example.com"
    And  I fill in "inputPassword" with "password"
    And I press "Sign in"
    Then I should be on "/"


  Scenario: User adds account
    Given I send a POST request to "/api/account/create" with parameters:
      | key     | value      |
      | email    | test_client1@example.com  |
    Then the response status code should be 201
    And show last response
    And the response should be in JSON
    And the response should contain "ACCOUNT_CREATED"

  Scenario: Creating account without sending params
    Given I send a POST request to "/api/account/create" with parameters:
      | key     | value      |
    Then the response status code should be 404
    And the response should be in JSON
    And show last response
    And the response should contain "USER_NOT_FOUND"
