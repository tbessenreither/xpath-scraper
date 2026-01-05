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

	public function __construct(string $cssQuery)
	{
		$queryBuilderElements = self::parseCssToQueryElements($cssQuery);
		parent::__construct($queryBuilderElements);
	}

	private static function parseCssToQueryElements(string $css): array
	{
		$queryElements = [];
		$regexTag = '/(?P<outerElement>(?P<tag>[\w\*]*)(?P<classes>(?:\.[\^~$]?[\w\-]+)+)?(?P<attributes>(?:\[[^\]\[]+\])*))(?P<combinator>(?:\s+[>]\s+|,))?/';


		$elementMatches = [];
		preg_match_all($regexTag, $css, $elementMatches, PREG_SET_ORDER + PREG_UNMATCHED_AS_NULL);
		self::cleanupMatches($elementMatches);

		$combinatorStarted = null;
		$combinatorStorage = [];
		foreach ($elementMatches as $key => &$match) {
			if (empty($match['outerElement'])) {
				unset($elementMatches[$key]);
				continue;
			}

			$queryElement = self::parseMatch($match);

			if (
				(
					$combinatorStarted === null
					|| $combinatorStarted === 'or'
				) &&
				$match['combinator'] === ','
			) {
				if ($combinatorStarted === null) {
					$combinatorStarted = 'or';
					$combinatorStorage = [];
				}
				$combinatorStorage[] = $queryElement;
				continue;
			}

			if ($combinatorStarted === 'or' && $match['combinator'] !== ',') {
				//close OR wrapper
				$combinatorStorage[] = $queryElement;
				$queryElements[] = new LogicWrapper(LogicWrapper::OR , $combinatorStorage);
				$combinatorStorage = [];
				$combinatorStarted = null;
			} else {
				$queryElements[] = $queryElement;
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

	private static function cleanupMatches(array &$matches): void
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

	private static function parseMatch(array &$match): QueryElement
	{
		if (!isset($match['classes'])) {
			$match['classes'] = null;
		}
		if (!isset($match['attributes'])) {
			$match['attributes'] = null;
		}
		$match['classes'] = self::parseClasses($match['classes']);
		$match['attributes'] = self::parseAttributes($match['attributes']);


		return new QueryElement(
			$match['tag'] ?? '*',
			array_filter([
				$match['classes'],
				$match['attributes'],
			]),
		);
	}

	private static function parseClasses(?string $classString): ?SelectorInterface
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

	private static function parseAttributes(?string $attributeString): ?SelectorInterface
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
