<?php

declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\Dto;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\Dto\ExtractionDto;
use Tbessenreither\XPathScraper\Dto\ExtractionsDto;

#[CoversClass(ExtractionsDto::class)]
#[UsesClass(ExtractionDto::class)]


class ExtractionsDtoEdgeTest extends TestCase
{

	public function testGetIteratorReturnsTraversable()
	{
		$dto1 = new ExtractionDto('foo', null, null, []);
		$dto2 = new ExtractionDto('bar', null, null, []);
		$extractions = new ExtractionsDto([$dto1, $dto2]);
		$iterator = $extractions->getIterator();
		$this->assertInstanceOf(\Traversable::class, $iterator);
		$this->assertEquals([$dto1, $dto2], iterator_to_array($iterator));
	}

	public function testOffsetUnsetThrows()
	{
		$dto = new ExtractionDto('foo', null, null, []);
		$extractions = new ExtractionsDto([$dto]);
		$this->expectException(\LogicException::class);
		unset($extractions[0]);
	}

}
