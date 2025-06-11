<?php

namespace App\Tests\Repository;

use App\Entity\Client;
use App\Entity\Account;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransactionRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?TransactionRepository $repository = null;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(Transaction::class);
    }

    public function testFindByAccountIdWithPaginationAndCount(): void
    {
        $client = new Client();
        $client->setName('John');
        $client->setCreatedAt(new \DateTimeImmutable());

        $fromAccount = new Account();
        $fromAccount->setClient($client);
        $fromAccount->setCurrency('USD');
        $fromAccount->setBalance(1000);
        $fromAccount->setCreatedAt(new \DateTimeImmutable());

        $toAccount = new Account();
        $toAccount->setClient($client);
        $toAccount->setCurrency('EUR');
        $toAccount->setBalance(500);
        $toAccount->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($client);
        $this->entityManager->persist($fromAccount);
        $this->entityManager->persist($toAccount);

        for ($i = 1; $i <= 5; $i++) {
            $transaction = new Transaction();
            $transaction->setFromAccount($fromAccount);
            $transaction->setToAccount($toAccount);
            $transaction->setFromCurrency('USD');
            $transaction->setToCurrency('EUR');
            $transaction->setOriginalAmount(100 + $i);
            $transaction->setConvertedAmount(90 + $i);
            $transaction->setExchangeRate(0.9);
            $transaction->setCreatedAt(new \DateTimeImmutable(sprintf('-%d days', $i)));

            $this->entityManager->persist($transaction);
        }

        $this->entityManager->flush();

        $transactions = $this->repository->findByAccountIdWithPagination($fromAccount->getId(), 0, 10);
        $this->assertCount(5, $transactions);

        $count = $this->repository->countAccountTransactions($fromAccount->getId());
        $this->assertEquals(5, $count);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager?->close();
        $this->entityManager = null;
    }
}
