<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 */
class Account
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="float")
     */
    private $money;

    /**
     * const integer ACTIVE_STATUS Estado activo cuenta
     */
    const ACTIVE_STATUS = 1;

    /**
     * const integer INACTIVE_STATUS Estado de una cuenta dada de baja.
     */
    const INACTIVE_STATUS = 0;

    /**
     * const integer MAX_MONEY_OPERATION Máximo de dinero que se puede operar desde la app.
     */
    const MAX_MONEY_OPERATION = 2000;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="accounts")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(?\DateTimeInterface $modified): self
    {
        $this->modified = $modified;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getMoney(): ?float
    {
        return $this->money;
    }

    public function setMoney(float $money): self
    {
        $this->money = $money;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addAccount($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeAccount($this);
        }

        return $this;
    }

    public function saveAccountMovement(Request $request, EntityManagerInterface $em)
    {
        $operationData = array(
            'userId' => $request->request->get('user_id'),
            'moneyQuantity' => $request->request->get('money'),
            'operationType' => $request->request->get('operation_type'),
        );

        $this->checkUserOperationIsAllowed($operationData);
        $accountHistory = $this->getAccountHistoryData($operationData);

        try {
            $em->persist($accountHistory);
            $em->persist($this);
            $em->flush();

            return true;
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Error during Saving movement ' . $e->getMessage(), null, 500);
        }
    }

    private function withdraw(int $amount): void
    {
        if ($amount > $this->money) {
            throw new NotFoundHttpException('User does not have enough money', null, 403);
        }

        $this->money -= $amount;
    }

    private function deposit(int $amount): void
    {
        $this->money += $amount;
    }

    private function checkUserOperationIsAllowed($operationData)
    {
        $this->checkAccountExists();
        $this->checkUserOwnsAccount($operationData['userId']);
        $this->checkMoneyQuantityAllowed($operationData['moneyQuantity']);

        return true;
    }

    private function getAccountHistoryData($operationData)
    {
        $now = new \DateTime();
        $accountHistory = new AccountHistory();
        $accountHistory->setAccountId($this->getId());
        $accountHistory->setUserId($operationData['userId']);
        $accountHistory->setBeforeMoney($this->getMoney());
        $accountHistory->setAfterMoney($this->getBankMoneyResult($operationData));
        $accountHistory->setDate($now);

        return $accountHistory;
    }

    private function getBankMoneyResult($operationData)
    {
        switch ($operationData['operationType']) {
            case 'take':
                $this->withdraw($operationData['moneyQuantity']);
                break;
            case 'give':
                $this->deposit($operationData['moneyQuantity']);
                break;
            default:
                throw new NotFoundHttpException('Operation type not allowed ', null, 404);
                break;
        }

        return $this->getMoney();
    }


    private function checkAccountExists()
    {
        if (!$this->getId()) {
            throw new NotFoundHttpException('Account does not exist ', null, 404);
        }

        return true;
    }

    private function checkUserOwnsAccount($userId)
    {
        if ($users = $this->getUsers()) {
            foreach ($users as $user) {
                if ($user->getId() == $userId) {
                    return  true;
                }
            }
        }

        throw new NotFoundHttpException('User ' . $opData['userId'] . ' does not own account', null, 403);
    }

    private function checkMoneyQuantityAllowed($moneyQuantity)
    {
        if ($moneyQuantity > self::MAX_MONEY_OPERATION) {
            throw new NotFoundHttpException('User cannot make this op through the app', null, 403);
        }

        return true;
    }
}
