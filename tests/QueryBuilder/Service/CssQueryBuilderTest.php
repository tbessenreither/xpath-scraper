<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\CssQueryBuilder\Service;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\Enum\PseudoclassOptions;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryAttribute;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryPseudoclass;
use Tbessenreither\XPathScraper\QueryBuilder\Service\CssQueryBuilder;
use Tbessenreither\XPathScraper\QueryBuilder\Service\QueryBuilder;

#[CoversClass(CssQueryBuilder::class)]
#[UsesClass(QueryBuilder::class)]
#[UsesClass(QueryElement::class)]
#[UsesClass(LogicWrapper::class)]
#[UsesClass(QueryClass::class)]
#[UsesClass(QueryAttribute::class)]
#[UsesClass(QueryPseudoclass::class)]


class CssQueryBuilderTest extends TestCase
{

    #[DataProvider('provideTestQueries')]

    public function testGivenQueries(string $query, string $expectedXPath)
    {
        $builder = new CssQueryBuilder($query);
        $xpath = $builder->getXPathSelector();
        $this->assertEquals($expectedXPath, $xpath);
    }

    public static function provideTestQueries(): Generator
    {
        yield [
            'div',
            (new QueryBuilder([
                new QueryElement(
                    'div'
                ),
            ]))->getXPathSelector(),
        ];

        yield [
            'div a',
            (new QueryBuilder([
                new QueryElement(
                    'div'
                ),
                new QueryElement(
                    'a'
                ),
            ]))->getXPathSelector(),
        ];

        yield [
            'div.outer',
            (new QueryBuilder([
                new QueryElement(
                    'div',
                    [
                        new QueryClass('outer', QueryClass::EXACT),
                    ]
                ),
            ]))->getXPathSelector(),
        ];

        yield [
            'a[href="test"]',
            (new QueryBuilder([
                new QueryElement(
                    'a',
                    [
                        new QueryAttribute('href', 'test', QueryAttribute::EXACT),
                    ]
                ),
            ]))->getXPathSelector(),
        ];

        $query1Variations = [
            'div.outer, div.^container- (div[data-role="main"][data-active="true"], span[data-role="main"][data-active="true"]) a.link[href^="/home"], a.link[href^="/about"][href$="/us"]',
            'div.outer,div.^container- (div[data-role="main"][data-active="true"],span[data-role="main"][data-active="true"]) a.link[href^="/home"],a.link[href^="/about"][href$="/us"]',
        ];

        foreach ($query1Variations as $query) {
            yield [
                $query,
                (new QueryBuilder([
                    new LogicWrapper(LogicWrapper::OR , [
                        new QueryElement(
                            'div',
                            [
                                new QueryClass('outer', QueryClass::EXACT),
                            ]
                        ),
                        new QueryElement(
                            'div',
                            [
                                new QueryClass('container-', QueryClass::STARTS_WITH),
                            ]
                        ),
                    ]),
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
                    new LogicWrapper(LogicWrapper::OR , [
                        new QueryElement(
                            'a',
                            [
                                new QueryClass('link', QueryClass::EXACT),
                                new QueryAttribute('href', '/home', QueryAttribute::STARTS_WITH),
                            ]
                        ),
                        new QueryElement(
                            'a',
                            [
                                new QueryClass('link', QueryClass::EXACT),
                                new LogicWrapper(LogicWrapper::AND , [
                                    new QueryAttribute('href', '/about', QueryAttribute::STARTS_WITH),
                                    new QueryAttribute('href', '/us', QueryAttribute::ENDS_WITH),
                                ]),
                            ]
                        ),
                    ]),
                ]))->getXPathSelector()
            ];
        }

        yield [
            'a.button[href$="/submit"][data-action="save"], a.button[href$="/cancel"][data-action="abort"]',
            (new QueryBuilder([
                new LogicWrapper(LogicWrapper::OR , [
                    new QueryElement(
                        tag: 'a',
                        attributes: [
                            new QueryClass('button', QueryClass::EXACT),
                            new LogicWrapper(LogicWrapper::AND , [
                                new QueryAttribute('href', '/submit', QueryAttribute::ENDS_WITH),
                                new QueryAttribute('data-action', 'save', QueryAttribute::EXACT),
                            ]),
                        ]
                    ),
                    new QueryElement(
                        tag: 'a',
                        attributes: [
                            new QueryClass('button', QueryClass::EXACT),
                            new LogicWrapper(LogicWrapper::AND , [
                                new QueryAttribute('href', '/cancel', QueryAttribute::ENDS_WITH),
                                new QueryAttribute('data-action', 'abort', QueryAttribute::EXACT),
                            ]),
                        ]
                    ),
                ]),
            ]))->getXPathSelector(),
        ];

        yield [
            'div.outer,div.test div.footer a.link',
            (new QueryBuilder([
                new LogicWrapper(LogicWrapper::OR , [
                    new QueryElement(
                        'div',
                        [
                            new QueryClass('outer', QueryClass::EXACT),
                        ]
                    ),
                    new QueryElement(
                        'div',
                        [
                            new QueryClass('test', QueryClass::EXACT),
                        ]
                    ),
                ]),
                new QueryElement(
                    'div',
                    [
                        new QueryClass('footer', QueryClass::EXACT),
                    ]
                ),
                new QueryElement(
                    'a',
                    [
                        new QueryClass('link', QueryClass::EXACT),
                    ]
                ),
            ]))->getXPathSelector(),
        ];

        yield [
            'div > div > a',
            (new QueryBuilder([
                new QueryElement(
                    tag: 'div',
                ),
                new QueryElement(
                    tag: 'div',
                    isDirectChild: true,
                ),
                new QueryElement(
                    tag: 'a',
                    isDirectChild: true,
                ),
            ]))->getXPathSelector(),
        ];

        yield [
            'div > div > a > span',
            (new QueryBuilder([
                new QueryElement(
                    tag: 'div',
                ),
                new QueryElement(
                    tag: 'div',
                    isDirectChild: true,
                ),
                new QueryElement(
                    tag: 'a',
                    isDirectChild: true,
                ),
                new QueryElement(
                    tag: 'span',
                    isDirectChild: true,
                ),
            ]))->getXPathSelector(),
        ];

        yield [
            'div div a > span',
            (new QueryBuilder([
                new QueryElement(
                    tag: 'div',
                ),
                new QueryElement(
                    tag: 'div',
                ),
                new QueryElement(
                    tag: 'a',
                ),
                new QueryElement(
                    tag: 'span',
                    isDirectChild: true,
                ),
            ]))->getXPathSelector(),
        ];

        yield [
            'ul li:nth-child(3) a.link',
            (new QueryBuilder([
                new QueryElement(
                    tag: 'ul',
                ),
                new QueryElement(
                    tag: 'li',
                    attributes: [
                        new QueryPseudoclass(PseudoclassOptions::NTH_CHILD, '3'),
                    ],
                ),
                new QueryElement(
                    tag: 'a',
                    attributes: [
                        new QueryClass('link', QueryClass::EXACT),
                    ],
                ),
            ]))->getXPathSelector(),
        ];
    }

}
