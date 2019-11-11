<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use App\Entity\UserDeleted;
use App\Service\AccountHelper;
use App\Service\AccountManagement;
use App\Service\AccountMovement;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @Route("/api/account/{id}/remove", name="api_account_remove")
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
     * @Route("/api/user/{id}/remove", name="api_user_remove")
     */
    public function removeUserApi(Request $request, EntityManagerInterface $em)
    {
        $id = $request->get('id');

        $userRepository = $em->getRepository(User::class);
        $user = $userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('USER_NOT_FOUND');
        }

        try {
            AccountHelper::insertUserDeleted($user, $request, $em);

            $em->remove($user);
            $em->flush();

            return new JsonResponse('USER_DELETED', 201);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Error on delete ' . $e->getMessage());
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
