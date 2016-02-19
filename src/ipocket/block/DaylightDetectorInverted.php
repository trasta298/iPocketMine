<?php
/**
 * Author: PeratX
 * Time: 2015/12/24 17:06
 ]

 *
 * OpenGenisys Project
 */
namespace ipocket\block;

use ipocket\item\Item;
use ipocket\Player;

class DaylightDetectorInverted extends DaylightDetector{
	protected $id = self::DAYLIGHT_SENSOR_INVERTED;

	public function onActivate(Item $item, Player $player = null){
		$this->getLevel()->setBlock($this, new DaylightDetector(), true, true);
		$this->getTile()->onUpdate();
		return true;
	}
}