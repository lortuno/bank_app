<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AccountFixture extends BaseFixture implements DependentFixtureInterface
{

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'account', function () {
            $account = new Account();
            $account->setNumber($this->faker->bankAccountNumber . $this->faker->citySuffix);
            $account->setCreated($this->faker->dateTimeThisDecade());
            $account->setModified($this->faker->dateTimeThisMonth());
            $account->setMoney($this->faker->randomFloat());
            $account->setStatus(Account::ACTIVE_STATUS);

            $user = $this->getRandomReference('main_users');
            $account->setOwnerId($user);


            return $account;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixture::class,
        ];
    }
}
