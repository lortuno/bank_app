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


  Scenario: User tries to remove account that does not exist
    Given I send a POST request to "/api/account/remove" with parameters:
      | key     | value      |
      | account_number    | 404  |
      | email    | test_client1@example.com  |
    Then the response status code should be 404
    And the response should be in JSON
    And the response should contain "Account requested does not exist"

  Scenario: User removes account that does not belong to him.
    Given I send a POST request to "/api/account/remove" with parameters:
      | key     | value      |
      | account_number    | test3333  |
      | email    | test_client1@example.com  |
    Then the response status code should be 403
    And the response should be in JSON
    And the response should contain "User test_client1@example.com does not own account"

  Scenario: User removes account that does belong to him.
    Given I send a POST request to "/api/account/remove" with parameters:
      | key     | value      |
      | account_number    | test2234  |
      | email    | test_client1@example.com  |
    Then the response status code should be 200
    And the response should be in JSON
    And the response should contain "ACCOUNT_DELETED"

  Scenario: User removes account again, and it is not active.
    Given I send a POST request to "/api/account/remove" with parameters:
      | key     | value      |
      | account_number    | test2234  |
      | email    | test_client1@example.com  |
    Then the response status code should be 403
    And the response should be in JSON
    And the response should contain "This account is not currently active"
