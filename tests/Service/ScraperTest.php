<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\Service\Scraper;
use Tbessenreither\XPathScraper\QueryBuilder\Service\QueryBuilder;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;


class ScraperTest extends TestCase
{

    public function testExtractLinksFromFooter()
    {
        $html = file_get_contents(__DIR__ . '/../fixtures/basic.html');
        $this->assertNotFalse($html, 'Failed to load HTML fixture');
        $scraper = new Scraper($html);
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
        $this->assertCount(2, $extractions);
        $this->assertEquals('/home', $extractions[0]->getAttribute('href'));
        $this->assertEquals('Home', $extractions[0]->getText());
        $this->assertEquals('/about', $extractions[1]->getAttribute('href'));
        $this->assertEquals('About Us', $extractions[1]->getText());
    }

    public function testExtractHtmlFromFooterLinks()
    {
        $html = file_get_contents(__DIR__ . '/../fixtures/basic.html');
        $this->assertNotFalse($html, 'Failed to load HTML fixture');
        $scraper = new Scraper($html);
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
        $this->assertCount(2, $extractions);
        $this->assertEquals('Home', $extractions[0]->getHtml());
        $this->assertEquals('<a class="link" href="/home">Home</a>', $extractions[0]->getOuterHtml());
        $this->assertNull($extractions[0]->getAttribute('data-foo'));
        $this->assertEquals('About Us', $extractions[1]->getHtml());
        $this->assertEquals('<a class="link" href="/about">About Us</a>', $extractions[1]->getOuterHtml());
        $this->assertNull($extractions[1]->getAttribute('data-foo'));
    }

}
