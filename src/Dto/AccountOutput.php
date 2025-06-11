<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class AccountOutput
{
    #[Groups(['client_accounts'])]
    public int $id;

    #[Groups(['client_accounts'])]
    public string $currency;

    #[Groups(['client_accounts'])]
    public float $balance;

    #[Groups(['client_accounts'])]
    public string $createdAt;
}
