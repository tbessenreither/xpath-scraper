<?php
declare(strict_types=1);

namespace Tbessenreither\XPathScraper\QueryBuilder\Service;

use InvalidArgumentException;
use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryAttribute;
use Tbessenreither\XPathScraper\QueryBuilder\Service\QueryBuilder;


class CssQueryBuilder extends QueryBuilder
{

	private ?string $combinatorStarted = null;
	private array $combinatorStorage = [];

	public function __construct(string $cssQuery)
	{
		$queryBuilderElements = $this->parseCssToQueryElements($cssQuery);
		parent::__construct($queryBuilderElements);
	}

	private function parseCssToQueryElements(string $css): array
	{
		$queryElements = [];
		$regexTag = '/(?P<outerElement>(?P<tag>[\w\*]*)(?P<classes>(?:\.[\^~$]?[\w\-]+)+)?(?P<attributes>(?:\[[^\]\[]+\])*))(?P<combinator>(?:\s+[>]\s+|,))?/';


		$elementMatches = [];
		preg_match_all($regexTag, $css, $elementMatches, PREG_SET_ORDER + PREG_UNMATCHED_AS_NULL);
		$this->cleanupMatches($elementMatches);

		$this->combinatorStarted = null;
		$this->combinatorStorage = [];
		foreach ($elementMatches as $key => &$match) {
			if (empty($match['outerElement'])) {
				unset($elementMatches[$key]);
				continue;
			}

			$queryElement = $this->parseMatch($match);

			if (
				(
					$this->combinatorStarted === null
					|| $this->combinatorStarted === 'or'
				) &&
				$match['combinator'] === ','
			) {
				if ($this->combinatorStarted === null) {
					$this->combinatorStarted = 'or';
					$this->combinatorStorage = [];
				}
				$this->combinatorStorage[] = $queryElement;
				continue;
			}

			if ($this->combinatorStarted === 'or' && $match['combinator'] !== ',') {
				//close OR wrapper
				$this->combinatorStorage[] = $queryElement;
				$queryElements[] = new LogicWrapper(LogicWrapper::OR , $this->combinatorStorage);
				$this->combinatorStorage = [];
				$this->combinatorStarted = null;
			} else {
				$queryElements[] = $queryElement;
			}

			if ($match['combinator'] === '>') {
				$this->combinatorStarted = '>';
			}
		}
		unset($match);

		//reset array keys
		$elementMatches = array_values($elementMatches);

		if (empty($queryElements)) {
			throw new InvalidArgumentException('Could not parse CSS query: ' . $css);
		}

		return $queryElements;
	}

	private function cleanupMatches(array &$matches): void
	{
		foreach ($matches as $key => &$match) {
			if (empty($match['outerElement'])) {
				unset($matches[$key]);
				continue;
			}
			foreach ($match as $subKey => &$value) {
				if (is_int($subKey) || $value === '') {
					unset($matches[$key][$subKey]);
				}
				if ($value === '') {
					$value = null;
				}
			}
			if ($match['combinator'] !== null) {
				$match['combinator'] = trim($match['combinator']);
			}
			unset($value);
		}
		unset($match);

		$matches = array_values($matches);
	}

	private function parseMatch(array &$match): QueryElement
	{
		if (!isset($match['classes'])) {
			$match['classes'] = null;
		}
		if (!isset($match['attributes'])) {
			$match['attributes'] = null;
		}
		$match['classes'] = $this->parseClasses($match['classes']);
		$match['attributes'] = $this->parseAttributes($match['attributes']);

		return new QueryElement(
			tag: $match['tag'] ?? '*',
			attributes: array_filter([
				$match['classes'],
				$match['attributes'],
			]),
			isDirectChild: $this->combinatorStarted === '>'
		);

		// Reset combinator after use
		if ($this->combinatorStarted === '>') {
			$this->combinatorStarted = null;
		}
	}

	private function parseClasses(?string $classString): ?SelectorInterface
	{
		if ($classString === null) {
			return null;
		}

		$classes = [];
		$classParts = explode('.', ltrim($classString, '.'));
		foreach ($classParts as $classPart) {
			if (strpos($classPart, '^') === 0) {
				$classPart = substr($classPart, 1);
				$classes[] = new QueryClass($classPart, QueryClass::STARTS_WITH);
			} else if (strpos($classPart, '$') === 0) {
				$classPart = substr($classPart, 1);
				$classes[] = new QueryClass($classPart, QueryClass::ENDS_WITH);
			} else if (strpos($classPart, '~') === 0) {
				$classPart = substr($classPart, 1);
				$classes[] = new QueryClass($classPart, QueryClass::CONTAINS);
			} else {
				$classes[] = new QueryClass($classPart, QueryClass::EXACT);
			}
		}
		if (count($classes) === 1) {
			return $classes[0];
		}

		return new LogicWrapper(LogicWrapper::AND , $classes);
	}

	private function parseAttributes(?string $attributeString): ?SelectorInterface
	{
		if ($attributeString === null) {
			return null;
		}
		$attributes = [];
		$attributeRegex = '/\[(?P<key>[\w-]+)(?P<operator>=|\~=|\^=|\$=)"(?P<value>(?:[^"]+(?:\\")?)+)"\]/U';
		$attributeMatches = [];
		preg_match_all($attributeRegex, $attributeString, $attributeMatches, PREG_SET_ORDER + PREG_UNMATCHED_AS_NULL);

		foreach ($attributeMatches as $attributeMatch) {
			$attrKey = $attributeMatch['key'];
			$attrValue = $attributeMatch['value'] ?? null;

			switch ($attributeMatch['operator']) {
				case '~=':
					$operator = QueryAttribute::CONTAINS;
					break;
				case '^=':
					$operator = QueryAttribute::STARTS_WITH;
					break;
				case '$=':
					$operator = QueryAttribute::ENDS_WITH;
					break;
				case '=':
				default:
					$operator = QueryAttribute::EXACT;
					break;
			}
			$attributes[] = new QueryAttribute($attrKey, $attrValue, $operator);
		}

		if (count($attributes) === 1) {
			return $attributes[0];
		}
		return new LogicWrapper(LogicWrapper::AND , $attributes);
	}

}
