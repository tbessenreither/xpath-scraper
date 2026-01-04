<?php

declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class XPathScraperBundle extends Bundle
{

	public function build(ContainerBuilder $container): void
	{
		parent::build($container);
	}

}
