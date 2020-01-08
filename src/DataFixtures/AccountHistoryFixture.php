<?php

namespace App\DataFixtures;

use App\Entity\AccountHistory;
use Doctrine\Common\Persistence\ObjectManager;

class AccountHistoryFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'movements', function () {
            $movement = new AccountHistory();
            $movement->setAccountId($this->faker->numberBetween(11112, 232323));
            $movement->setUserId($this->faker->numberBetween(232, 444));
            $movement->setDate($this->faker->dateTimeThisYear);
            $movement->setBeforeMoney($this->faker->randomFloat());
            $movement->setAfterMoney($this->faker->randomFloat());

            return $movement;
        });

        $manager->flush();
    }
}
