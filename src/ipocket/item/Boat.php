<?php

namespace ipocket\item;

use ipocket\level\Level;
use ipocket\block\Block;
use ipocket\nbt\tag\IntTag;
use ipocket\Player;
use ipocket\nbt\tag\CompoundTag;
use ipocket\nbt\tag\EnumTag;
use ipocket\nbt\tag\DoubleTag;
use ipocket\nbt\tag\FloatTag;
use ipocket\entity\Boat as BoatEntity;

class Boat extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::BOAT, $meta, $count, "Boat");
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$realPos = $block->getSide($face);

		$boat = new BoatEntity($player->getLevel()->getChunk($realPos->getX() >> 4, $realPos->getZ() >> 4), new CompoundTag("", [
			"Pos" => new EnumTag("Pos", [
				new DoubleTag("", $realPos->getX() + 0.5),
				new DoubleTag("", $realPos->getY()),
				new DoubleTag("", $realPos->getZ() + 0.5)
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
			"WoodID" => new IntTag("WoodID", $this->getDamage())
		]));
		$boat->spawnToAll();

		if($player->isSurvival()) {
			$item = $player->getInventory()->getItemInHand();
			$count = $item->getCount();
			if(--$count <= 0){
				$player->getInventory()->setItemInHand(Item::get(Item::AIR));
				return true;
			}

			$item->setCount($count);
			$player->getInventory()->setItemInHand($item);
		}

		return true;
	}
}
