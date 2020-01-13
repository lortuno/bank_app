<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\UserRegistrationFormModel;
use App\Form\UserRegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\UserManagement;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('Will be intercepted before getting here');
    }

    /**
     * @Route("/register", name="app_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $em
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $formAuthenticator
     * @param FormFactoryInterface $formFactory
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $formAuthenticator,
        FormFactoryInterface $formFactory
    ) {
        $userManager = new UserManagement($request, $em, $passwordEncoder, $guardHandler, $formFactory);

        try {
            $user = $userManager->registerUser();

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $formAuthenticator,
                'main'
            );
        } catch (\Exception $exception) {
            $form = $this->createForm(UserRegistrationFormType::class);
            $form->handleRequest($request);

            return $this->render('security/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }
    }
}
