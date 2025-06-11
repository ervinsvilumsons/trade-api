<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\AccountOutput;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/clients/{id}/accounts',
            controller: \App\Controller\ApiController::class . '::getClientAccounts',
            extraProperties: [
                'summary' => 'List client accounts',
                'parameters' => [
                    ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                ]
            ],
            normalizationContext: ['groups' => ['client_accounts']],
            read: false,
            output: AccountOutput::class
        )
    ],
    paginationEnabled: false
)]
class AccountsApi {}