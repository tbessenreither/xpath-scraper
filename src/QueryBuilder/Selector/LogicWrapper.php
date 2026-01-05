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
        if ($this->type === self::OR && $this->allChildrenAreElements()) {
            // Node set union: join with |
            $paths = array_map(fn($child) => $child->getXPathSelector($context), $this->children);

            return implode(' | ', $paths);
        }

        // Predicate-level logic
        $selectors = array_map(function ($child) use ($context) {
            return $child->getXPathSelector($context);
        }, $this->children);
        $glue = $this->type === self::AND ? ' and ' : ' or ';
        $expr = implode($glue, $selectors);

        return '(' . $expr . ')';
    }

    public function isElementLevelOr(): bool
    {
        return $this->type === self::OR && $this->allChildrenAreElements();
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    private function allChildrenAreElements(): bool
    {
        foreach ($this->children as $child) {
            if (!($child instanceof QueryElement)) {
                return false;
            }
        }

        return true;
    }

}
