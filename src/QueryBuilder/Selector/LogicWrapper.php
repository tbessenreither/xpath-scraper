<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Selector;

use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;


class LogicWrapper implements SelectorInterface
{
    public const AND = 'and';
    public const OR = 'or';

    public function __construct(
        private string $type,
        private array $children,
    ) {
    }

    public function getXPathSelector(?string $context = null): string
    {
        $selectors = array_map(function ($child) use ($context) {
            return $child->getXPathSelector($context);
        }, $this->children);
        $glue = $this->type === self::AND ? ' and ' : ' or ';
        $expr = implode($glue, $selectors);

        return '(' . $expr . ')';
    }

}
