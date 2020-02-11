<?php

namespace App\Controller;

use App\Helper\AccountHelper;
use App\Service\AccountManagement;
use App\Service\AccountMovement;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class AccountApi extends BaseController
{
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
            $account->removeAccount();

            return new JsonResponse('ACCOUNT_DELETED', 200);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }

    /**
     * @Route("/api/account/create", name="api_account_create")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function createAccountApi(Request $request, EntityManagerInterface $em)
    {
        try {
            $account = new AccountManagement($request, $em);
            $account->createAccount();

            return new JsonResponse('ACCOUNT_CREATED', 201);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }

    /**
     * @Route("/api/account/add/user", name="api_account_add_user")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function addUserAccountApi(Request $request, EntityManagerInterface $em)
    {
        try {
            $account = new AccountManagement($request, $em);
            $account->associateAccount();

            return new JsonResponse('ACCOUNT_ATTACHED', 200);
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
            $account->removeUserAccessToAccount();

            return new JsonResponse('USER_ACCOUNT_DELETED', 200);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }
}
