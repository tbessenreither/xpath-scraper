<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\QueryBuilder\Selector;

use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryAttribute;


class QueryAttributeTest extends TestCase
{

    public function testExactMatch()
    {
        $q = new QueryAttribute('data-x', 'foo', QueryAttribute::EXACT);
        $this->assertEquals("@data-x='foo'", $q->getXPathSelector());
    }

    public function testStartsWith()
    {
        $q = new QueryAttribute('data-x', 'foo', QueryAttribute::STARTS_WITH);
        $this->assertEquals("starts-with(@data-x, 'foo')", $q->getXPathSelector());
    }

    public function testEndsWith()
    {
        $q = new QueryAttribute('data-x', 'foo', QueryAttribute::ENDS_WITH);
        $this->assertEquals("substring(@data-x, string-length(@data-x) - string-length('foo') + 1) = 'foo'", $q->getXPathSelector());
    }

    public function testContains()
    {
        $q = new QueryAttribute('data-x', 'foo', QueryAttribute::CONTAINS);
        $this->assertEquals("contains(@data-x, 'foo')", $q->getXPathSelector());
    }

}
