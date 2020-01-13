<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Service\UserManagement;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/**
 * @IsGranted("ROLE_USER")
 */
class AccountController extends BaseController
{
    /**
     * @Route("/", name="app_account")
     */
    public function index(LoggerInterface $logger)
    {
        $logger->debug('Checking account page for ' . $this->getUser()->getEmail());

        return $this->render('account/index.html.twig', [
            'client' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/user/{id}/edit", name="user_edit")
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param FormFactoryInterface $formFactory
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        FormFactoryInterface $formFactory
    ) {
        try {
            $userManager = new UserManagement($request, $em, $passwordEncoder, $guardHandler, $formFactory);
            $userManager->setUser($user);
            $userEdition = $userManager->editUser(true);

            if ($userEdition) {
                $this->addFlash('success', 'Usuario actualizado con éxito');

                return $this->redirectToRoute('user_edit', [
                    'id' => $user->getId(),
                ]);
            }

            return $this->renderEditForm($user);

        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $error = $e->getMessage();
            return $this->renderEditForm($user, $error);
        }
    }

    /**
     * @param User $user
     * @param string $error
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderEditForm(User $user, $error = '')
    {
        $form = $this->createForm(UserFormType::class, $user);

        return $this->render('user_edition/edit.html.twig', [
            'userForm' => $form->createView(),
            'error' => $error,
        ]);
    }

    /**
     * @Route("/user/{id}/edit2", name="user_edit2")
     */
    public function editOld(User $user, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Usuario actualizado con éxito');

            return $this->redirectToRoute('user_edit', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('user_edition/edit.html.twig', [
            'userForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/{user_id}/remove", name="user_remove")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param FormFactoryInterface $formFactory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        FormFactoryInterface $formFactory
    )
    {
        try {
            $request->request->set('user_id', $request->get('user_id'));
            $user = new UserManagement($request, $em, $passwordEncoder, $guardHandler, $formFactory);
            $user->setUserRequested();
            $user->removeUser();
            $this->get('security.context')->setToken(null);
            $session = $request->getSession();
            $session->invalidate();
            $this->addFlash('success', 'Usuario borrado con éxito');

            return $this->render('security/login.html.twig', [
                'last_username' => '',
                'error' => '',
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error: no se pudo borrar el Usuario');
        }
    }

    /**
     * @Route("/phpinfo", name="app_info")
     */
    public function info()
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        return $this->render('security/phpinfo.html.twig', array('phpinfo' => $phpinfo));
    }
}
