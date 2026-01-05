<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Service;

use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;


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
        // Special handling: if the first element is a LogicWrapper (OR),
        // generate a union of full paths for each branch.
        if (!empty($this->elements) && $this->elements[0] instanceof LogicWrapper && $this->elements[0]->isElementLevelOr()) {
            $paths = [];
            foreach ($this->elements[0]->getChildren() as $branch) {
                $branchElements = [$branch, ...array_slice($this->elements, 1)];
                $selectors = array_map(fn($el) => ltrim($el->getXPathSelector(), '/'), $branchElements);
                if (!empty($selectors)) {
                    $selectors[0] = '//' . ltrim($selectors[0], '/');
                }
                $paths[] = implode('/', $selectors);
            }

            return implode(' | ', $paths);
        }
        // Remove leading '//' from each element except the first
        $selectors = array_map(fn($el) => ltrim($el->getXPathSelector(), '/'), $this->elements);
        // Add leading '//' to the first element only
        if (!empty($selectors)) {
            $selectors[0] = '//' . ltrim($selectors[0], '/');
        }

        return implode('/', $selectors);
    }

}
