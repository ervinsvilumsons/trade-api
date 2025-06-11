<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class TransactionOutput
{
    #[Groups(['transactions'])]
    public int $id;

    #[Groups(['transactions'])]
    public int $fromAccountId;

    #[Groups(['transactions'])]
    public int $toAccountId;

    #[Groups(['transactions'])]
    public string $fromCurrency;

    #[Groups(['transactions'])]
    public string $to_currency;

    #[Groups(['transactions'])]
    public float $original_amount;

    #[Groups(['transactions'])]
    public float $convertedAmount;

    #[Groups(['transactions'])]
    public float $exchangeRate;

    #[Groups(['transactions'])]
    public string $createdAt;
}