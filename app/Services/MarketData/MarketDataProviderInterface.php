<?php

namespace App\Services\MarketData;

interface MarketDataProviderInterface
{
    /**
     * Get the provider's unique identifier.
     */
    public function getIdentifier(): string;

    /**
     * Determine if this provider supports the given exchange.
     */
    public function supportsExchange(?string $exchange): bool;

    /**
     * Retrieve the current normalized quote for a stock symbol.
     *
     * @param  string  $symbol  Stock ticker symbol (e.g. 2330, 2317, 2454)
     * @param  string|null  $exchange  Exchange code (e.g. TWSE, TPEx)
     * @return MarketQuote|null Returns null if quote is unavailable or request fails
     */
    public function getQuote(string $symbol, ?string $exchange = null): ?MarketQuote;
}
