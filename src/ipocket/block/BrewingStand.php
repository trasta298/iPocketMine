<?php

namespace ipocket\block;

use ipocket\item\Item;
use ipocket\item\Tool;
use ipocket\nbt\tag\CompoundTag;
use ipocket\nbt\tag\IntTag;
use ipocket\nbt\tag\StringTag;
use ipocket\nbt\NBT;
use ipocket\nbt\tag\EnumTag;
use ipocket\Player;
use ipocket\tile\Tile;
use ipocket\tile\BrewingStand as TileBrewingStand;
use ipocket\math\Vector3;

class BrewingStand extends Transparent{

	protected $id = self::BREWING_STAND;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($block->getSide(Vector3::SIDE_DOWN)->isTransparent() === false){
			$this->getLevel()->setBlock($block, $this, true, true);
			$nbt = new CompoundTag("", [
				new EnumTag("Items", []),
				new StringTag("id", Tile::BREWING_STAND),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
			$nbt->Items->setTagType(NBT::TAG_Compound);
			if($item->hasCustomName()){
				$nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
			}

			if($item->hasCustomBlockData()){
				foreach($item->getCustomBlockData() as $key => $v){
					$nbt->{$key} = $v;
				}
			}

			Tile::createTile(Tile::BREWING_STAND, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);

			return true;
		}
		return false;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getHardness() {
		return 3;
	}

	public function getName() : string{
		return "Brewing Stand";
	}

	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			//TODO lock
			if($player->isCreative()){
				return true;
			}
			$t = $this->getLevel()->getTile($this);
			$brewingStand = false;
			if($t instanceof TileBrewingStand){
				$brewingStand = $t;
			}else{
				$nbt = new CompoundTag("", [
					new EnumTag("Items", []),
					new StringTag("id", Tile::BREWING_STAND),
					new IntTag("x", $this->x),
					new IntTag("y", $this->y),
					new IntTag("z", $this->z)
				]);
				$nbt->Items->setTagType(NBT::TAG_Compound);
				$brewingStand = Tile::createTile(Tile::BREWING_STAND, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
			}
			$player->addWindow($brewingStand->getInventory());
		}

		return true;
	}

	public function getDrops(Item $item) : array {
		$drops = [];
		if($item->isPickaxe() >= Tool::TIER_WOODEN){
			$drops[] = [Item::BREWING_STAND, 0, 1];
		}

		return $drops;
	}
}
