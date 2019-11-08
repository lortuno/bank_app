<?php


namespace App\Service;


use App\Entity\Account;
use App\Entity\User;
use App\Entity\UserDeleted;
use App\Entity\AccountHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AccountHelper
{
    /**
     * Inserta en usuarios eliminados un usuario dado de baja.
     * @param $user
     * @param Request $request
     * @param EntityManagerInterface $em
     * @throws \Exception
     */
    public static function insertUserDeleted($user, Request $request, EntityManagerInterface $em)
    {
        $now = new \DateTime();
        $userDeleted = new UserDeleted();
        $userDeleted->setEmail($user->getEmail());
        $userDeleted->setUserId($user->getId());
        $userDeleted->setDate($now);
        $userDeleted->setReason($request->get('reason'));

        try {
            $em->persist($userDeleted);
            $em->flush();
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Error during User Delete creation');
        }
    }

}