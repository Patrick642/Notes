<?php
namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Service\FlashMessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings')]
class SettingsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private FlashMessageService $flashMessage,
    ) {
    }

    #[Route('', name: 'app_settings')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);

        return $this->render('settings/index.html.twig', [
            'formChangePassword' => $changePasswordForm,
            'email' => $this->getUser()->getEmail()
        ]);
    }

    #[Route('/change_password', name: 'app_settings_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $password = $form->get('password')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $password));
                $this->em->flush();
                $this->flashMessage->success('Your password has been changed!');
            } else {
                $errors = $form->getErrors(true);
                $this->flashMessage->error($errors[0]->getMessage());
            }
            $this->em->refresh($user);
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

            $this->em->remove($user);
            $this->em->flush();

            return $this->redirectToRoute('app_home');
        }

        $this->flashMessage->error('Wrong password');
        return $this->redirectToRoute('app_settings');
    }
}