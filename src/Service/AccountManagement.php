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
    protected $user;

    /**
     * AccountManagement constructor.
     * @param Request $request
     * @param EntityManagerInterface $em
     * @throws Exception
     */
    public function __construct(Request $request, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->request = $request;
        $this->faker = Factory::create();
        $this->setOperationProperties();
    }

    /**
     * @throws Exception
     */
    public function setAccountRequested()
    {
        $number = $this->request->request->get('account_number');
        $accountRepository = $this->em->getRepository(Account::class);
        $account = $accountRepository->findOneBy(['number' => $number]);

        $this->setAccount($account);
        $this->checkAccountExists($number);
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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @param $number
     * @return bool
     * @throws Exception
     */
    private function checkAccountExists($number)
    {
        if (!$this->account && $number) {
            throw new Exception('Account requested does not exist', 404);
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function setOperationProperties()
    {
        $username = $this->request->request->get('email');
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $username]);
        $this->setUser($user);
        $this->checkUserExists();
        $this->setAccountRequested();
    }

    /**
     * @return bool
     * @throws Exception
     */
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
            $account->addUser($this->getUser());
        } catch (Exception $e) {
            throw new Exception('Error on adding user to account: ' . $e->getMessage(), 500);
        }

        try {
            $this->em->persist($account);
            $this->em->flush();

            return true;
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
            $account = $this->getAccount();

            if (!$account) {
                throw new Exception('Error; the account does not exist.', 404);
            }
        } catch (Exception $exception) {
            throw new Exception( $exception->getMessage(), $exception->getCode());
        }

        try {
            $account->addUser($this->getUser());
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
            $this->account->removeUser($this->user);
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
        $userEmail = $this->getUser()->getEmail();

        if ($users = $this->getAccount()->getUsers()) {
            foreach ($users as $user) {
                if ($user->getEmail() == $userEmail) {
                    return true;
                }
            }
        }

        throw new Exception('User ' . $userEmail . ' does not own account', 403);
    }

    /**
     * @throws Exception
     */
    protected function checkUserExists()
    {
        if (!$this->user) {
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
