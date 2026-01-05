<?php declare(strict_types=1);

namespace Tbessenreither\XPathScraper\Enum;


enum PseudoclassOptions: string
{
	case NTH_CHILD = 'position';

	public function getByCssName(): string
	{
		return match ($this) {
			self::NTH_CHILD => 'nth-child',
		};
	}

}