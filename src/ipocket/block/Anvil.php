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

use ipocket\inventory\AnvilInventory;
use ipocket\item\Item;
use ipocket\item\Tool;
use ipocket\Player;

class Anvil extends Fallable{

	protected $id = self::ANVIL;

	public function isSolid(){
		return false;
	}

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getHardness() {
		return 5;
	}

	public function getResistance(){
		return 6000;
	}

	public function getName() : string{
		return "Anvil";
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function onActivate(Item $item, Player $player = null){
		if(!$this->getLevel()->getServer()->anviletEnabled) return true;
		if($player instanceof Player){
			if($player->isCreative()){
				return true;
			}

			$player->addWindow(new AnvilInventory($this));
		}

		return true;
	}

	public function getDrops(Item $item) : array {
		if($item->isPickaxe() >= 1){
			return [
				[$this->id, 0, 1], //TODO break level
			];
		}else{
			return [];
		}
	}
}