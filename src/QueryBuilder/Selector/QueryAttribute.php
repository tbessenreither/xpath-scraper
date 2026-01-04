<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Selector;

use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;


class QueryAttribute implements SelectorInterface
{
    public const EXACT = 'exact';
    public const STARTS_WITH = 'starts_with';
    public const ENDS_WITH = 'ends_with';
    public const CONTAINS = 'contains';

    public function __construct(
        private string $name,
        private string $value,
        private string $matchType = self::EXACT,
    ) {
    }

    public function getXPathSelector(?string $context = null): string
    {
        switch ($this->matchType) {
            case self::STARTS_WITH:

                return "starts-with(@{$this->name}, '{$this->value}')";
            case self::ENDS_WITH:
                return "substring(@{$this->name}, string-length(@{$this->name}) - string-length('{$this->value}') + 1) = '{$this->value}'";
            case self::CONTAINS:
                return "contains(@{$this->name}, '{$this->value}')";
            case self::EXACT:
            default:
                return "@{$this->name}='{$this->value}'";
        }
    }

}
