<?php

namespace ipocket\block;

use ipocket\item\Item;
use ipocket\item\Tool;
use ipocket\nbt\tag\CompoundTag;
use ipocket\nbt\tag\IntTag;
use ipocket\nbt\tag\StringTag;
use ipocket\Player;
use ipocket\tile\Tile;
use ipocket\math\AxisAlignedBB;
use ipocket\nbt\tag\ByteTag;
use ipocket\tile\Skull;

class SkullBlock extends Transparent{

	protected $id = self::SKULL_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() {
		return 1;
	}

	public function isHelmet(){
		return true;
	}

	public function isSolid(){
		return false;
	}

	public function getBoundingBox(){
		return new AxisAlignedBB(
			$this->x - 0.75,
			$this->y - 0.5,
			$this->z - 0.75,
			$this->x + 0.75,
			$this->y + 0.5,
			$this->z + 0.75
		);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($face !== 0 && $fy > 0.5 && $target->getId() !== self::SKULL_BLOCK && !$down instanceof SkullBlock){
			$this->getLevel()->setBlock($block, Block::get(Block::SKULL_BLOCK, 0), true, true);
			if($face === 1){
				$rot = new ByteTag("Rot", floor(($player->yaw * 16 / 360) + 0.5) & 0x0F);
			}else{
				$rot = new ByteTag("Rot", 0);
			}
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::SKULL),
				new IntTag("x", $block->x),
				new IntTag("y", $block->y),
				new IntTag("z", $block->z),
				new ByteTag("SkullType", $item->getDamage()),
				$rot
			]);

			$chunk = $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4);
			$pot = Tile::createTile("Skull", $chunk, $nbt);
			$this->getLevel()->setBlock($block, Block::get(Block::SKULL_BLOCK, $face), true, true);
			return true;
		}
		return false;
	}

	public function getResistance(){
		return 5;
	}

	public function getName() : string{
		static $names = [
			0 => "Skeleton Skull",
			1 => "Wither Skeleton Skull",
			2 => "Zombie Head",
			3 => "Head",
			4 => "Creeper Head"
		];
		return $names[$this->meta & 0x04];
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, true);
		return true;
	}

	public function getDrops(Item $item) : array {
		/** @var Skull $tile */
		if(($tile = $this->getLevel()->getTile($this)) instanceof Skull){
			return [[Item::SKULL, $tile->getSkullType(), 1]];
		}else
			return [[Item::SKULL, 0, 1]];
	}
}
