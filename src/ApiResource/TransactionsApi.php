<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\TransactionOutput;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/accounts/{id}/transactions',
            controller: \App\Controller\ApiController::class . '::getTransactions',
            extraProperties: [
                'summary' => 'List transactions for an account',
                'parameters' => [
                    ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ['name' => 'offset', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']],
                    ['name' => 'limit', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']],
                ]
            ],
            normalizationContext: ['groups' => ['transactions']],
            read: false,
            output: TransactionOutput::class
        )
    ],
    paginationEnabled: false
)]
class TransactionsApi {}