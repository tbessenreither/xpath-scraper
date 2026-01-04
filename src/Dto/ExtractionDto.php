<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Dto;


class ExtractionDto
{

    /**
     * @param array $attributes key-value pairs of attribute names and values
     */
    public function __construct(
        private ?string $text,
        private ?string $html,
        private ?string $outerHtml,
        private array $attributes = [],
    ) {
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function getOuterHtml(): ?string
    {
        return $this->outerHtml;
    }

    public function getAttribute(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }

}
