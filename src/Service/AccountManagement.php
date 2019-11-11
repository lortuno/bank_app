<?php

namespace App\Service;


use App\Entity\Account;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class AccountManagement
{
    protected $em;
    protected $request;
    private $account;
    private $userId;

    /**
     * AccountManagement constructor.
     * @param Request $request
     * @param EntityManagerInterface $em
     * @throws \Exception
     */
    public function __construct(Request $request, EntityManagerInterface $em)
    {
        $id = $request->get('account_id');
        $accountRepository = $em->getRepository(Account::class);
        $account = $accountRepository->find($id);

        $this->setAccount($account);
        $this->checkAccountExists();
        $this->em = $em;
        $this->setRequest($request);
        $this->setOperationProperties();
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request): void
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account): void
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    private function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    private function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function checkAccountExists()
    {
        if (!$this->account) {
            throw new \Exception('Account requested does not exist', 404);
        }

        return true;
    }

    private function setOperationProperties()
    {
        $this->setUserId($this->request->request->get('user_id'));
    }

    /**
     * @throws \Exception
     */
    public function removeAccount()
    {
        try {
            $this->account->setStatus(Account::INACTIVE_STATUS);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception('Error on deleting account ' . $e->getMessage(), 500);
        }
    }

    /**
     * @throws \Exception
     */
    public function removeUserAccessToAccount()
    {
        if ($this->getUserId() && $this->getAccount()) {
            $userRepository = $this->em->getRepository(User::class);
            $user = $userRepository->find($this->getUserId());
            $this->checkUserExists($user);
        }

        try {
            $this->account->removeUser($user);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception('Error on deleting user ' . $e->getMessage(), 500);
        }
    }

    /**
     * @param $user
     * @throws \Exception
     */
    private function checkUserExists($user)
    {
        if (!$user) {
            throw new \Exception('USER_NOT_FOUND');
        }
    }


}