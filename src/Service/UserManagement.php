<?php


namespace App\Service;

use App\Entity\User;
use App\Entity\UserDeleted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserManagement
{
    protected $em;
    protected $request;
    private $user;

    /**
     * UserManagement constructor.
     * @param Request $request
     * @param EntityManagerInterface $em
     * @throws \Exception
     */
    public function __construct(Request $request, EntityManagerInterface $em)
    {
        $id = $request->get('user_id');
        $userRepository = $em->getRepository(User::class);
        $user = $userRepository->find($id);

        $this->setUser($user);
        $this->checkUserExists();
        $this->em = $em;
        $this->request = $request;
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
     * @throws \Exception
     */
    protected function checkUserExists()
    {
        if (!$this->getUser()) {
            throw new \Exception('USER_NOT_FOUND', 404);
        }
    }

    /**
     * @throws \Exception
     */
    public function removeUser()
    {
        try {
            $this->insertUserDeleted();
            $this->em->remove($this->getUser());
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception('Error on delete ' . $e->getMessage(), 500);
        }
    }

    /**
     * Inserta en usuarios eliminados un usuario dado de baja.
     * @param $user
     * @param Request $request
     * @param EntityManagerInterface $em
     * @throws \Exception
     */
    public function insertUserDeleted()
    {
        $now = new \DateTime();
        $userDeleted = new UserDeleted();
        $userDeleted->setEmail($this->getUser()->getEmail());
        $userDeleted->setUserId($this->getUser()->getId());
        $userDeleted->setDate($now);
        $userDeleted->setReason($this->request->get('reason'));

        try {
            $this->em->persist($userDeleted);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception('Error during User Delete creation');
        }
    }
}
