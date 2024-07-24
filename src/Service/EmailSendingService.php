<?php
namespace App\Service;

use App\Entity\EmailVerification;
use App\Entity\PasswordReset;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;

/*
 * You can set up sender details in config/packages/mailer.yaml
 */

class EmailSendingService extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private AuthKeyService $authKey,
        private TimeConverterService $timeConverter
    ) {
    }

    public function changeEmailRequest(Request $request, User $user, string $newEmail, int $secondsLinkValid): void
    {
        $emailVerification = new EmailVerification();

        // Add change email address request details to the database
        $authKey = $this->authKey->generate();
        $emailVerification
            ->setUser($user)
            ->setEmail($newEmail)
            ->setAuthKey($authKey)
            ->setExpiresAt(new \DateTimeImmutable('+' . $secondsLinkValid . ' seconds'))
        ;

        $this->em->persist($emailVerification);
        $this->em->flush();

        // Prepare change email address request email
        $verificationLink = $request->getSchemeAndHttpHost() . $this->generateUrl('app_settings_change_email_verify', [
            'authKey' => $authKey
        ]);

        $mail = (new TemplatedEmail())
            ->to($newEmail)
            ->subject('Change your email')
            ->htmlTemplate('emails/change_email.html.twig')
            ->textTemplate('emails/email_verification.txt.twig')
            ->context([
                'baseUrl' => $request->getSchemeAndHttpHost(),
                'verificationLink' => $verificationLink,
                'newEmail' => $newEmail,
                'timeValid' => $this->timeConverter->secondsToHuman($secondsLinkValid)
            ])
        ;

        // Send change email address request email
        $this->mailer->send($mail);
    }

    public function passwordResetRequest(Request $request, User $user, int $secondsLinkValid): void
    {
        $passwordReset = new PasswordReset();

        // Add password reset details to the database
        $authKey = $this->authKey->generate();

        $passwordReset
            ->setUser($user)
            ->setAuthKey($authKey)
            ->setExpiresAt(new \DateTimeImmutable('+' . $secondsLinkValid . ' seconds'))
        ;

        $resetLink = $request->getSchemeAndHttpHost() . $this->generateUrl('app_password_reset_verify', [
            'authKey' => $authKey
        ]);

        $this->em->persist($passwordReset);
        $this->em->flush();

        // Prepare password reset request email
        $mail = (new TemplatedEmail)
            ->to($user->getEmail())
            ->subject('Reset your password')
            ->textTemplate('emails/password_reset.txt.twig')
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'baseUrl' => $request->getSchemeAndHttpHost(),
                'resetLink' => $resetLink,
                'timeValid' => $this->timeConverter->secondsToHuman($secondsLinkValid)
            ])
        ;

        // Send password reset request email
        $this->mailer->send($mail);
    }

    public function registerVerificationRequest(Request $request, User $user, int $secondsLinkValid): void
    {
        $emailVerification = new EmailVerification();

        // Add verification details to the database
        $authKey = $this->authKey->generate();
        $emailVerification
            ->setUser($user)
            ->setEmail($user->getEmail())
            ->setAuthKey($authKey)
            ->setExpiresAt(new \DateTimeImmutable('+' . $secondsLinkValid . ' seconds'))
        ;

        $this->em->persist($emailVerification);
        $this->em->flush();

        // Prepare verification email
        $verificationLink = $request->getSchemeAndHttpHost() . $this->generateUrl('app_signup_verify', [
            'authKey' => $authKey
        ]);

        $mail = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('New account email verification')
            ->htmlTemplate('emails/email_verification.html.twig')
            ->textTemplate('emails/email_verification.txt.twig')
            ->context([
                'baseUrl' => $request->getSchemeAndHttpHost(),
                'verificationLink' => $verificationLink,
                'timeValid' => $this->timeConverter->secondsToHuman($secondsLinkValid)
            ])
        ;

        // Send verification email
        $this->mailer->send($mail);
    }

    public function accountDeletionConfirmation(Request $request, User $user): void
    {
        $mail = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Account deleted')
            ->htmlTemplate('emails/account_deleted.html.twig')
            ->textTemplate('emails/account_deleted.txt.twig')
            ->context([
                'baseUrl' => $request->getSchemeAndHttpHost()
            ])
        ;

        $this->mailer->send($mail);
    }
}