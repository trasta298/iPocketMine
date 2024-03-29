<?php

namespace ipocket\level\sound;

use ipocket\math\Vector3;
use ipocket\network\protocol\LevelEventPacket;

class ButtonClickSound extends GenericSound{
	public function __construct(Vector3 $pos){
		parent::__construct($pos, LevelEventPacket::EVENT_SOUND_BUTTON_CLICK);
	}
}