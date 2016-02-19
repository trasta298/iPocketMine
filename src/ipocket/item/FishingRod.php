<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 14:28
 ]

 */
namespace ipocket\item;

use ipocket\block\Block;

class FishingRod extends Item {
	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::FISHING_ROD, 0, $count, "Fishing Rod");
	}
}
