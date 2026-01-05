<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Service;

use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;


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
        // Build XPath with respect to isDirectChild
        $selectors = [];
        foreach ($this->elements as $i => $el) {
            $xpath = ltrim($el->getXPathSelector(), '/');
            if ($i === 0) {
                $selectors[] = '//' . $xpath;
            } else {
                // Only use '/' if current is QueryElement, isDirectChild is true, and previous is also QueryElement
                $prev = $this->elements[$i - 1];
                if (
                    $el instanceof QueryElement &&
                    $el->isDirectChild() &&
                    $prev instanceof QueryElement
                ) {
                    $selectors[] = '/' . $xpath;
                } else {
                    $selectors[] = '//' . $xpath;
                }
            }
        }
            // Join selectors, always add / between them except for the first
        $xpath = array_shift($selectors);
        foreach ($selectors as $sel) {
            if (str_starts_with($sel, '/')) {
                $xpath .= $sel;
            } else {
                    $xpath .= $sel;
            }
        }
        return $xpath;
    }

}
