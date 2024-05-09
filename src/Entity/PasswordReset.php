<?php

namespace App\Entity;

use App\Repository\PasswordResetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

#[ORM\Entity(repositoryClass: PasswordResetRepository::class)]
class PasswordReset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $reset_key = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getResetKey(): ?string
    {
        return $this->reset_key;
    }

    public function setResetKey(string $reset_key): static
    {
        $this->reset_key = $reset_key;

        return $this;
    }

    public function getExpire(): ?\DateTimeImmutable
    {
        return $this->expire;
    }

    public function setExpire(\DateTimeImmutable $expire): static
    {
        $this->expire = $expire;

        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('email', new Assert\NotBlank(['message' => 'Email field cannot be empty.']));
        $metadata->addPropertyConstraint('email', new Assert\Email(['message' => 'The email "{{ value }}" is not a valid email.']));
    }
}
