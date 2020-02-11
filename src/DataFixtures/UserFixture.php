<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends BaseFixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function loadData(ObjectManager $manager)
    {
        // Test user
        for($i=1; $i<=2; $i++) {
            $user = new User();
            $user->setEmail('test_client'.$i.'@example.com');
            $user->setFirstName('test');
            $user->setLastname('user');
            $user->setPostalCode($this->faker->postcode);
            $user->setCity($this->faker->city);
            $user->setAddress($this->faker->address);
            $user->setTownship($this->faker->country);
            $user->agreeToTerms();
            $this->addReference('test_user_'.$i, $user);

            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'password'
            ));

            $apiToken1 = new ApiToken($user);
            $apiToken2 = new ApiToken($user);
            $manager->persist($apiToken1);
            $manager->persist($apiToken2);
            $manager->persist($user);
        }

        $this->createMany(9, 'main_users', function ($i) use ($manager) {
            $user = new User();
            $user->setEmail(sprintf('client%d@example.com', $i));
            $user->setFirstName($this->faker->firstName);
            $user->setLastname($this->faker->lastName);
            $user->setPostalCode($this->faker->postcode);
            $user->setCity($this->faker->city);
            $user->setAddress($this->faker->address);
            $user->setTownship($this->faker->country);
            $user->agreeToTerms();

            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'password'
            ));

            $apiToken1 = new ApiToken($user);
            $apiToken2 = new ApiToken($user);
            $manager->persist($apiToken1);
            $manager->persist($apiToken2);

            return $user;
        });

        $this->createMany(3, 'admin_users', function ($i) {
            $user = new User();
            $user->setEmail(sprintf('admin%d@bank.com', $i));
            $user->setFirstName($this->faker->firstName);
            $user->setLastname($this->faker->lastName);
            $user->setRoles(['ROLE_ADMIN']);
            $user->agreeToTerms();

            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'admin'
            ));

            return $user;
        });

        $manager->flush();
    }
}
