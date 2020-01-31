<?php

namespace App\Service;

use Exception;
use App\Entity\Account;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Faker\Factory;

class AccountManagement
{
    protected $em;
    protected $request;
    protected $faker;
    private $account;
    private $userId;

    /**
     * AccountManagement constructor.
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Factory $factory
     */
    public function __construct(Request $request, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->request = $request;
        $this->faker = Factory::create();
    }

    /**
     * @throws Exception
     */
    public function setAccountRequested()
    {
        $id = $this->request->request->get('account_id');
        $accountRepository = $this->em->getRepository(Account::class);
        $account = $accountRepository->find($id);

        $this->setAccount($account);
        $this->checkAccountExists();
        $this->setOperationProperties();
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
    protected function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    protected function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function checkAccountExists()
    {
        if (!$this->account) {
            throw new Exception('Account requested does not exist', 404);
        }

        return true;
    }

    private function setOperationProperties()
    {
        $userId = $this->request->request->get('user_id');
        $this->setUserId($userId);
    }

    public function createAccount()
    {
        $date = new \DateTime();
        $account = new Account();
        $account->setNumber($this->faker->bankAccountNumber . $this->faker->citySuffix);
        $account->setCreated($date);
        $account->setModified($date);
        $account->setStatus(Account::ACTIVE_STATUS);
        $account->setMoney(0);

        try {
            $this->em->persist($account);
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Error on creating account ' . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function removeAccount()
    {
        try {
            $this->checkUserOwnsAccount();
            $this->checkActiveAccount();
        } catch (Exception $exception) {
            throw new Exception( $exception->getMessage(), $exception->getCode());
        }

        try {
            $this->account->setStatus(Account::INACTIVE_STATUS);
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Error on deleting account ' . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function associateAccount()
    {
        try {
            $this->setOperationProperties();
            $accountId = $this->request->request->get('account_id');
            $account = $this->em->getRepository(Account::class)->findOneBy(['id' => $accountId]);

            if (!$account) {
                throw new Exception('Error; the account does not exist.', 404);
            }
        } catch (Exception $exception) {
            throw new Exception( $exception->getMessage(), $exception->getCode());
        }

        try {
            $user = $this->em->getRepository(User::class)->find($this->getUserId());
            $this->checkUserExists($user);
            $account->addUser($user);
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Error on associating user to account ' . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function removeUserAccessToAccount()
    {
        try {
            $this->checkUserOwnsAccount();
        } catch (Exception $exception) {
            throw new Exception( $exception->getMessage(), $exception->getCode());
        }

        try {
            $userRepository = $this->em->getRepository(User::class);
            $user = $userRepository->find($this->getUserId());
            $this->checkUserExists($user);

            $this->account->removeUser($user);
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Error on deleting user ' . $e->getMessage(), 500);
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function checkUserOwnsAccount()
    {
        $userId = $this->getUserId();

        if ($users = $this->getAccount()->getUsers()) {
            foreach ($users as $user) {
                if ($user->getId() == $userId) {
                    return true;
                }
            }
        }

        throw new Exception('User ' . $userId . ' does not own account', 403);
    }

    /**
     * @param $user
     * @throws Exception
     */
    protected function checkUserExists($user)
    {
        if (!$user) {
            throw new Exception('USER_NOT_FOUND', 404);
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function checkActiveAccount()
    {
        if ($this->getAccount()->getStatus() === 0) {
            throw new Exception('This account is not currently active', 403);
        }

        return true;
    }
}
