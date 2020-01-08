Feature:
  In order to prove that accounts are only accessed by logged users
  As a user
  I want to check this is true

  Background: User is logged
    Given I go to "/login"
    When I fill in "inputEmail" with "client4@example.com"
    And  I fill in "inputPassword" with "password"
    And I press "Sign in"
    Then I go to "/"
    And I should see "Bienvenid@"
    And I should not see "Login"
    And I should see "Logout"

  Scenario: User edition saves data correctly
    Given I follow "Editar"
    Then I should see "Edita Tu perfil"
    When I fill in "user_form_city" with "My city log"
    And I press "Guardar"
    Then I should see "Usuario actualizado con éxito"
    And the "#user_form_city" value must be "My city log"

  Scenario: User edition saves data correctly
    Given I follow "Editar"
    Then I should see "Edita Tu perfil"
    When I fill in "user_form_city" with "My city log"
    And I press "Guardar"
    Then I should see "Usuario actualizado con éxito"
    And the "#user_form_city" value must be "My city log"