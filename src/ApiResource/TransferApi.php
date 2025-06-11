<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\TransferInput;
use App\Dto\TransferOutput;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/transfer',
            controller: \App\Controller\ApiController::class . '::transfer',
            extraProperties: [
                'summary' => 'Transfer funds between accounts',
            ],
            input: TransferInput::class,
            output: TransferOutput::class,
            read: false
        )
    ]
)]
class TransferApi {}