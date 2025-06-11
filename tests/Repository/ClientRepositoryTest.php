<?php

namespace App\Tests\Repository;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClientRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?ClientRepository $repository = null;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(Client::class);
    }

    public function testFindByIdReturnsCorrectClient(): void
    {
        $client = new Client();
        $client->setName('Test Client');
        $client->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $found = $this->repository->findById($client->getId());

        $this->assertNotNull($found);
        $this->assertSame('Test Client', $found->getName());
        $this->assertSame($client->getId(), $found->getId());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager?->close();
        $this->entityManager = null;
    }
}
