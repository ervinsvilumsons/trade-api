<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @param int $id
     * @return Client|null
     */
    public function findById(int $id): ?Client
    {
        return $this->createQueryBuilder('client')
            ->andWhere('client.id = :id')
            ->setParameter('id', $id)
            ->orderBy('client.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
