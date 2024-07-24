<?php
namespace App\Controller;

use App\Entity\EmailVerification;
use App\Form\ChangeEmailType;
use App\Form\ChangePasswordType;
use App\Service\EmailSendingService;
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
    // The number of seconds that the change email link is valid.
    private const TIME_VALID = 48 * 60 * 60;

    public function __construct(
        private EntityManagerInterface $em,
        private FlashMessageService $flashMessage,
        private EmailSendingService $emailSending
    ) {
    }

    #[Route('', name: 'app_settings')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $changeEmailForm = $this->createForm(ChangeEmailType::class);
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);

        return $this->render('settings/index.html.twig', [
            'formChangeEmail' => $changeEmailForm,
            'formChangePassword' => $changePasswordForm,
            'email' => $this->getUser()->getEmail()
        ]);
    }

    #[Route('/change_email', name: 'app_settings_change_email')]
    public function changeEmail(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangeEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->emailSending->changeEmailRequest($request, $user, $form->get('email')->getData(), self::TIME_VALID);
                $this->flashMessage->success('Check new email inbox to confirm email change');
            } else {
                $errors = $form->getErrors(true);
                $this->flashMessage->error($errors[0]->getMessage());
            }
        }

        return $this->redirectToRoute('app_settings');
    }

    #[Route('/change_email/verify/{authKey}', name: 'app_settings_change_email_verify')]
    public function changeEmailVerify(Request $request, string $authKey): Response
    {
        $emailVerification = $this->em->getRepository(EmailVerification::class)->findOneBy(['authKey' => $authKey]);

        if ($emailVerification === NULL) {
            $this->flashMessage->error('The link is invalid');
            return $this->redirectToRoute('app_settings');
        }

        if ($emailVerification->getExpiresAt() < new \DateTimeImmutable()) {
            $this->flashMessage->error('The link has expired');
            return $this->redirectToRoute('app_settings');
        }

        $user = $emailVerification->getUser();

        // Check if the client is logged in, if not, redirect to login page
        if ($this->getUser() == null) {
            $this->flashMessage->error('You have to log in first');
            $gotoUrl = $this->generateUrl(
                'app_login',
                [
                    '_target_path' => $this->generateUrl(
                        'app_settings_change_email_verify',
                        ['authKey' => $authKey]
                    )
                ]
            );
            return $this->redirect($gotoUrl);
        }

        // Check if the client is logged in to the same account that the email change applies to
        if (($this->getUser() ?? null) != $user) {
            $this->flashMessage->error('Wrong account');
            return $this->redirectToRoute('app_settings');
        }

        // Verify user if is not verified. A registered user who has not verified the email address provided in the registration form can still change their email address in the settings page.
        if (!$user->getIsVerified())
            $user->setIsVerified(true);

        $user->setEmail($emailVerification->getEmail());

        $this->em->persist($user);
        $this->em->remove($emailVerification);
        $this->em->flush();

        $this->em->refresh($this->getUser());

        $this->flashMessage->success('Email changed!');
        return $this->redirectToRoute('app_settings');
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

            $this->emailSending->accountDeletionConfirmation($request, $user);

            return $this->redirectToRoute('app_home');
        }

        $this->flashMessage->error('Wrong password');
        return $this->redirectToRoute('app_settings');
    }
}