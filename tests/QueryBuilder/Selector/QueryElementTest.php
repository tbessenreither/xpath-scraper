<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\QueryBuilder\Selector;

use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryAttribute;


class QueryElementTest extends TestCase
{

    public function testTagOnly()
    {
        $el = new QueryElement('div');
        $this->assertEquals('div', $el->getXPathSelector());
    }

    public function testTagWithClassLogic()
    {
        $el = new QueryElement('div', [
            new LogicWrapper(LogicWrapper::AND , [
                new QueryClass('foo', QueryClass::EXACT),
                new QueryClass('bar', QueryClass::CONTAINS),
            ])
        ]);
        $this->assertEquals("div[(contains(concat(' ', normalize-space(@class), ' '), ' foo ' ) and contains(@class, 'bar'))]", $el->getXPathSelector());
    }

    public function testTagWithAttributeLogic()
    {
        $el = new QueryElement('div', [], [
            new LogicWrapper(LogicWrapper::OR , [
                new QueryAttribute('data-x', 'foo', QueryAttribute::EXACT),
                new QueryAttribute('data-y', 'bar', QueryAttribute::CONTAINS),
            ])
        ]);
        $this->assertEquals("div[(@data-x='foo' or contains(@data-y, 'bar'))]", $el->getXPathSelector());
    }

    public function testTagWithBoth()
    {
        $el = new QueryElement('div', [
            new LogicWrapper(LogicWrapper::AND , [
                new QueryClass('foo', QueryClass::EXACT),
            ])
        ], [
            new LogicWrapper(LogicWrapper::AND , [
                new QueryAttribute('data-x', 'foo', QueryAttribute::EXACT),
            ])
        ]);
        $this->assertEquals("div[(contains(concat(' ', normalize-space(@class), ' '), ' foo ' ))][(@data-x='foo')]", $el->getXPathSelector());
    }

}
