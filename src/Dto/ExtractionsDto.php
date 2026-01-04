<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Dto;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
use LogicException;
use Tbessenreither\XPathScraper\Service\Scraper;
use Tbessenreither\XPathScraper\Dto\ExtractionDto;


class ExtractionsDto implements Countable, ArrayAccess, IteratorAggregate
{

    /** @param ExtractionDto[] $items */
    public function __construct(private array $items)
    {
    }

    public function getAll(string $field): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($field === Scraper::EXTRACT_TEXT) {
                $result[] = $item->getText();
            } elseif ($field === Scraper::EXTRACT_HTML) {
                $result[] = $item->getHtml();
            } elseif ($field === Scraper::EXTRACT_OUTER_HTML) {
                $result[] = $item->getOuterHtml();
            } else {
                $result[] = $item->getAttribute($field);
            }
        }

        return $result;
    }

    public function element(int $i): ?ExtractionDto
    {
        return $this->items[$i] ?? null;
    }

    public function first(): ?ExtractionDto
    {
        return $this->items[0] ?? null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): ?ExtractionDto
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new LogicException('ExtractionsDto is immutable');
    }

    public function offsetUnset($offset): void
    {
        throw new LogicException('ExtractionsDto is immutable');
    }

}
