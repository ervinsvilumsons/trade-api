<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Service\CurrencyConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TransferService {

    private EntityManagerInterface $entityManager;
    private CurrencyConverter $currencyConverter;

    public function __construct(EntityManagerInterface $entityManager, CurrencyConverter $currencyConverter)
    {
        $this->entityManager = $entityManager;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @param Account $fromAccount
     * @param Account $toAccount
     * @param float $amount
     * @return void
     */
    public function transferFunds(Account $fromAccount, Account $toAccount, float $amount): void 
    {
        if ($amount <= 0) throw new BadRequestHttpException('Transfer amount must be positive.');
        if ($fromAccount->getBalance() < $amount) throw new BadRequestHttpException('Insufficient funds in sender account.');

        $convertedData = [
            'amount' => $amount,
            'exchange_rate' => 1,
        ];
        $fromCurrency = $fromAccount->getCurrency();
        $toCurrency = $toAccount->getCurrency();

        if ($fromCurrency!== $toCurrency) {
            $convertedData = $this->currencyConverter->convert($fromCurrency, $toCurrency, $amount);
        }

        $em = $this->entityManager;
        $conn = $em->getConnection();
        $conn->beginTransaction();
        
        $fromAccount->setBalance($fromAccount->getBalance() - $amount);
        $toAccount->setBalance($toAccount->getBalance() + $convertedData['amount']);
        $em->persist($fromAccount);
        $em->persist($toAccount);

        $transaction = new Transaction();
        $transaction->setFromAccount($fromAccount);
        $transaction->setToAccount($toAccount);
        $transaction->setOriginalAmount($amount);
        $transaction->setConvertedAmount($convertedData['amount']);
        $transaction->setFromCurrency($fromCurrency);
        $transaction->setToCurrency($toCurrency);
        $transaction->setExchangeRate($convertedData['exchange_rate']);
        $em->persist($transaction);
        
        $em->flush();
        $conn->commit();
    }
}
