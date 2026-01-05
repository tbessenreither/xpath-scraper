<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\QueryBuilder\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryAttribute;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;
use Tbessenreither\XPathScraper\QueryBuilder\Service\QueryBuilder;

#[CoversClass(QueryBuilder::class)]
#[UsesClass(QueryElement::class)]
#[UsesClass(LogicWrapper::class)]
#[UsesClass(QueryClass::class)]
#[UsesClass(QueryAttribute::class)]


class QueryBuilderTest extends TestCase
{

    public function testComplexQueryBuildsCorrectXPath()
    {
        $builder = new QueryBuilder([
            new QueryElement(
                'div',
                [
                    new LogicWrapper(LogicWrapper::OR , [
                        new QueryClass('outer', QueryClass::EXACT),
                        new QueryClass('container-', QueryClass::STARTS_WITH),
                    ])
                ]
            ),
            new LogicWrapper(LogicWrapper::OR , [
                new QueryElement(
                    'div',
                    [
                        new LogicWrapper(LogicWrapper::AND , [
                            new QueryAttribute('data-role', 'main', QueryAttribute::EXACT),
                            new QueryAttribute('data-active', 'true', QueryAttribute::EXACT),
                        ])
                    ]
                ),
                new QueryElement(
                    'span',
                    [
                        new LogicWrapper(LogicWrapper::AND , [
                            new QueryAttribute('data-role', 'main', QueryAttribute::EXACT),
                            new QueryAttribute('data-active', 'true', QueryAttribute::EXACT),
                        ])
                    ]
                ),
            ]),
            new QueryElement(
                'a',
                [
                    new LogicWrapper(LogicWrapper::AND , [
                        new QueryClass('link', QueryClass::CONTAINS),
                    ]),
                    new LogicWrapper(LogicWrapper::OR , [
                        new QueryAttribute('href', '/home', QueryAttribute::STARTS_WITH),
                        new LogicWrapper(LogicWrapper::AND , [
                            new QueryAttribute('href', '/about', QueryAttribute::STARTS_WITH),
                            new QueryAttribute('href', '/us', QueryAttribute::ENDS_WITH),
                        ]),
                    ])
                ]
            ),
        ]);

        $xpath = $builder->getXPathSelector();
        $expected = "//div[(contains(concat(' ', normalize-space(@class), ' '), ' outer ' ) or starts-with(normalize-space(@class), 'container-'))]"
            . "//div[(@data-role='main' and @data-active='true')] | span[(@data-role='main' and @data-active='true')]"
            . "//a[(contains(@class, 'link'))][(starts-with(@href, '/home') or (starts-with(@href, '/about') and substring(@href, string-length(@href) - string-length('/us') + 1) = '/us'))]";
        $this->assertEquals($expected, $xpath);

    }

}
