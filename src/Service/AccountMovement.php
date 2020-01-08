<?php

namespace App\Service;

use App\Entity\AccountHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class AccountMovement extends AccountManagement
{
    private $moneyQuantity;
    private $operationType;

    /**
     * const integer MAX_MONEY_OPERATION Máximo de dinero que se puede operar desde la app.
     */
    const MAX_MONEY_OPERATION = 2000;

    /**
     * @return mixed
     */
    private function getMoneyQuantity()
    {
        return $this->moneyQuantity;
    }

    /**
     * @param mixed $moneyQuantity
     */
    private function setMoneyQuantity($moneyQuantity): void
    {
        $this->moneyQuantity = $moneyQuantity;
    }

    /**
     * @return mixed
     */
    private function getOperationType()
    {
        return $this->operationType;
    }

    /**
     * @param mixed $operationType
     */
    private function setOperationType($operationType): void
    {
        $this->operationType = $operationType;
    }

    /**
     * AccountMovements constructor.
     * @param Request $request
     * @param EntityManagerInterface $em
     * @throws \Exception
     */
    public function __construct(Request $request, EntityManagerInterface $em)
    {
        parent::__construct($request, $em);
        $this->setOperationProperties();
    }

    /**
     * Guarda el movimiento en la cuenta y en el histórico de movimientos.
     * @throws \Exception
     */
    public function saveAccountMovement()
    {
        $this->checkUserOperationIsAllowed();
        $accountHistory = $this->getAccountHistoryData();

        try {
            $this->em->persist($accountHistory);
            $this->em->persist($this->getAccount());
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception('Error during Saving movement ' . $e->getMessage(), 500);
        }
    }

    private function setOperationProperties()
    {
        $this->setUserId($this->request->request->get('user_id'));
        $this->setOperationType($this->request->request->get('operation_type'));
        $this->setMoneyQuantity($this->request->request->get('money'));
    }

    /**
     * @param int $amount
     * @throws \Exception
     */
    private function withdraw(int $amount): void
    {
        if ($amount > $this->getAccount()->getMoney()) {
            throw new \Exception('User does not have enough money. User has €' . $this->getAccount()->getMoney(), 403);
        }

        $total = $this->getAccount()->getMoney() - $amount;
        $this->getAccount()->setMoney($total);
    }

    /**
     * @param int $amount
     */
    private function deposit(int $amount): void
    {
        $total = $this->getAccount()->getMoney() + $amount;
        $this->getAccount()->setMoney($total);
    }

    /**
     * @throws \Exception
     */
    private function checkUserOperationIsAllowed()
    {
        $this->checkUserOwnsAccount();
        $this->checkMoneyQuantityAllowed();
    }

    /**
     * @return AccountHistory
     * @throws \Exception
     */
    private function getAccountHistoryData()
    {
        $now = new \DateTime();
        $accountHistory = new AccountHistory();
        $accountHistory->setAccountId($this->getAccount()->getId());
        $accountHistory->setUserId($this->getUserId());
        $accountHistory->setBeforeMoney($this->getAccount()->getMoney());
        $accountHistory->setAfterMoney($this->getBankMoneyResult());
        $accountHistory->setDate($now);

        return $accountHistory;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getBankMoneyResult()
    {
        $operationType = strtolower($this->getOperationType());

        switch ($operationType) {
            case 'take':
                $this->withdraw($this->getMoneyQuantity());
                break;
            case 'give':
                $this->deposit($this->getMoneyQuantity());
                break;
            default:
                throw new \Exception('Operation type not allowed: ' . $operationType, 404);
                break;
        }

        return $this->getAccount()->getMoney();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function checkUserOwnsAccount()
    {
        $userId = $this->getUserId();

        if ($users = $this->getAccount()->getUsers()) {
            foreach ($users as $user) {
                if ($user->getId() == $userId) {
                    return true;
                }
            }
        }

        throw new \Exception('User ' . $userId . ' does not own account', 403);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function checkMoneyQuantityAllowed()
    {
        if ($this->getMoneyQuantity() > self::MAX_MONEY_OPERATION) {
            throw new \Exception('User cannot make this op through the app', 403);
        }

        return true;
    }
}
