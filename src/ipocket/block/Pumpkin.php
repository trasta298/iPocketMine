<?php

namespace ipocket\block;

use ipocket\item\Item;
use ipocket\item\Tool;
use ipocket\Player;
use ipocket\entity\IronGolem;
use ipocket\entity\SnowGolem;
use ipocket\nbt\tag\CompoundTag;
use ipocket\nbt\tag\EnumTag;
use ipocket\nbt\tag\DoubleTag;
use ipocket\nbt\tag\FloatTag;

class Pumpkin extends Solid{

	protected $id = self::PUMPKIN;

	public function __construct(){

	}

	public function getHardness() {
		return 1;
	}

	public function isHelmet(){
		return true;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function getName() : string{
		return "Pumpkin";
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player instanceof Player){
			$this->meta = ((int) $player->getDirection() + 5) % 4;
		}
		$this->getLevel()->setBlock($block, $this, true, true);
		if($player != null) {
			$level = $this->getLevel();
			if($player->getServer()->allowSnowGolem) {
				$block0 = $level->getBlock($block->add(0,-1,0));
				$block1 = $level->getBlock($block->add(0,-2,0));
				if($block0->getId() == Item::SNOW_BLOCK and $block1->getId() == Item::SNOW_BLOCK) {
					$level->setBlock($block, new Air());
					$level->setBlock($block0, new Air());
					$level->setBlock($block1, new Air());
					$golem = new SnowGolem($player->getLevel()->getChunk($this->getX() >> 4, $this->getZ() >> 4), new CompoundTag("", [
						"Pos" => new EnumTag("Pos", [
							new DoubleTag("", $this->x),
							new DoubleTag("", $this->y),
							new DoubleTag("", $this->z)
						]),
						"Motion" => new EnumTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						"Rotation" => new EnumTag("Rotation", [
							new FloatTag("", 0),
							new FloatTag("", 0)
						]),
					]));
					$golem->spawnToAll();
				}
			}
			if($player->getServer()->allowIronGolem) {
				$block0 = $level->getBlock($block->add(0,-1,0));
				$block1 = $level->getBlock($block->add(0,-2,0));
				$block2 = $level->getBlock($block->add(-1,-1,0));
				$block3 = $level->getBlock($block->add(1,-1,0));
				$block4 = $level->getBlock($block->add(0,-1,-1));
				$block5 = $level->getBlock($block->add(0,-1,1));
				if($block0->getId() == Item::IRON_BLOCK and $block1->getId() == Item::IRON_BLOCK) {
					if($block2->getId() == Item::IRON_BLOCK and $block3->getId() == Item::IRON_BLOCK and $block4->getId() == Item::AIR and $block5->getId() == Item::AIR) {
						$level->setBlock($block2, new Air());
						$level->setBlock($block3, new Air());
					}elseif($block4->getId() == Item::IRON_BLOCK and $block5->getId() == Item::IRON_BLOCK and $block2->getId() == Item::AIR and $block3->getId() == Item::AIR){
						$level->setBlock($block4, new Air());
						$level->setBlock($block5, new Air());
					}else return;
					$level->setBlock($block, new Air());
					$level->setBlock($block0, new Air());
					$level->setBlock($block1, new Air());
					$golem = new IronGolem($player->getLevel()->getChunk($this->getX() >> 4, $this->getZ() >> 4), new CompoundTag("", [
						"Pos" => new EnumTag("Pos", [
							new DoubleTag("", $this->x),
							new DoubleTag("", $this->y),
							new DoubleTag("", $this->z)
						]),
						"Motion" => new EnumTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						"Rotation" => new EnumTag("Rotation", [
							new FloatTag("", 0),
							new FloatTag("", 0)
						]),
					]));
					$golem->spawnToAll();
				}
			}
		}

		return true;
	}

}
