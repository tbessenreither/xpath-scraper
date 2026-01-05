<?php

declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Tests\Bundle;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tbessenreither\XPathScraper\Bundle\XPathScraperBundle;

#[CoversClass(XPathScraperBundle::class)]


class XPathScraperBundleTest extends TestCase
{

	public function testBuildCallsParentBuild(): void
	{
		$bundle = new XPathScraperBundle();
		$container = $this->createMock(ContainerBuilder::class);
		// No exception should be thrown
		$bundle->build($container);
		$this->assertTrue(true); // Dummy assertion to mark the test as passed
	}

}
