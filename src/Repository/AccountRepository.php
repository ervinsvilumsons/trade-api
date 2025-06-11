<?php

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @param int $id
     * @return Account|null
     */
    public function findById(int $id): ?Account
    {
        return $this->createQueryBuilder('account')
            ->andWhere('account.id = :id')
            ->setParameter('id', $id)
            ->orderBy('account.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
