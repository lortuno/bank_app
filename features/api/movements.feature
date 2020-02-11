Feature:
  In order to prove that movements are managed correctly
  As a user
  I want to send requests that give me a proper answer

  Background: User is logged
    Given I go to "/login"
    When I fill in "inputEmail" with "client4@example.com"
    And  I fill in "inputPassword" with "password"
    And I press "Sign in"
    Then I should be on "/"

  Scenario: User does not own account, but account exists
    Given I send a POST request to "/api/account/make_movement" with parameters:
      | key     | value      |
      | account_number    | test3333  |
      | email    | test_client1@example.com  |
      | operation_type  | take  |
      | money      | 50    |
    Then the response status code should be 403
    And the response should be in JSON
    And the response should contain "User test_client1@example.com does not own account"

  Scenario: User does not own account, and account does not exist
    Given I send a POST request to "/api/account/make_movement" with parameters:
      | key     | value      |
      | account_number    | notfound630  |
      | email    | test_client1@example.com  |
      | operation_type  | take  |
      | money      | 50    |
    Then the response status code should be 404
    And the response should be in JSON
    And the response should contain "Account requested does not exist"

  Scenario: Account exists, but User does not
    Given I send a POST request to "/api/account/make_movement" with parameters:
      | key     | value      |
      | account_number    | test1234  |
      | email    | 10  |
      | operation_type  | take  |
      | money      | 50    |
    Then the response status code should be 404
    And the response should be in JSON
    And the response should contain "USER_NOT_FOUND"

  Scenario: Account and user exist, but operation type does not
    Given I send a POST request to "/api/account/make_movement" with parameters:
      | key     | value      |
      | account_number    | test1234  |
      | email    | test_client1@example.com  |
      | operation_type  | withdraw  |
      | money      | 50    |
    Then the response status code should be 404
    And the response should be in JSON
    And the response should contain "Operation type not allowed: withdraw"

  Scenario: User takes too much money for the app
    Given I send a POST request to "/api/account/make_movement" with parameters:
      | key     | value      |
      | account_number    | test1234  |
      | email    | test_client1@example.com  |
      | operation_type  | take  |
      | money      | 100000000    |
    Then the response status code should be 403
    And the response should be in JSON
    And the response should contain "User cannot make this op through the app"

  Scenario: User gives too much money for the app
    Given I send a POST request to "/api/account/make_movement" with parameters:
      | key     | value      |
      | account_number    | test1234  |
      | email    | test_client1@example.com  |
      | operation_type  | give  |
      | money      | 2001    |
    Then the response status code should be 403
    And the response should be in JSON
    And the response should contain "User cannot make this op through the app"

  Scenario: User takes more money that he has
    Given I send a POST request to "/api/account/make_movement" with parameters:
      | key     | value      |
      | account_number    | test1234  |
      | email    | test_client1@example.com  |
      | operation_type  | take  |
      | money      | 1999    |
    Then the response status code should be 403
    And the response should be in JSON
    And the response should contain "User does not have enough money"

  Scenario: User takes money he has and it works
    Given I send a POST request to "/api/account/make_movement" with parameters:
      | key     | value      |
      | account_number    | test1234  |
      | email    | test_client1@example.com  |
      | operation_type  | take  |
      | money      | 1    |
    Then the response status code should be 201
    And the response should be in JSON
    And the response should contain "MOVEMENT_SUCCESSFUL"

  Scenario: User gives money and it works
    Given I send a POST request to "/api/account/make_movement" with parameters:
      | key     | value      |
      | account_number    | test1234  |
      | email    | test_client1@example.com  |
      | operation_type  | give  |
      | money      | 50    |
    Then the response status code should be 201
    And the response should be in JSON
    And the response should contain "MOVEMENT_SUCCESSFUL"
