<?php

declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\QueryBuilder\Interface;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tbessenreither\XPathScraper\QueryBuilder\Interface\SelectorInterface;

#[CoversClass(SelectorInterface::class)]


class SelectorInterfaceTest extends TestCase
{

	public function testSelectorInterfaceExists()
	{
		$this->assertTrue(interface_exists(SelectorInterface::class));
	}

}
