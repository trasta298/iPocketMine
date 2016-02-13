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
 * @link http://ipocket.link/
 *
 *
*/

namespace ipocket\block;

use ipocket\item\Item;
use ipocket\item\Tool;

class IronDoor extends Door{

	protected $id = self::IRON_DOOR_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Iron Door Block";
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getHardness(){
		return 5;
	}

	public function getDrops(Item $item){
		if($item->isPickaxe() >= Tool::TIER_WOODEN){
			return [
				[Item::IRON_DOOR, 0, 1],
			];
		}else{
			return [];
		}
	}
}