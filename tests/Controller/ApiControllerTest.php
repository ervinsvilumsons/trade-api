<?php
namespace App\Tests\Controller;

use App\Entity\Account;
use App\Entity\Client;
use App\Entity\Transaction;
use App\Repository\AccountRepository;
use App\Repository\ClientRepository;
use App\Repository\TransactionRepository;
use App\Service\TransferService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerTest extends WebTestCase
{
    private $client;
    private $clientRepositoryMock;
    private $accountRepositoryMock;
    private $transactionRepositoryMock;
    private $transferServiceMock;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->clientRepositoryMock = $this->createMock(ClientRepository::class);
        $this->accountRepositoryMock = $this->createMock(AccountRepository::class);
        $this->transactionRepositoryMock = $this->createMock(TransactionRepository::class);
        $this->transferServiceMock = $this->createMock(TransferService::class);

        self::getContainer()->set(ClientRepository::class, $this->clientRepositoryMock);
        self::getContainer()->set(AccountRepository::class, $this->accountRepositoryMock);
        self::getContainer()->set(TransactionRepository::class, $this->transactionRepositoryMock);
        self::getContainer()->set(TransferService::class, $this->transferServiceMock);
    }

    /**
     * @return void
     */
    public function testGetClientAccountsReturnsAccounts(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn(456);
        $account->method('getCurrency')->willReturn('USD');
        $account->method('getBalance')->willReturn('100');
        $account->method('getCreatedAt')->willReturn(new \DateTimeImmutable());

        $client = $this->createMock(Client::class);
        $client->method('getId')->willReturn(123);
        $client->method('getAccounts')->willReturn(new ArrayCollection([$account]));

        $this->clientRepositoryMock
            ->method('findById')
            ->willReturn($client);

        $this->client->request('GET', '/api/clients/123/accounts');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $responseData);
        $this->assertEquals(456, $responseData[0]['id']);
        $this->assertEquals('USD', $responseData[0]['currency']);
        $this->assertEquals(100, $responseData[0]['balance']);
    }

    /**
     * @return void
     */
    public function testGetClientAccountsClientNotFound(): void
    {
        $this->clientRepositoryMock
            ->method('findById')
            ->willReturn(null);

        $this->client->request('GET', '/api/clients/999/accounts');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @retrun void
     */
    public function testGetTransactionsWithPagination(): void
    {
        $fromAccount = $this->createMock(Account::class);
        $fromAccount->method('getId')->willReturn(5);
        $fromAccount->method('getCurrency')->willReturn('USD');
        $fromAccount->method('getBalance')->willReturn('100');
        
        $toAccount = $this->createMock(Account::class);
        $toAccount->method('getId')->willReturn(6);
        $toAccount->method('getCurrency')->willReturn('EUR');
        $toAccount->method('getBalance')->willReturn('100');

        $transaction = new Transaction();
        $transaction->setFromAccount($fromAccount);
        $transaction->setToAccount($toAccount);
        $transaction->setFromCurrency('USD');
        $transaction->setToCurrency('EUR');
        $transaction->setOriginalAmount(50);
        $transaction->setConvertedAmount(45);
        $transaction->setExchangeRate(0.9);
        
        $reflection = new \ReflectionClass(Transaction::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($transaction, 123);
        $transaction->setCreatedAt(new \DateTimeImmutable('2025-06-11 12:00:00'));

        $this->accountRepositoryMock->method('findById')->willReturn($fromAccount);
        $this->transactionRepositoryMock
            ->method('findByAccountIdWithPagination')
            ->willReturn([$transaction]);
        $this->transactionRepositoryMock
            ->method('countAccountTransactions')
            ->willReturn(1);

        $this->client->request('GET', "/api/accounts/5/transactions?offset=0&limit=10");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(0, $responseData['offset']);
        $this->assertEquals(10, $responseData['limit']);
        $this->assertEquals(1, $responseData['total']);
        $this->assertCount(1, $responseData['results']);

        $txn = $responseData['results'][0];

        $this->assertEquals(123, $txn['id']);
        $this->assertEquals(5, $txn['from_account_id']);
        $this->assertEquals(6, $txn['to_account_id']);
        $this->assertEquals('USD', $txn['from_currency']);
        $this->assertEquals('EUR', $txn['to_currency']);
        $this->assertEquals(50, $txn['original_amount']);
        $this->assertEquals(45, $txn['convertedAmount']);
        $this->assertEquals(0.9, $txn['exchange_rate']);
        $this->assertEquals('2025-06-11 12:00:00', $txn['created_at']);
    }

    /**
     * @return void
     */
    public function testGetAccountTransactionsNotFound(): void
    {
        $this->accountRepositoryMock
            ->method('findById')
            ->willReturn(null);

        $this->client->request('GET', '/api/accounts/999/transactions');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @retrun void
     */
    public function testTransferSuccess(): void
    {
        $postData = [
            'from_account_id' => 1,
            'to_account_id' => 2,
            'amount' => 30,
        ];

        $fromAccount = new Account();
        $toAccount = new Account();

        $this->accountRepositoryMock->method('findById')
            ->willReturnMap([
                [1, $fromAccount],
                [2, $toAccount],
            ]);

        $this->transferServiceMock->expects($this->once())
            ->method('transferFunds')
            ->with($fromAccount, $toAccount, 30);

        $this->client->request(
            'POST',
            '/api/transfer',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Transfer successful!', $responseData['message']);
    }

    /**
     * @retrun void
     */
    public function testTransferMissingParams(): void
    {
        $this->client->request('POST', '/api/transfer', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @retrun void
     */
    public function testTransferAccountNotFound(): void
    {
        $postData = [
            'from_account_id' => 1,
            'to_account_id' => 9999,
            'amount' => 30,
        ];

        $this->accountRepositoryMock->method('findById')->willReturn(null);

        $this->client->request(
            'POST',
            '/api/transfer',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @retrun void
     */
    public function testTransferAccountInsufficientFunds(): void
    {
        $postData = [
            'from_account_id' => 1,
            'to_account_id' => 2,
            'amount' => 300000,
        ];

        $fromAccount = new Account();
        $toAccount = new Account();

        $this->accountRepositoryMock->method('findById')
            ->willReturnMap([
                [1, $fromAccount],
                [2, $toAccount],
            ]);

        $this->transferServiceMock->expects($this->once())
            ->method('transferFunds')
            ->with($fromAccount, $toAccount, 300000)
            ->willThrowException(new \Exception('Insufficient funds in sender account.'));

        $this->client->request(
            'POST',
            '/api/transfer',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
