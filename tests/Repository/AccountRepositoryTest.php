<?php

namespace App\Tests\Repository;

use App\Entity\Account;
use App\Entity\Client;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AccountRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?AccountRepository $repository = null;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(Account::class);

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testFindByIdReturnsCorrectAccount(): void
    {
        $client = new Client();
        $client->setName('John');
        $client->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($client);

        $account = new Account();
        $account->setClient($client);
        $account->setCurrency('USD');
        $account->setBalance(1000.00);
        $account->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $found = $this->repository->findById($account->getId());

        $this->assertNotNull($found);
        $this->assertSame($account->getId(), $found->getId());
        $this->assertSame('USD', $found->getCurrency());
    }

    public function testFindByIdReturnsNullForInvalidId(): void
    {
        $result = $this->repository->findById(9999);
        $this->assertNull($result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager?->close();
        $this->entityManager = null;
    }
}
