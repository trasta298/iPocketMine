<?php

/*
 *
 *  ____			_		_   __  __ _				  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___	  |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|	 |_|  |_|_|
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

namespace ipocket\item;

use ipocket\block\Block;
use ipocket\block\Fire;
use ipocket\block\Solid;
use ipocket\level\Level;
use ipocket\Player;
use ipocket\math\Vector3;

class FlintSteel extends Tool{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::FLINT_STEEL, $meta, $count, "Flint and Steel");
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if(($player->gamemode & 0x01) === 0 and $this->useOn($block) and $this->getDamage() >= $this->getMaxDurability()){
			$player->getInventory()->setItemInHand(new Item(Item::AIR, 0, 0));
		}

		if($target->getId() === 49 and $player->getServer()->netherEnabled){//黑曜石 4*5最小 23*23最大
			$level->setBlock($block, new Fire(), true);
			$tx = $target->getX();
			$ty = $target->getY();
			$tz = $target->getZ();
			//x方向
			$x_max = $tx;//x最大值
			$x_min = $tx;//x最小值
			$count_x = 0;//x方向方块
			for($x = $tx + 1;$level->getBlock(new Vector3($x, $ty, $tz))->getId() == 49;$x++){
				$x_max++;
			}
			for($x = $tx - 1;$level->getBlock(new Vector3($x, $ty, $tz))->getId() == 49;$x--){
				$x_min--;
			}
			$count_x = $x_max - $x_min + 1;
			if($count_x >= 4 and $count_x <= 23){//4 23
				$x_max_y = $ty;//x最大值时的y最大值
				$x_min_y = $ty;//x最小值时的y最大值
				for($y = $ty;$level->getBlock(new Vector3($x_max, $y, $tz))->getId() == 49;$y++){
					$x_max_y++;
				}
				for($y = $ty;$level->getBlock(new Vector3($x_min, $y, $tz))->getId() == 49;$y++){
					$x_min_y++;
				}
				$y_max = min($x_max_y, $x_min_y) - 1;//y最大值
				$count_y = $y_max - $ty + 2;//方向方块
				//Server::getInstance()->broadcastMessage("$y_max $x_max_y $x_min_y $x_max $x_min");
				if($count_y >= 5 and $count_y <= 23){//5 23
					$count_up = 0;//上面
					for($ux = $x_min;($level->getBlock(new Vector3($ux, $y_max, $tz))->getId() == 49 and $ux <= $x_max);$ux++){
						$count_up++;
					}
					//Server::getInstance()->broadcastMessage("$count_up $count_x");
					if($count_up == $count_x){
						for($px = $x_min + 1;$px < $x_max;$px++){
							for($py = $ty + 1;$py < $y_max;$py++){
								$level->setBlock(new Vector3($px, $py, $tz), new Block(90, 0));
							}
						}
					}
				}
			}

			//z方向
			$z_max = $tz;//z最大值
			$z_min = $tz;//z最小值
			$count_z = 0;//z方向方块
			for($z = $tz + 1;$level->getBlock(new Vector3($tx, $ty, $z))->getId() == 49;$z++){
				$z_max++;
			}
			for($z = $tz - 1;$level->getBlock(new Vector3($tx, $ty, $z))->getId() == 49;$z--){
				$z_min--;
			}
			$count_z = $z_max - $z_min + 1;
			if($count_z >= 4 and $count_z <= 23){//4 23
				$z_max_y = $ty;//z最大值时的y最大值
				$z_min_y = $ty;//z最小值时的y最大值
				for($y = $ty;$level->getBlock(new Vector3($tx, $y, $z_max))->getId() == 49;$y++){
					$z_max_y++;
				}
				for($y = $ty;$level->getBlock(new Vector3($tx, $y, $z_min))->getId() == 49;$y++){
					$z_min_y++;
				}
				$y_max = min($z_max_y, $z_min_y) - 1;//y最大值
				$count_y = $y_max - $ty + 2;//方向方块
				if($count_y >= 5 and $count_y <= 23){//5 23
					$count_up = 0;//上面
					for($uz = $z_min;($level->getBlock(new Vector3($tx, $y_max, $uz))->getId() == 49 and $uz <= $z_max);$uz++){
						$count_up++;
					}
					//Server::getInstance()->broadcastMessage("$count_up $count_z");
					if($count_up == $count_z){
						for($pz = $z_min + 1;$pz < $z_max;$pz++){
							for($py = $ty + 1;$py < $y_max;$py++){
								$level->setBlock(new Vector3($tx, $py, $pz), new Block(90, 0));
							}
						}
					}
				}
			}
			return true;
		}

		if($block->getId() === self::AIR and ($target instanceof Solid)){
			$level->setBlock($block, new Fire(), true);

			return true;
		}

		return false;
	}
}