<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AccountFixture extends BaseFixture implements DependentFixtureInterface
{

    protected function loadData(ObjectManager $manager)
    {
        for($i=1; $i<=2; $i++) {
            $account = new Account();
            $account->setNumber('test'.$i.'234');
            $account->setCreated($this->faker->dateTimeThisCentury());
            $account->setModified($this->faker->dateTimeThisMonth());
            $account->setMoney(1500);
            $account->setStatus(Account::ACTIVE_STATUS);

            $user = $this->getReference('test_user_1');
            $account->addUser($user);

            $manager->persist($account);
        }

        $account = new Account();
        $account->setNumber('test3333');
        $account->setCreated($this->faker->dateTimeThisCentury());
        $account->setModified($this->faker->dateTimeThisMonth());
        $account->setMoney(1500);
        $account->setStatus(Account::ACTIVE_STATUS);

        $user = $this->getReference('test_user_2');
        $account->addUser($user);

        $manager->persist($account);

        $this->createMany(8, 'account', function () {
            $account = new Account();
            $account->setNumber($this->faker->bankAccountNumber . $this->faker->citySuffix);
            $account->setCreated($this->faker->dateTimeThisCentury());
            $account->setModified($this->faker->dateTimeThisMonth());
            $account->setMoney($this->faker->randomFloat());
            $account->setStatus(Account::ACTIVE_STATUS);

            $users = $this->getRandomReferences('main_users', 3);

            foreach ($users as $user) {
                $account->addUser($user);
            }

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
