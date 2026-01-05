<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Service;

use DOMDocument;
use DOMXPath;
use DOMNode;
use DOMNodeList;

use Tbessenreither\XPathScraper\QueryBuilder\Service\QueryBuilder;
use Tbessenreither\XPathScraper\Dto\ExtractionDto;
use Tbessenreither\XPathScraper\Dto\ExtractionsDto;


class Scraper
{
    public const EXTRACT_TEXT = 'text';
    public const EXTRACT_HTML = 'html';
    public const EXTRACT_OUTER_HTML = 'outerHtml';
    public const EXTRACT_ATTRIBUTE_PREFIX = 'attribute:';
    private DOMDocument $dom;
    private DOMXPath $xpath;
    /** @var DOMNode[] */
    private array $nodes;

    /**
     * @param string|DOMNode|DOMNode[] $input
     */
    public function __construct(string|DOMNode|array $input)
    {
        if (is_string($input)) {
            $this->dom = new DOMDocument();
            @$this->dom->loadHTML($input);
            $this->xpath = new DOMXPath($this->dom);
            $this->nodes = [$this->dom->documentElement];
        } elseif ($input instanceof DOMNode) {
            $this->dom = $input->ownerDocument ?? $input;
            $this->xpath = new DOMXPath($this->dom);
            $this->nodes = [$input];
        } elseif (is_array($input) && isset($input[0]) && $input[0] instanceof DOMNode) {
            $this->dom = $input[0]->ownerDocument;
            $this->xpath = new DOMXPath($this->dom);
            $this->nodes = $input;
        } else {
            throw new \InvalidArgumentException('Invalid input for XPathScraper');
        }
    }

    /**
     * @return Scraper|null
     */
    public function get(QueryBuilder $query): ?Scraper
    {
        $selector = $query->getXPathSelector();
        $results = [];
        foreach ($this->nodes as $node) {
            $xpath = new DOMXPath($node->ownerDocument);
            $nodeList = $xpath->query($selector, $node);
            if ($nodeList) {
                foreach ($nodeList as $found) {
                    $oid = spl_object_id($found);
                    $results[$oid] = $found;
                }
            }
        }
        if (count($results) === 0) {
            return null;
        }

        // Only unique nodes are passed to the next Scraper
        return new self(array_values($results));
    }

    /**
     * @param string[] $fields
     * @return array
     */
    public function extract(array $fields): ExtractionsDto
    {
        $output = [];
        foreach ($this->nodes as $node) {
            $text = null;
            $html = null;
            $outerHtml = null;
            $attributes = [];
            foreach ($fields as $field) {
                if ($field === self::EXTRACT_TEXT) {
                    $text = $node->textContent;
                } elseif ($field === self::EXTRACT_HTML) {
                    $html = $this->getInnerHtml($node);
                } elseif ($field === self::EXTRACT_OUTER_HTML) {
                    $outerHtml = $this->getOuterHtml($node);
                } elseif (str_starts_with($field, self::EXTRACT_ATTRIBUTE_PREFIX)) {
                    $attr = substr($field, mb_strlen(self::EXTRACT_ATTRIBUTE_PREFIX));
                    $attributes[$attr] = $node->attributes?->getNamedItem($attr)?->value ?? null;
                }
            }
            $output[] = new ExtractionDto($text, $html, $outerHtml, $attributes);
        }
        return new ExtractionsDto($output);
    }

    private function getInnerHtml(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }
        return $html;
    }

    private function getOuterHtml(DOMNode $node): string
    {
        return $node->ownerDocument->saveHTML($node);
    }

}
