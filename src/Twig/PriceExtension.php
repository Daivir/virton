<?php
namespace Virton\Twig;

class PriceExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private $currency;

    /**
     * PriceExtension constructor.
     * @param string $currency eur|usd
     */
    public function __construct(string $currency = 'usd')
    {
        $this->currency = $currency;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('price_format', [$this, 'priceFormat']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('vat', [$this, 'getVat']),
            new \Twig_SimpleFunction('vat_only', [$this, 'getVatOnly'])
        ];
    }

    public function priceFormat(?float $price, ?string $currency = null): string
    {
        if ($currency) {
            return number_format($price) . $currency;
        }
        switch ($this->currency) {
            case 'eur':
                return number_format($price, 2, ',', ' ') . ' €';
            break;
            case 'usd':
                return '$' . number_format($price, 2, '.', ',');
            break;
        }
    }

    public function getVat(float $price, ?float $vatRate): float
    {
        return $price + $this->getVatOnly($price, $vatRate);
    }

    public function getVatOnly(float $price, ?float $vatRate): float
    {
        if (is_null($vatRate)) {
            return 0;
        }
        return $price * $vatRate / 100;
    }
}
