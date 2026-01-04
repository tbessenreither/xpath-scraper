<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Interface;


interface SelectorInterface
{

    /**
     * Returns the XPath selector string for this selector.
     *
     * @param string|null $context Optional context (e.g., 'class', 'attribute') for logic wrappers
     * @return string
     */
    public function getXPathSelector(?string $context = null): string;

}
