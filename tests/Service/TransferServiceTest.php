<?php

namespace App\Tests\Service;

use App\Entity\Account;
use App\Service\TransferService;
use App\Service\CurrencyConverter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TransferServiceTest extends TestCase
{
    private $em;
    private $converter;
    private $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->converter = $this->createMock(CurrencyConverter::class);
        $this->service = new TransferService($this->em, $this->converter);
    }

    public function testSuccessfulTransferSameCurrency(): void
    {
        $from = (new Account())->setBalance(100)->setCurrency('USD');
        $to = (new Account())->setBalance(50)->setCurrency('USD');

        // We don't need conversion when currencies match
        $this->em->method('getConnection')->willReturn($this->createMock(\Doctrine\DBAL\Connection::class));
        $this->em->expects($this->once())->method('flush');

        $this->service->transferFunds($from, $to, 30);

        $this->assertEquals(70, $from->getBalance());
        $this->assertEquals(80, $to->getBalance());
    }

    public function testInsufficientFundsThrows(): void
    {
        $from = (new Account())->setBalance(10)->setCurrency('USD');
        $to = (new Account())->setBalance(50)->setCurrency('USD');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Insufficient funds');

        $this->service->transferFunds($from, $to, 50);
    }

    public function testTransferWithCurrencyConversion(): void
    {
        $from = (new Account())->setBalance(100)->setCurrency('USD');
        $to = (new Account())->setBalance(0)->setCurrency('EUR');

        $this->converter
            ->expects($this->once())
            ->method('convert')
            ->with('USD', 'EUR', 50)
            ->willReturn([
                'amount' => 45.0,
                'exchange_rate' => 0.9,
            ]);

        $this->em->method('getConnection')->willReturn($this->createMock(\Doctrine\DBAL\Connection::class));
        $this->em->expects($this->once())->method('flush');

        $this->service->transferFunds($from, $to, 50);

        $this->assertEquals(50, $from->getBalance());
        $this->assertEquals(45, $to->getBalance());
    }
}