<?php
namespace App\Controller;

use App\Entity\PasswordReset;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\PasswordResetType;
use App\Service\EmailSendingService;
use App\Service\FlashMessageService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/password_reset')]
class PasswordResetController extends AbstractController
{
    // The number of seconds that the password reset link is valid.
    private const TIME_VALID = 15 * 60;

    public function __construct(
        private EntityManagerInterface $em,
        private FlashMessageService $flashMessage,
        private EmailSendingService $emailSending
    ) {
    }

    #[Route('', name: 'app_password_reset')]
    public function index(Request $request, MailerInterface $mailer, ManagerRegistry $registry): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('app_notes');

        $form = $this->createForm(PasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user !== null) {
                $this->emailSending->passwordResetRequest(
                    $request,
                    $user,
                    self::TIME_VALID
                );

                $request->getSession()->getFlashBag()->add('reset_password_email', $email);
                return $this->redirectToRoute('app_password_reset_email_sent');
            }

            $form->addError(new FormError('The email address provided is not associated with any account'));
        }

        return $this->render('password_reset/index.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/email_sent', name: 'app_password_reset_email_sent')]
    public function emailSentConfirmation(Request $request): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('app_notes');

        $email = $request->getSession()->getFlashBag()->get('reset_password_email')[0] ?? null;

        if ($email === null)
            throw new NotFoundHttpException();

        return $this->render('password_reset/email_sent.html.twig', [
            'email' => $email
        ]);
    }

    #[Route('/reset/success', name: 'app_password_reset_success')]
    public function success(Request $request): Response
    {
        $success = $request->getSession()->getFlashBag()->get('password_changed')[0] ?? null;

        if ($success === null)
            throw new NotFoundHttpException();

        return $this->render('password_reset/success.html.twig');
    }

    #[Route('/verify/{authKey}', name: 'app_password_reset_verify')]
    public function reset(Request $request, string $authKey, ManagerRegistry $registry, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('app_notes');

        $passwordReset = $this->em->getRepository(PasswordReset::class)->findOneBy(['authKey' => $authKey]);

        if ($passwordReset === NULL) {
            $this->flashMessage->error('The password reset link is invalid');
            return $this->redirectToRoute('app_password_reset');
        }

        if ($passwordReset->getExpiresAt() < new \DateTimeImmutable()) {
            $this->flashMessage->error('The password reset link has expired');
            return $this->redirectToRoute('app_password_reset');
        }

        $user = $passwordReset->getUser();

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $this->em->remove($passwordReset);
            $this->em->flush();

            $request->getSession()->getFlashBag()->add('password_changed', '');

            return $this->redirectToRoute('app_password_reset_success');
        }

        return $this->render('password_reset/reset.html.twig', [
            'form' => $form,
            'email' => $passwordReset->getUser()->getEmail()
        ]);
    }
}