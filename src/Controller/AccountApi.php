<?php

namespace App\Controller;

use App\Helper\AccountHelper;
use App\Service\AccountManagement;
use App\Service\AccountMovement;
use App\Service\UserManagement;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;


class AccountApi extends BaseController
{
    /**
     * @Route("/api/account", name="api_account")
     */
    public function accountApi()
    {
        $user = $this->getUser();

        return $this->json($user, 200, [], [
            'groups' => ['main'],
        ]);
    }

    /**
     * @Route("/api/account/make_movement", name="api_account_make_movement", methods={"PATCH","POST"})
     */
    public function changeAccountMoneyApi(Request $request, EntityManagerInterface $em)
    {
        try {
            $accountOperator = new AccountMovement($request, $em);
            $accountOperator->setOperationProperties();
            $accountOperator->saveAccountMovement();

            return new JsonResponse('MOVEMENT_SUCCESSFUL', 201);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }

    /**
     * @Route("/api/account/remove", name="api_account_remove")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function removeAccountApi(Request $request, EntityManagerInterface $em)
    {
        try {
            $account = new AccountManagement($request, $em);
            $account->setAccountRequested();
            $account->removeAccount();

            return new JsonResponse('ACCOUNT_DELETED', 200);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }

    /**
     * @Route("/api/user/remove", name="api_user_remove")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param FormFactoryInterface $formFactory
     * @return JsonResponse
     */
    public function removeUserApi(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        FormFactoryInterface $formFactory
    ) {
        try {
            $user = new UserManagement($request, $em, $passwordEncoder, $guardHandler, $formFactory);
            $user->setUserRequested();
            $user->removeUser();

            return new JsonResponse('USER_DELETED', 200);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }

    /**
     * @Route("/api/user/create", name="api_user_create")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param FormFactoryInterface $formFactory
     * @return JsonResponse
     */
    public function createUserApi(
         Request $request,
         EntityManagerInterface $em,
         UserPasswordEncoderInterface $passwordEncoder,
         GuardAuthenticatorHandler $guardHandler,
         FormFactoryInterface $formFactory
    ) {
        try {
            $user = new UserManagement($request, $em, $passwordEncoder, $guardHandler, $formFactory);
            $user->registerUser(false);

            return new JsonResponse('USER_CREATED', 201);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }

    /**
     * @Route("/api/account/remove_user", name="api_user_account_remove")
     */
    public function removeAccountUserAccessApi(Request $request, EntityManagerInterface $em)
    {
        try {
            $account = new AccountManagement($request, $em);
            $account->setAccountRequested();
            $account->removeUserAccessToAccount();

            return new JsonResponse('USER_ACCOUNT_DELETED', 200);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }
}
