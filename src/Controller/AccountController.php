<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        $logger->debug('Checking account page for '.$this->getUser()->getEmail());

        return $this->render('account/index.html.twig', [
            'client' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/user/{id}/edit", name="user_edit")
     */
    public function edit(User $user, Request $request, EntityManagerInterface $em)
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
     * @Route("/user/{id}/remove", name="user_remove")
     */
    public function removeUser(Request $request, EntityManagerInterface $em)
    {
        if (AccountApi::removeUserApi($request, $em)) {
            $this->addFlash('success', 'Usuario borrado con éxito');
            $session = $request->getSession();
            $session->invalidate();

            return $this->render('security/login.html.twig', [
                'last_username' => '',
                'error'         => '',
            ]);
        } else {
            $this->addFlash('error', 'Error: no se pudo borrar el Usuario');
        }
    }
}
