<?php

namespace Tests\Unit;

use App\Integration\Providers\CurrencyProvider;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CurrencyProviderTest extends TestCase
{
    protected MockObject $provider;

    public function setUp(): void
    {
        parent::setUp();

        $this->provider = $this->getMockBuilder(CurrencyProvider::class)
            ->onlyMethods(['getResponse'])
            ->getMock();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_available_currencies(): void
    {
        $currencies = [
            'currencies' => [
                'USD' => 'USD',
                'EUR' => 'EUR',
                'JPY' => 'JPY',
            ],
        ];

        $this->provider->method('getResponse')
            ->willReturn($currencies);

        $result = $this->provider->getAvailableCurrencies();

        $this->assertEquals(['USD', 'EUR', 'JPY'], $result);
    }

    public function test_get_exchange_rate_for_currencies()
    {
        $quotes = [
            'quotes' => [
                'USDAED' => 3.67315,
                'USDAFN' => 60.790001,
                'USDALL' => 126.194504,
                'USDAMD' => 477.359985,
                'USDANG' => 1.790403,
            ],
        ];

        $this->provider->method('getResponse')
            ->willReturn($quotes);

        $result = $this->provider->getExchangeRateForCurrencies('USD', 'AED');

        $this->assertEquals(3.67315, $result);

        $result = $this->provider->getExchangeRateForCurrencies('USD', 'EUR');

        $this->assertEquals(null, $result);

        $result = $this->provider->getExchangeRateForCurrencies('USD', 'ALL');

        $this->assertEquals(126.194504, $result);
    }
}
