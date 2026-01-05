<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Selector;

use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;


class QueryElement implements SelectorInterface
{

    /**
     * @param string $tag
     * @param SelectorInterface[] $attributes
     */
    public function __construct(
        private string $tag = '*',
        private array $attributes = [],
        private bool $isDirectChild = false,
    ) {
    }

    public function getXPathSelector(?string $context = null): string
    {
        $selectorString = '';
        foreach ($this->attributes as $selector) {
            $selectorString .= '[' . $selector->getXPathSelector() . ']';
        }

        return $this->tag . $selectorString;
    }

    public function isDirectChild(): bool
    {
        return $this->isDirectChild;
    }

}
