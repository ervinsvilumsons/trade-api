<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @param int $accountId
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function findByAccountIdWithPagination(int $accountId, int $offset = 0, int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.fromAccount = :accountId')
            ->orWhere('t.toAccount = :accountId')
            ->setParameter('accountId', $accountId)
            ->orderBy('t.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $accountId
     * @return int
     */
    public function countAccountTransactions(int $accountId): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.fromAccount = :accountId')
            ->orWhere('t.toAccount = :accountId')
            ->setParameter('accountId', $accountId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
