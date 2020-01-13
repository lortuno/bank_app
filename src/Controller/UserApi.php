<?php

namespace App\Controller;

use App\Entity\User;
use App\Helper\AccountHelper;
use App\Service\UserManagement;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class UserApi extends BaseController
{
    /**
     * @Route("/api/user_info", name="api_info")
     * @IsGranted("ROLE_USER")
     */
    public function accountApi()
    {
        $user = $this->getUser();

        return $this->json($user, 200, [], [
            'groups' => ['main'],
        ]);
    }

    /**
     * @Route("/api/user/remove", name="api_user_remove")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param FormFactoryInterface $formFactory
     * @return JsonResponse
     */
    public function removeUserApi(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        FormFactoryInterface $formFactory
    ) {
        try {
            $user = new UserManagement($request, $em, $passwordEncoder, $guardHandler, $formFactory);
            $user->setUserRequested();
            $user->removeUser();

            return new JsonResponse('USER_DELETED', 200);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }

    /**
     * @Route("/api/user/create", name="api_user_create")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param FormFactoryInterface $formFactory
     * @return JsonResponse
     */
    public function createUserApi(
         Request $request,
         EntityManagerInterface $em,
         UserPasswordEncoderInterface $passwordEncoder,
         GuardAuthenticatorHandler $guardHandler,
         FormFactoryInterface $formFactory
    ) {
        try {
            $user = new UserManagement($request, $em, $passwordEncoder, $guardHandler, $formFactory);
            $user->registerUser(false);

            return new JsonResponse('USER_CREATED', 201);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }

    /**
     * @Route("/api/user/edit", name="api_user_edit")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param FormFactoryInterface $formFactory
     * @return JsonResponse
     */
    public function editUserApi(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        FormFactoryInterface $formFactory
    ) {
        try {
            $userManager = new UserManagement($request, $em, $passwordEncoder, $guardHandler, $formFactory);
            $userManager->setUserRequested();
            $userManager->editUser(false);

            return new JsonResponse('USER_EDITED', 200);
        } catch (\Exception $e) {
            return AccountHelper::getJsonErrorResponse($e);
        }
    }
}
