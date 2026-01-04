<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Selector;

use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;


class QueryElement implements SelectorInterface
{

    /**
     * @param SelectorInterface[] $classLogics
     * @param SelectorInterface[] $attributeLogics
     */
    public function __construct(
        private string $tag,
        private array $classLogics = [],
        private array $attributeLogics = [],
    ) {
    }

    public function getXPathSelector(?string $context = null): string
    {
        $selectorString = '';
        foreach ($this->classLogics as $logic) {
            $selectorString .= '[' . $logic->getXPathSelector('class') . ']';
        }
        foreach ($this->attributeLogics as $logic) {
            $selectorString .= '[' . $logic->getXPathSelector('attribute') . ']';
        }

        return $this->tag . $selectorString;
    }

}
