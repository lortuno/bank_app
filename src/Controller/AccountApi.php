<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use App\Entity\UserDeleted;
use App\Service\AccountHelper;
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
     * const integer DEFAULT_STATUS_CODE_ERROR Status http por defecto cuando hay error.
     */
    const DEFAULT_STATUS_CODE_ERROR = 404;
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
            $accountRepository = $em->getRepository(Account::class);
            $bankAccount = $accountRepository->find($request->request->get('account_id'));
            $bankAccount->saveAccountMovement($request, $em);

            return new JsonResponse('MOVEMENT_SUCCESS', 201);
        } catch(\Exception $e) {
            $code = ($e->getCode() > 0) ? $e->getCode() : self::DEFAULT_STATUS_CODE_ERROR;
            return new JsonResponse($e->getMessage(), $code );
        }
    }

    /**
     * @Route("/api/account/{id}/remove", name="api_account_remove")
     */
    public function removeAccountApi(Request $request, EntityManagerInterface $em)
    {
        $id = $request->get('id');

        $accountRepository = $em->getRepository(Account::class);
        $account = $accountRepository->find($id);

        if (!$account) {
            throw new NotFoundHttpException('ACCOUNT_NOT_FOUND');
        }

        try {
           $account->setStatus(Account::INACTIVE_STATUS);
           $em->flush();

           return new JsonResponse('ACCOUNT_DELETED', 200);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Error on delete');
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
        $accountId = $request->request->get('account_id');
        $userId = $request->request->get('user_id');

        if ($userId && $accountId) {
            $accountRepository = $em->getRepository(Account::class);
            $account = $accountRepository->find($accountId);


            if (!$account) {
                throw new NotFoundHttpException('ACCOUNT_NOT_FOUND');
            }

            $userRepository = $em->getRepository(User::class);
            $user = $userRepository->find($userId);

            if (!$user) {
                throw new NotFoundHttpException('USER_NOT_FOUND');
            }
        }


        try {
            $account->removeUser($user);
            $em->flush();

            return new JsonResponse('USER_ACCOUNT_DELETED', 200);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Error on delete');
        }
    }


}
