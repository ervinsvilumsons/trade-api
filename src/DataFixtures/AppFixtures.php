<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $clientsData = [
            'Alice' => [
                ['currency' => 'USD', 'balance' => 1000],
                ['currency' => 'EUR', 'balance' => 500],
            ],
            'Bob' => [
                ['currency' => 'GBP', 'balance' => 800],
                ['currency' => 'EUR', 'balance' => 2000],
            ],
            'Jonas' => [
                ['currency' => 'SEK', 'balance' => 1500],
                ['currency' => 'EUR', 'balance' => 700],
                ['currency' => 'USD', 'balance' => 4000],
            ],
        ];

        $accounts = [];

        foreach ($clientsData as $clientName => $accountDataList) {
            $client = new Client();
            $client->setName($clientName);
            $client->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($client);

            foreach ($accountDataList as $accountData) {
                $account = new Account();
                $account->setClient($client);
                $account->setCurrency($accountData['currency']);
                $account->setBalance($accountData['balance']);
                $account->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($account);

                $accounts[] = $account;
            }
        }

        $manager->flush();
    }
}
