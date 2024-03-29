<?php
/**
 * Author: PeratX
 * Time: 2015/12/24 0:20
 ]

 */
namespace ipocket\block;

use ipocket\item\Item;
use ipocket\Player;
use ipocket\nbt\tag\CompoundTag;
use ipocket\nbt\tag\StringTag;
use ipocket\nbt\tag\IntTag;
use ipocket\tile\Tile;
use ipocket\tile\DLDetector;

class DaylightDetector extends RedstoneSource{
	protected $id = self::DAYLIGHT_SENSOR;
	//protected $hasStartedUpdate = false;

	public function getName() : string{
		return "Daylight Sensor";
	}

	public function getBoundingBox(){
		if($this->boundingBox === null){
			$this->boundingBox = $this->recalculateBoundingBox();
		}
		return $this->boundingBox;
	}

	public function canBeFlowedInto(){
		return false;
	}

	public function canBeActivated() : bool {
		return true;
	}

	/**
	 * @return DLDetector
	 */
	protected function getTile(){
		$t = $this->getLevel()->getTile($this);
		if($t instanceof DLDetector){
			return $t;
		}else{
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::DAY_LIGHT_DETECTOR),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
			return Tile::createTile(Tile::DAY_LIGHT_DETECTOR, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
		}
	}

	public function onActivate(Item $item, Player $player = null){
		$this->getLevel()->setBlock($this, new DaylightDetectorInverted(), true, true);
		$this->getTile()->onUpdate();
		return true;
	}

	public function isActivated(){
		return $this->getTile()->isActivated();
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air());
		if($this->isActivated()) $this->deactivate();
	}

	public function getDrops(Item $item) : array {
		return [
			[self::DAYLIGHT_SENSOR, 0, 1]
		];
	}
}