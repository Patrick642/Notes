<?php
namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FlashMessageService extends AbstractController
{
    /**
     * Create a success flash message.
     *
     * @param  mixed $message
     * @return void
     */
    public function success(mixed $message): void
    {
        $this->addFlash('success', $message);
    }

    /**
     * Create an error flash message.
     *
     * @param  mixed $message
     * @return void
     */
    public function error(mixed $message): void
    {
        $this->addFlash('error', $message);
    }
}