<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['account:read', 'client:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    #[Groups(['account:read', 'client:read'])]
    #[Assert\Positive]
    private ?string $originalAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    #[Groups(['account:read', 'client:read'])]
    private ?string $convertedAmount = null;

    #[ORM\Column(length: 3)]
    #[Groups(['account:read', 'client:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 3)]
    private ?string $fromCurrency = null;

    #[ORM\Column(length: 3, nullable: true)]
    #[Groups(['account:read', 'client:read'])]
    private ?string $toCurrency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 6, nullable: true)]
    #[Groups(['account:read', 'client:read'])]
    private ?string $exchangeRate = null;

    #[ORM\Column]
    #[Groups(['account:read', 'client:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Account $fromAccount = null;

    #[ORM\ManyToOne(inversedBy: 'receivedTransactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Account $toAccount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalAmount(): ?string
    {
        return $this->originalAmount;
    }

    public function setOriginalAmount(string $originalAmount): static
    {
        $this->originalAmount = $originalAmount;

        return $this;
    }

    public function getConvertedAmount(): ?string
    {
        return $this->convertedAmount;
    }

    public function setConvertedAmount(?string $convertedAmount): static
    {
        $this->convertedAmount = $convertedAmount;

        return $this;
    }

    public function getFromCurrency(): ?string
    {
        return $this->fromCurrency;
    }

    public function setFromCurrency(string $fromCurrency): static
    {
        $this->fromCurrency = $fromCurrency;

        return $this;
    }

    public function getToCurrency(): ?string
    {
        return $this->toCurrency;
    }

    public function setToCurrency(?string $toCurrency): static
    {
        $this->toCurrency = $toCurrency;

        return $this;
    }

    public function getExchangeRate(): ?string
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(?string $exchangeRate): static
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getFromAccount(): ?Account
    {
        return $this->fromAccount;
    }

    public function setFromAccount(?Account $fromAccount): static
    {
        $this->fromAccount = $fromAccount;

        return $this;
    }

    public function getToAccount(): ?Account
    {
        return $this->toAccount;
    }

    public function setToAccount(?Account $toAccount): static
    {
        $this->toAccount = $toAccount;

        return $this;
    }

    #[Groups(['account:read', 'client:read'])]
    public function getFromAccountId(): ?int
    {
        return $this->fromAccount?->getId();
    }

    #[Groups(['account:read', 'client:read'])]
    public function getToAccountId(): ?int
    {
        return $this->toAccount?->getId();
    }
}
