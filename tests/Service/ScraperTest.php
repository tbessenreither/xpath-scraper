<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;
use Tbessenreither\XPathScraper\QueryBuilder\Service\QueryBuilder;
use Tbessenreither\XPathScraper\Service\Scraper;
use Tbessenreither\XPathScraper\Dto\ExtractionDto;
use Tbessenreither\XPathScraper\Dto\ExtractionsDto;
use Tbessenreither\XPathScraper\QueryBuilder\Service\CssQueryBuilder;

#[CoversClass(Scraper::class)]
#[UsesClass(QueryBuilder::class)]
#[UsesClass(QueryElement::class)]
#[UsesClass(LogicWrapper::class)]
#[UsesClass(QueryClass::class)]
#[UsesClass(ExtractionDto::class)]
#[UsesClass(ExtractionsDto::class)]
#[UsesClass(CssQueryBuilder::class)]


class ScraperTest extends TestCase
{
    protected string $html;

    protected function setUp(): void
    {
        $this->html = file_get_contents(__DIR__ . '/../fixtures/basic.html');
        $this->assertNotFalse($this->html, 'Failed to load HTML fixture');
    }

    public function testExtractLinksFromFooter(): void
    {
        $scraper = new Scraper($this->html);
        $mainContent = $scraper->get(new QueryBuilder([
            new QueryElement('div', [
                new LogicWrapper(LogicWrapper::OR , [
                    new QueryClass('outer', QueryClass::EXACT),
                    new QueryClass('container-', QueryClass::STARTS_WITH),
                ])
            ])
        ]));
        $footer = $mainContent->get(new QueryBuilder([
            new QueryElement('div', [
                new QueryClass('footer', QueryClass::EXACT),
            ])
        ]));
        $links = $footer->get(new QueryBuilder([
            new QueryElement('a', [
                new QueryClass('link', QueryClass::CONTAINS),
            ])
        ]));
        $extractions = $links->extract([
            Scraper::EXTRACT_ATTRIBUTE_PREFIX . 'href',
            Scraper::EXTRACT_TEXT,
        ]);
        $this->assertCount(3, $extractions);
        $this->assertEquals('/home', $extractions[0]->getAttribute('href'));
        $this->assertEquals('Home', $extractions[0]->getText());
        $this->assertEquals('/about', $extractions[1]->getAttribute('href'));
        $this->assertEquals('About Us', $extractions[1]->getText());
    }

    public function testExtractHtmlFromFooterLinks(): void
    {
        $scraper = new Scraper($this->html);
        $mainContent = $scraper->get(new QueryBuilder([
            new QueryElement('div', [
                new LogicWrapper(LogicWrapper::OR , [
                    new QueryClass('outer', QueryClass::EXACT),
                    new QueryClass('container-', QueryClass::STARTS_WITH),
                ])
            ])
        ]));
        $footer = $mainContent->get(new QueryBuilder([
            new QueryElement('div', [
                new QueryClass('footer', QueryClass::EXACT),
            ])
        ]));
        $links = $footer->get(new QueryBuilder([
            new QueryElement('a', [
                new QueryClass('link', QueryClass::CONTAINS),
            ])
        ]));
        $extractions = $links->extract([
            Scraper::EXTRACT_HTML,
            Scraper::EXTRACT_OUTER_HTML,
            Scraper::EXTRACT_ATTRIBUTE_PREFIX . 'data-foo',
        ]);
        $this->assertCount(3, $extractions);
        $this->assertEquals('Home', $extractions[0]->getHtml());
        $this->assertEquals('<a class="link" href="/home">Home</a>', $extractions[0]->getOuterHtml());
        $this->assertNull($extractions[0]->getAttribute('data-foo'));
        $this->assertEquals('About Us', $extractions[1]->getHtml());
        $this->assertEquals('<a class="link" href="/about">About Us</a>', $extractions[1]->getOuterHtml());
        $this->assertNull($extractions[1]->getAttribute('data-foo'));
    }

    public function testFooterTitleNodeIsFound(): void
    {
        $scraper = new Scraper($this->html);
        $result = $scraper->get(new QueryBuilder([
            new QueryElement(attributes: [
                new QueryClass('footer-title', QueryClass::EXACT),
            ]),
        ]));
        $this->assertGreaterThan(0, $result->nodeCount(), 'footer-title node should be found by QueryBuilder');
    }

    public function testParentSelector(): void
    {
        $scraper = new Scraper($this->html);

        $links = $scraper
            ->get(new QueryBuilder([
                new QueryElement(attributes: [
                    new QueryClass('footer-title', QueryClass::EXACT),
                ]),
            ]))
            ->parent(new QueryBuilder([
                new QueryElement('div', [
                    new QueryClass('outer', QueryClass::EXACT),
                ]),
            ]))
            ->get(new QueryBuilder([
                new QueryElement(attributes: [
                    new QueryClass('link', QueryClass::EXACT),
                ]),
            ]));

        $extractions = $links->extract([
            Scraper::EXTRACT_ATTRIBUTE_PREFIX . 'href',
            Scraper::EXTRACT_TEXT,
        ]);
        $this->assertCount(2, $extractions);
    }

    public function testLogicWrapperEdgeCase(): void
    {
        $scraper = new Scraper($this->html);
        $cssQuery = 'div.outer,div.^container- div.footer a.link';
        $queryBuilder = new CssQueryBuilder($cssQuery);
        $result = $scraper->get($queryBuilder);
        $this->assertGreaterThan(0, $result->nodeCount(), 'Node should be found by CssQueryBuilder with LogicWrapper edge case');

        $extractions = $result->extract([
            Scraper::EXTRACT_ATTRIBUTE_PREFIX . 'href',
            Scraper::EXTRACT_TEXT,
        ]);

        $this->assertCount(2, $extractions);
        $this->assertEquals('/home', $extractions[0]->getAttribute('href'));
        ;
        $this->assertEquals('Home', $extractions[0]->getText());
        $this->assertEquals('/about', $extractions[1]->getAttribute('href'));
        ;
        $this->assertEquals('About Us', $extractions[1]->getText());
    }

    public function testDirectChildSelector(): void
    {
        $scraper = new Scraper($this->html);
        $result = $scraper->get(new QueryBuilder([
            new QueryElement(
                tag: 'div',
                attributes: [
                    new QueryClass('outer', QueryClass::EXACT),
                ],
            ),
            new QueryElement(
                tag: 'div',
                attributes: [
                    new QueryClass('footer', QueryClass::EXACT),
                ],
                isDirectChild: true,
            ),
        ]));
        $this->assertGreaterThan(0, $result->nodeCount(), 'Node should be found by QueryBuilder with direct child selector');


        $scraper = new Scraper($this->html);
        $result = $scraper->get(new QueryBuilder([
            new QueryElement(
                tag: 'div',
                attributes: [
                    new QueryClass('outer', QueryClass::EXACT),
                ],
            ),
            new QueryElement(
                tag: 'a',
                attributes: [
                    new QueryClass('link', QueryClass::EXACT),
                ],
                isDirectChild: true,
            ),
        ]));
        $this->assertEquals(0, $result->nodeCount(), 'No node should be found by QueryBuilder with direct child selector in this case');
    }

}
