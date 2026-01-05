<?php

declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\QueryBuilder\Selector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;

#[CoversClass(LogicWrapper::class)]
#[UsesClass(QueryElement::class)]


class LogicWrapperEdgeTest extends TestCase
{

	public function testElementLevelOrReturnsUnion()
	{
		$logic = new LogicWrapper(LogicWrapper::OR , [
			new QueryElement('div'),
			new QueryElement('span'),
		]);
		$expected = 'div | span';
		$this->assertEquals($expected, $logic->getXPathSelector());
	}

	public function testIsElementLevelOr()
	{
		$logic = new LogicWrapper(LogicWrapper::OR , [
			new QueryElement('a'),
			new QueryElement('b'),
		]);
		$this->assertTrue($logic->isElementLevelOr());
	}

	public function testGetChildren()
	{
		$children = [new QueryElement('a'), new QueryElement('b')];
		$logic = new LogicWrapper(LogicWrapper::OR , $children);
		$this->assertSame($children, $logic->getChildren());
	}

}
