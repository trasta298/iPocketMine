<?php

namespace ipocket\tile;

use ipocket\block\Block;
use ipocket\level\format\FullChunk;
use ipocket\nbt\tag\CompoundTag;
use ipocket\nbt\tag\IntTag;
use ipocket\nbt\tag\ShortTag;
use ipocket\nbt\tag\StringTag;

class FlowerPot extends Spawnable{

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->item)){
			$nbt->item = new ShortTag("item", 0);
		}
		if(!isset($nbt->data)){
			$nbt->data = new IntTag("data", 0);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getFlowerPotItem(){
		return $this->namedtag["item"];
	}

	public function getFlowerPotData(){
		return $this->namedtag["data"];
	}

	public function setFlowerPotData($item, $data){
		$this->namedtag->item = new ShortTag("item", (int) $item);
		$this->namedtag->data = new IntTag("data", (int) $data);
		$this->spawnToAll();
		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
			$block = $this->level->getBlock($this);
			if($block->getId() === Block::FLOWER_POT_BLOCK){
				$this->level->setBlock($this, Block::get(Block::FLOWER_POT_BLOCK, $data), true, true);
			}
		}
		return true;
	}

	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", Tile::FLOWER_POT),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			new ShortTag("item", (int) $this->namedtag["item"]),
			new IntTag("data", (int) $this->namedtag["data"])
		]);
	}
}