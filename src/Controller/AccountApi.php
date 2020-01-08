<?php

namespace App\Controller;

use App\Service\AccountHelper;
use App\Service\AccountManagement;
use App\Service\AccountMovement;
use App\Service\UserManagement;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
            $accountOperator->saveAccountMovement();

            return new JsonResponse('MOVEMENT_SUCCESSFUL', 201);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }

    /**
     * @Route("/api/account/{account_id}/remove", name="api_account_remove")
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
     * @Route("/api/user/{user_id}/remove", name="api_user_remove")
     */
    public function removeUserApi(Request $request, EntityManagerInterface $em)
    {
        try {
            $user = new UserManagement($request, $em);
            $user->removeUser();

            return new JsonResponse('USER_DELETED', 201);
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
