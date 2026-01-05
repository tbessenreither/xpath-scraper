<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\QueryBuilder\Selector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;

#[CoversClass(QueryClass::class)]


class QueryClassTest extends TestCase
{

    public function testExactMatch()
    {
        $q = new QueryClass('foo', QueryClass::EXACT);
        $this->assertEquals("contains(concat(' ', normalize-space(@class), ' '), ' foo ' )", $q->getXPathSelector());
    }

    public function testStartsWith()
    {
        $q = new QueryClass('foo', QueryClass::STARTS_WITH);
        $this->assertEquals("starts-with(normalize-space(@class), 'foo')", $q->getXPathSelector());
    }

    public function testEndsWith()
    {
        $q = new QueryClass('foo', QueryClass::ENDS_WITH);
        $this->assertEquals("substring(normalize-space(@class), string-length(normalize-space(@class)) - string-length('foo') + 1) = 'foo'", $q->getXPathSelector());
    }

    public function testContains()
    {
        $q = new QueryClass('foo', QueryClass::CONTAINS);
        $this->assertEquals("contains(@class, 'foo')", $q->getXPathSelector());
    }

}
