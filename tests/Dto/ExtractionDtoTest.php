<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\Dto;

use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\Dto\ExtractionDto;


class ExtractionDtoTest extends TestCase
{

	public function testGetters()
	{
		$dto = new ExtractionDto(
			text: 'foo',
			html: '<b>foo</b>',
			outerHtml: '<span><b>foo</b></span>',
			attributes: ['href' => '/bar', 'data-x' => 'baz']
		);
		$this->assertSame('foo', $dto->getText());
		$this->assertSame('<b>foo</b>', $dto->getHtml());
		$this->assertSame('<span><b>foo</b></span>', $dto->getOuterHtml());
		$this->assertSame('/bar', $dto->getAttribute('href'));
		$this->assertSame('baz', $dto->getAttribute('data-x'));
		$this->assertNull($dto->getAttribute('not-present'));
	}

}
