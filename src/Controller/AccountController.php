<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use App\Entity\UserDeleted;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 */
class AccountController extends BaseController
{

    /**
     * @Route("/", name="app_account")
     */
    public function index(LoggerInterface $logger)
    {
        $logger->debug('Checking account page for '.$this->getUser()->getEmail());
        return $this->render('account/index.html.twig', [
            'client' => $this->getUser(),
        ]);
    }

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
            if ($this->insertUserDeleted($user, $request, $em)) {
                $em->remove($user);
                $em->flush();
            }

            return new JsonResponse('USER_DELETED', 201);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Error on delete ' . $e->getMessage());
        }
    }

    private function insertUserDeleted($user, Request $request, EntityManagerInterface $em)
    {
        $now = new \DateTime();
        $userDeleted = new UserDeleted();
        $userDeleted->setEmail($user->getEmail());
        $userDeleted->setUserId($user->getId());
        $userDeleted->setDate($now);
        $userDeleted->setReason($request->get('reason'));

        try {
            $em->persist($userDeleted);
            $em->flush();
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Error during User Delete creation');
        }
    }
}
