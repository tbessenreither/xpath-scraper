<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Selector;

use Tbessenreither\XPathScraper\Enum\PseudoclassOptions;
use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;


class QueryPseudoclass implements SelectorInterface
{

    public function __construct(
        private PseudoclassOptions $name,
        private string $value,
    ) {
    }

    public function getXPathSelector(?string $context = null): string
    {
        return $this->name->value . '()=' . $this->value;
    }

}
