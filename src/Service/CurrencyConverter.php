<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

class CurrencyConverter {

    private HttpClientInterface $client;
    public const API_URL = 'https://api.frankfurter.app/latest';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    
    /**
     * Converts amount from one currency to another
     * 
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $amount
     * @return array
     * @throws \RuntimeException,\InvalidArgumentException
     */
    public function convert(string $fromCurrency, string $toCurrency, float $amount): array 
    {
        $response = $this->client->request('GET', self::API_URL, [
            'query' => [
                'from' => $fromCurrency,
                'to' => $toCurrency,
            ]
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) throw new \RuntimeException('Currency conversion service unavailable, please try again later');
        $data = $response->toArray();

        if (!isset($data['rates'][$toCurrency])) throw new \InvalidArgumentException("Currency '$toCurrency' not supported.");
        $rate = $data['rates'][$toCurrency];

        return [
            'amount' => $amount * $rate,
            'exchange_rate' => $rate,
        ];
    }
}
