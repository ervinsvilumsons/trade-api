<?php

namespace App\Controller;

use App\Repository\AccountRepository;
use App\Repository\ClientRepository;
use App\Repository\TransactionRepository;
use App\Service\TransferService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController extends AbstractController
{
    public function __construct(
        private AccountRepository $accountRepository, 
        private ClientRepository $clientRepository, 
        private TransactionRepository $transactionRepository,
        private TransferService $transferService,
    ) 
    {}

    #[Route('/api/clients/{id}/accounts', name: 'client_accounts', methods: ['GET'])]
    public function getClientAccounts(int $id): JsonResponse 
    {
        $client = $this->clientRepository->findById($id) ?? 
            throw new NotFoundHttpException("Client with ID $id not found.");

        $data = array_map(fn($account) => [
            'id' => $account->getId(),
            'currency' => $account->getCurrency(),
            'balance' => $account->getBalance(),
            'createdAt' => $account->getCreatedAt()->format('Y-m-d H:i:s'),
        ], $client->getAccounts()->toArray());

        return $this->json($data);
    }

    #[Route('/api/accounts/{id}/transactions', name: 'account_transactions', methods: ['GET'])]
    public function getTransactions(int $id, Request $request): JsonResponse 
    {
        $offset = max(0, (int) $request->query->get('offset', 0));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        $account = $this->accountRepository->findById($id) ?? 
            throw new NotFoundHttpException("Account with ID $id not found.");

        $transactions = $this->transactionRepository->findByAccountIdWithPagination($account->getId(), $offset, $limit);
        $total = $this->transactionRepository->countAccountTransactions($account->getId());

        $data = array_map(fn($txn) => [
            'id' => $txn->getId(),
            'from_account_id' => $txn->getFromAccountId(),
            'to_account_id' => $txn->getToAccountId(),
            'from_currency' => $txn->getFromCurrency(),
            'to_currency' => $txn->getToCurrency(),
            'original_amount' => $txn->getOriginalAmount(),
            'convertedAmount' => $txn->getConvertedAmount(),
            'exchange_rate' => $txn->getExchangeRate(),
            'created_at' => $txn->getCreatedAt()->format('Y-m-d H:i:s'),
        ], $transactions);

        return $this->json([
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
            'results' => $data,
        ]);
    }

    #[Route('/api/transfer', name: 'transfer_funds', methods: ['POST'])]
    public function transfer(Request $request, TransferService $service): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);

        $fromAccountId = $data['from_account_id'] ?? null;
        $toAccountId = $data['to_account_id'] ?? null;
        $amount = $data['amount'] ?? null;

        if (!$fromAccountId || !$toAccountId || !$amount) {
            throw new BadRequestHttpException("Missing required parameters");
        }

        $amount = (float) $amount;
        $fromAccount = $this->accountRepository->findById($fromAccountId) ?? 
            throw new NotFoundHttpException("Sender account not found.");
        $toAccount = $this->accountRepository->findById($toAccountId) ?? 
            throw new NotFoundHttpException("Receiver account not found.");

        try {
            $this->transferService->transferFunds($fromAccount, $toAccount, $amount);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return $this->json(['message' => 'Transfer successful!']);
    }
}
