<?php

namespace ipocket\level\particle;

use ipocket\math\Vector3;
use ipocket\network\protocol\LevelEventPacket;

class SpellParticle extends GenericParticle{
	public function __construct(Vector3 $pos, $r = 0, $g = 0, $b = 0, $a = 255){
		parent::__construct($pos, LevelEventPacket::EVENT_PARTICLE_SPLASH, (($a & 0xff) << 24) | (($r & 0xff) << 16) | (($g & 0xff) << 8) | ($b & 0xff));
	}

	public function encode(){
		$pk = new LevelEventPacket();
		$pk->evid = LevelEventPacket::EVENT_PARTICLE_SPLASH;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->data = $this->data;
		return $pk;
	}
}