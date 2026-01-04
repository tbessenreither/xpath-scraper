<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Service;

use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;


class QueryBuilder
{

    /**
     * @param SelectorInterface[] $elements
     */
    public function __construct(
        private array $elements,
    ) {
    }

    public function getXPathSelector(): string
    {
        // Remove leading '//' from each element except the first
        $selectors = array_map(fn($el) => ltrim($el->getXPathSelector(), '/'), $this->elements);
        // Add leading '//' to the first element only
        if (!empty($selectors)) {
            $selectors[0] = '//' . ltrim($selectors[0], '/');
        }

        return implode('/', $selectors);
    }

}
