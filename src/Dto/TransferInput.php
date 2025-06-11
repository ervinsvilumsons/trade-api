<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class TransferInput
{
    #[Groups(['transfer'])]
    public int $from_account_id;

    #[Groups(['transfer'])]
    public int $to_account_id;

    #[Groups(['transfer'])]
    public float $amount;
}
