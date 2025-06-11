<?php
namespace App\Tests\Service;

use App\Service\CurrencyConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class CurrencyConverterTest extends TestCase
{
    private $clientMock;
    private $responseMock;
    private CurrencyConverter $converter;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(HttpClientInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->converter = new CurrencyConverter($this->clientMock);
    }

    /**
     * @return void
     */
    public function testSuccessfulConversion(): void
    {
        $from = 'USD';
        $to = 'EUR';
        $amount = 10.0;
        $data = [
            'rates' => [
                $to => 0.85,
            ]
        ];

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('GET', CurrencyConverter::API_URL, [
                'query' => ['from' => $from, 'to' => $to],
            ])
            ->willReturn($this->responseMock);

        $this->responseMock->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->responseMock->method('toArray')->willReturn($data);

        $result = $this->converter->convert($from, $to, $amount);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertArrayHasKey('exchange_rate', $result);
        $this->assertEquals($amount * 0.85, $result['amount']);
        $this->assertEquals(0.85, $result['exchange_rate']);
    }

    /**
     * @return void
     */
    public function testApiUnavailableThrowsRuntimeException(): void
    {
        $this->clientMock->method('request')->willReturn($this->responseMock);
        $this->responseMock->method('getStatusCode')->willReturn(Response::HTTP_SERVICE_UNAVAILABLE);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Currency conversion service unavailable');

        $this->converter->convert('USD', 'EUR', 10);
    }

    /**
     * @return void
     */
    public function testUnsupportedCurrencyThrowsInvalidArgumentException(): void
    {
        $this->clientMock->method('request')->willReturn($this->responseMock);
        $this->responseMock->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->responseMock->method('toArray')->willReturn(['rates' => []]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Currency 'EUR' not supported.");

        $this->converter->convert('USD', 'EUR', 10);
    }
}
