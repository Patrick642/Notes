<?php
namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FlashMessageService extends AbstractController
{
    /**
     * Create an success message.
     *
     * @param  mixed $message
     * @return void
     */
    public function success(mixed $message): void
    {
        $this->addFlash('success', $message);
    }

    /**
     * Create an error message.
     *
     * @param  mixed $message
     * @return void
     */
    public function error(string $message): void
    {
        $this->addFlash('error', $message);
    }

    /**
     * Create an error message informing that the user is not authorized to perform the operation.
     *
     * @return void
     */
    public function errorUnauthorized(): void
    {
        $this->error('You cannot perform this action.');
    }
}