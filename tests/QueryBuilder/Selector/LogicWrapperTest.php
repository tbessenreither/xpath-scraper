<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\QueryBuilder\Selector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryAttribute;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;

#[CoversClass(LogicWrapper::class)]
#[UsesClass(QueryClass::class)]
#[UsesClass(QueryAttribute::class)]


class LogicWrapperTest extends TestCase
{

    public function testAndLogicWithClasses()
    {
        $logic = new LogicWrapper(LogicWrapper::AND , [
            new QueryClass('foo', QueryClass::EXACT),
            new QueryClass('bar', QueryClass::CONTAINS),
        ]);
        $expected = "(contains(concat(' ', normalize-space(@class), ' '), ' foo ' ) and contains(@class, 'bar'))";
        $this->assertEquals($expected, $logic->getXPathSelector('class'));
    }

    public function testOrLogicWithAttributes()
    {
        $logic = new LogicWrapper(LogicWrapper::OR , [
            new QueryAttribute('data-x', '1', QueryAttribute::EXACT),
            new QueryAttribute('data-y', '2', QueryAttribute::CONTAINS),
        ]);
        $expected = "(@data-x='1' or contains(@data-y, '2'))";
        $this->assertEquals($expected, $logic->getXPathSelector('attribute'));
    }

    public function testNestedLogic()
    {
        $logic = new LogicWrapper(LogicWrapper::AND , [
            new QueryClass('foo', QueryClass::EXACT),
            new LogicWrapper(LogicWrapper::OR , [
                new QueryClass('bar', QueryClass::CONTAINS),
                new QueryClass('baz', QueryClass::STARTS_WITH),
            ]),
        ]);
        $expected = "(contains(concat(' ', normalize-space(@class), ' '), ' foo ' ) and (contains(@class, 'bar') or starts-with(normalize-space(@class), 'baz')))";
        $this->assertEquals($expected, $logic->getXPathSelector('class'));
    }

}
