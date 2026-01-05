<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\Dto;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\Dto\ExtractionDto;
use Tbessenreither\XPathScraper\Dto\ExtractionsDto;
use Tbessenreither\XPathScraper\Service\Scraper;

#[CoversClass(ExtractionsDto::class)]
#[UsesClass(ExtractionDto::class)]
#[UsesClass(Scraper::class)]


class ExtractionsDtoTest extends TestCase
{

	public function testGetAllAndElementAndFirst()
	{
		$dto1 = new ExtractionDto('foo', '<b>foo</b>', '<span><b>foo</b></span>', ['href' => '/bar', 'data-x' => 'baz']);
		$dto2 = new ExtractionDto('bar', '<b>bar</b>', '<span><b>bar</b></span>', ['href' => '/baz', 'data-x' => 'qux']);
		$extractions = new ExtractionsDto([$dto1, $dto2]);

		$this->assertSame(['foo', 'bar'], $extractions->getAll(Scraper::EXTRACT_TEXT));
		$this->assertSame(['<b>foo</b>', '<b>bar</b>'], $extractions->getAll(Scraper::EXTRACT_HTML));
		$this->assertSame(['<span><b>foo</b></span>', '<span><b>bar</b></span>'], $extractions->getAll(Scraper::EXTRACT_OUTER_HTML));
		$this->assertSame(['/bar', '/baz'], $extractions->getAll('href'));
		$this->assertSame(['baz', 'qux'], $extractions->getAll('data-x'));
		$this->assertSame('foo', $extractions->element(0)->getText());
		$this->assertSame('bar', $extractions->element(1)->getText());
		$this->assertSame('foo', $extractions->first()->getText());
		$this->assertCount(2, $extractions);
		$this->assertTrue(isset($extractions[0]));
		$this->assertFalse(isset($extractions[2]));
		$this->assertSame('bar', $extractions[1]->getText());
	}

	public function testImmutability()
	{
		$dto = new ExtractionDto('foo', null, null, []);
		$extractions = new ExtractionsDto([$dto]);
		$this->expectException(\LogicException::class);
		$extractions[0] = $dto;
	}

}
