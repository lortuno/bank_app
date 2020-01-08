<?php

namespace App\DataFixtures;

use App\Entity\UserDeleted;
use Doctrine\Common\Persistence\ObjectManager;

class UserDeletedFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(4, 'deleted_users', function ($i) use ($manager) {
            $user = new UserDeleted();
            $user->setEmail(sprintf('client%d@example.com', $i));
            $user->setUserId($this->faker->numberBetween(232, 4545));
            $user->setDate($this->faker->dateTime);

            if ($this->faker->boolean) {
                $user->setReason($this->faker->text);
            }


            return $user;
        });

        $manager->flush();
    }
}
