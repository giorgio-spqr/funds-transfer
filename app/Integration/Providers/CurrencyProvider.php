<?php

namespace App\Integration\Providers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CurrencyProvider
{
    private Client $client;
    private string $baseUri;
    private string $key;

    public function __construct()
    {
        $this->baseUri = config('services.exchange_rate.base_uri');
        $this->key = config('services.exchange_rate.key');

        $this->client = new Client([
            'base_uri' => $this->baseUri,
        ]);
    }

    public function getAvailableCurrencies(): ?array
    {
        $uri = "/list?access_key=$this->key";
        $cacheKey = md5($this->baseUri . $uri);

        $content = Cache::get($cacheKey);
        if ($content) {
            return $content;
        }

        $content = $this->getResponse($uri);

        if (!is_array($content)) {
            return null;
        }

        $currencies = data_get($content, 'currencies');
        if (!is_array($currencies) or empty($currencies)) {
            return null;
        }

        $currencies = array_keys($currencies);

        Cache::put($cacheKey, $currencies, Carbon::now()->addDay());

        return $currencies;
    }

    public function getExchangeRateForCurrencies(string $from, string $to): ?float
    {
        $uri = "/live?access_key=$this->key&source=$from";
        $cacheKey = md5($this->baseUri . $uri);

        $currencyPair = $from . $to;

        $quotes = Cache::get($cacheKey);
        if ($quotes) {
            return data_get($quotes, $currencyPair);
        }

        $content = $this->getResponse($uri);
        if (!is_array($content)) {
            return null;
        }

        $quotes = data_get($content, 'quotes');
        if (!is_array($quotes) or empty($quotes)) {
            return null;
        }

        Cache::put($cacheKey, $quotes, Carbon::now()->addDay());

        return data_get($quotes, $currencyPair);
    }

    protected function getResponse(string $uri): ?array
    {
        try {
            $response = $this->client->request(
                method: 'GET',
                uri: $uri,
                options: [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ],
            );

            $json = $response->getBody()->getContents();

            $content = json_decode($json, true);

            if (!is_array($content)) {
                return null;
            }

        } catch (Throwable $e) {
            return null;
        }

        return $content;
    }
}
