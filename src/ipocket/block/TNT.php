<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iPocket Team
 * @link http://www.ipocket.net/
 *
 *
*/

namespace ipocket\block;

use ipocket\entity\Entity;
use ipocket\item\Item;
use ipocket\nbt\tag\ByteTag;
use ipocket\nbt\tag\CompoundTag;
use ipocket\nbt\tag\DoubleTag;
use ipocket\nbt\tag\EnumTag;
use ipocket\nbt\tag\FloatTag;
use ipocket\Player;
use ipocket\utils\Random;

class TNT extends Solid{

	protected $id = self::TNT;

	public function __construct(){

	}

	public function getName() : string{
		return "TNT";
	}

	public function getHardness() {
		return 0;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::FLINT_STEEL){
			$item->useOn($this);
			$this->getLevel()->setBlock($this, new Air(), true);

			$mot = (new Random())->nextSignedFloat() * M_PI * 2;
			$tnt = Entity::createEntity("PrimedTNT", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), new CompoundTag("", [
				"Pos" => new EnumTag("Pos", [
					new DoubleTag("", $this->x + 0.5),
					new DoubleTag("", $this->y),
					new DoubleTag("", $this->z + 0.5)
				]),
				"Motion" => new EnumTag("Motion", [
					new DoubleTag("", -sin($mot) * 0.02),
					new DoubleTag("", 0.2),
					new DoubleTag("", -cos($mot) * 0.02)
				]),
				"Rotation" => new EnumTag("Rotation", [
					new FloatTag("", 0),
					new FloatTag("", 0)
				]),
				"Fuse" => new ByteTag("Fuse", 80)
			]));

			$tnt->spawnToAll();

			return true;
		}

		return false;
	}
}