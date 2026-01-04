<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Selector;

use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;


class QueryClass implements SelectorInterface
{
    public const EXACT = 'exact';
    public const STARTS_WITH = 'starts_with';
    public const ENDS_WITH = 'ends_with';
    public const CONTAINS = 'contains';

    public function __construct(
        private string $name,
        private string $matchType = self::EXACT,
    ) {
    }

    public function getXPathSelector(?string $context = null): string
    {
        switch ($this->matchType) {
            case self::STARTS_WITH:

                return "starts-with(normalize-space(@class), '{$this->name}')";
            case self::ENDS_WITH:
                return "substring(normalize-space(@class), string-length(normalize-space(@class)) - string-length('{$this->name}') + 1) = '{$this->name}'";
            case self::CONTAINS:
                return "contains(@class, '{$this->name}')";
            case self::EXACT:
            default:
                return "contains(concat(' ', normalize-space(@class), ' '), ' {$this->name} ' )";
        }
    }

}
