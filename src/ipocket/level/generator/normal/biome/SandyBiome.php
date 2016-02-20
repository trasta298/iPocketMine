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

namespace ipocket\level\generator\normal\biome;

use ipocket\block\Sapling;
use ipocket\block\Block;
use ipocket\level\generator\populator\Cactus;
use ipocket\level\generator\populator\TallCacti;
use ipocket\level\generator\populator\DeadBush;

class SandyBiome extends GrassyBiome{

	public function __construct(){
		parent::__construct();

		$cactus = new Cactus();
		$cactus->setBaseAmount(2);
		$tallCacti = new TallCacti();
		$tallCacti->setBaseAmount(60);
		$deadBush = new DeadBush();
		$deadBush->setBaseAmount(2);

		$this->addPopulator($cactus);
		$this->addPopulator($tallCacti);
		$this->addPopulator($deadBush);

		$this->setElevation(63, 81);

		$this->temperature = 0.05;
		$this->rainfall = 0.8;
                $this->setGroundCover([
			Block::get(Block::SAND, 0),
			Block::get(Block::SAND, 0),
			Block::get(Block::SAND, 0),
			Block::get(Block::SANDSTONE, 0),
			Block::get(Block::SANDSTONE, 0),
		]);
        }

	public function getName() : string{
		return "Sandy";
	}
}
