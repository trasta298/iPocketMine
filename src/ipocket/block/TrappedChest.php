<?php
/**
 * Author: PeratX
 * Time: 2015/12/13 19:18
 ]

 */

namespace ipocket\block;

use ipocket\item\Item;
use ipocket\item\Tool;
use ipocket\math\AxisAlignedBB;
use ipocket\nbt\NBT;
use ipocket\nbt\tag\CompoundTag;
use ipocket\nbt\tag\EnumTag;
use ipocket\nbt\tag\IntTag;
use ipocket\nbt\tag\StringTag;
use ipocket\Player;
use ipocket\tile\Chest as TileChest;
use ipocket\tile\Tile;

class TrappedChest extends RedstoneSource{
	protected $id = self::TRAPPED_CHEST;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getBoundingBox(){
		if($this->boundingBox === null){
			$this->boundingBox = $this->recalculateBoundingBox();
		}
		return $this->boundingBox;
	}

	public function isSolid(){
		return true;
	}

	public function canBeFlowedInto(){
		return false;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getHardness() {
		return 2.5;
	}

	public function getResistance(){
		return $this->getHardness() * 5;
	}

	public function getName() : string{
		return "Trapped Chest";
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	protected function recalculateBoundingBox() {
		return new AxisAlignedBB(
				$this->x + 0.0625,
				$this->y,
				$this->z + 0.0625,
				$this->x + 0.9375,
				$this->y + 0.9475,
				$this->z + 0.9375
		);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$faces = [
				0 => 4,
				1 => 2,
				2 => 5,
				3 => 3,
		];

		$chest = null;
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];

		for($side = 2; $side <= 5; ++$side){
			if(($this->meta === 4 or $this->meta === 5) and ($side === 4 or $side === 5)){
				continue;
			}elseif(($this->meta === 3 or $this->meta === 2) and ($side === 2 or $side === 3)){
				continue;
			}
			$c = $this->getSide($side);
			if($c instanceof Chest and $c->getDamage() === $this->meta){
				$tile = $this->getLevel()->getTile($c);
				if($tile instanceof TileChest and !$tile->isPaired()){
					$chest = $tile;
					break;
				}
			}
		}

		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new CompoundTag("", [
				new EnumTag("Items", []),
				new StringTag("id", Tile::CHEST),
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

		$tile = Tile::createTile("Chest", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);

		if($chest instanceof TileChest and $tile instanceof TileChest){
			$chest->pairWith($tile);
			$tile->pairWith($chest);
		}

		return true;
	}

	public function onBreak(Item $item){
		$t = $this->getLevel()->getTile($this);
		if($t instanceof TileChest){
			$t->unpair();
		}
		$this->getLevel()->setBlock($this, new Air(), true, true);

		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			$top = $this->getSide(1);
			if($top->isTransparent() !== true){
				return true;
			}

			$t = $this->getLevel()->getTile($this);
			$chest = null;
			if($t instanceof TileChest){
				$chest = $t;
			}else{
				$nbt = new CompoundTag("", [
						new EnumTag("Items", []),
						new StringTag("id", Tile::CHEST),
						new IntTag("x", $this->x),
						new IntTag("y", $this->y),
						new IntTag("z", $this->z)
				]);
				$nbt->Items->setTagType(NBT::TAG_Compound);
				$chest = Tile::createTile("Chest", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
			}

			if(isset($chest->namedtag->Lock) and $chest->namedtag->Lock instanceof StringTag){
				if($chest->namedtag->Lock->getValue() !== $item->getCustomName()){
					return true;
				}
			}

			if($player->isCreative()){
				return true;
			}
			$player->addWindow($chest->getInventory());
		}

		return true;
	}

	public function getDrops(Item $item) : array {
		return [
				[$this->id, 0, 1],
		];
	}
}