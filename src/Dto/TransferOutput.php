<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class TransferOutput
{
    #[Groups(['transfer'])]
    public string $message;
}
