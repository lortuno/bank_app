<?php


namespace App\Service;

use App\Entity\User;
use App\Entity\UserDeleted;
use App\Form\Model\UserRegistrationFormModel;
use App\Form\UserRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class UserManagement
{
    protected $em;
    protected $request;
    protected $passwordEncoder;
    protected $guardHandler;
    protected $formFactory;
    private $user;

    /**
     * UserManagement constructor.
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        FormFactoryInterface $formFactory)
    {
        $this->em = $em;
        $this->request = $request;
        $this->passwordEncoder = $passwordEncoder;
        $this->guardHandler = $guardHandler;
        $this->formFactory = $formFactory;
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
     * @throws Exception
     */
    public function setUserRequested()
    {
        $id = $this->request->request->get('user_id');
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->find($id);

        $this->setUser($user);
        $this->checkUserExists();
    }

    /**
     * @throws Exception
     */
    protected function checkUserExists()
    {
        if (!$this->getUser()) {
            throw new Exception('USER_NOT_FOUND', 404);
        }
    }

    /**
     * @throws Exception
     */
    public function removeUser()
    {
        try {
            $this->insertUserDeleted();
            $this->em->remove($this->getUser());
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Error on delete ' . $e->getMessage(), 500);
        }
    }

    /**
     * Inserta en usuarios eliminados un usuario dado de baja.
     * @throws Exception
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
        } catch (Exception $e) {
            throw new Exception('Error during User Delete creation');
        }
    }

    /**
     * @param bool $csrfTokenActive
     * @return User
     * @throws Exception
     */
    public function registerUser($csrfTokenActive = true)
    {
        try {
            $options =  array('csrf_protection' => $csrfTokenActive);
            $form = $this->formFactory->create(UserRegistrationFormType::class, null, $options);
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var UserRegistrationFormModel $userModel */
                $userModel = $form->getData();

                $user = new User();
                $user->setEmail($userModel->email);
                $user->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    $userModel->plainPassword
                ));
                $user->setLastname($userModel->lastName);
                $user->setFirstName($userModel->name);
                // be absolutely sure they agree
                if (true === $userModel->agreeTerms) {
                    $user->agreeToTerms();
                }

                $em = $this->em;
                $em->persist($user);
                $em->flush();

                return $user;
            }

            throw new Exception('User form invalid', 503);

        } catch (Exception $e) {
            throw new Exception('Error on user creation', 500);
        }
    }
}
