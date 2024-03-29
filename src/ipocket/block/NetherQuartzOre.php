<?php

namespace ipocket\block;

use ipocket\item\Item;
use ipocket\item\Tool;

class NetherQuartzOre extends Solid{
	protected $id = self::NETHER_QUARTZ_ORE;

	public function __construct(){

	}

	public function getName() : string{
		return "Nether Quartz Ore";
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getDrops(Item $item) : array {
		if($item->isPickaxe() >= Tool::TIER_WOODEN){
			return [
				[Item::NETHER_QUARTZ, 0, 1],
			];
		}else{
			return [];
		}
	}
}