<?php
namespace App\Controller;

use App\Form\ChangeEmailType;
use App\Form\ChangePasswordType;
use App\Service\FlashMessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/settings')]
class SettingsController extends AbstractController
{
    private $entityManager;
    private $flashMessage;

    public function __construct(EntityManagerInterface $entityManager, FlashMessageService $flashMessage)
    {
        $this->entityManager = $entityManager;
        $this->flashMessage = $flashMessage;
    }

    #[Route('', name: 'app_settings')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $changeEmailForm = $this->createForm(ChangeEmailType::class, $user);
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);

        return $this->render('settings/index.html.twig', [
            'form_change_email' => $changeEmailForm,
            'form_change_password' => $changePasswordForm,
            'email' => $this->getUser()->getEmail()
        ]);
    }

    #[Route('/change_email', name: 'app_settings_change_email')]
    public function changeEmail(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangeEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->flashMessage->success('Your email has been changed!');
        }

        return $this->redirectToRoute('app_settings');
    }

    #[Route('/change_password', name: 'app_settings_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            $this->entityManager->flush();
            $this->flashMessage->success('Your password has been changed!');
        }

        return $this->redirectToRoute('app_settings');
    }

    #[Route('/delete_user', name: 'app_settings_delete_user')]
    public function deleteUser(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $password = $request->request->get('password');

        if ($password !== NULL && $passwordHasher->isPasswordValid($user, $password)) {
            $session = $request->getSession();
            $session = new Session();
            $session->invalidate();
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_home');
        }

        $this->flashMessage->error('Wrong password.');
        return $this->redirectToRoute('app_settings');
    }
}