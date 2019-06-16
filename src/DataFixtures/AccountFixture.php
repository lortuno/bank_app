<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Common\Persistence\ObjectManager;

class AccountFixture extends BaseFixture
{
    const ACTIVE_STATUS = 1;

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'account', function() {
            $account = new Account();
            $account->setNumber($this->faker->bankAccountNumber);
            $account->setCreatedDate($this->faker->dateTimeThisDecade());
            $account->setModified($this->faker->dateTimeThisMonth());
            $account->setMoney($this->faker->randomFloat());
            $account->setStatus(self::ACTIVE_STATUS);

            return $account;
        });

        $manager->flush();
    }
}
