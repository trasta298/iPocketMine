<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/2/3 15:44


 *
 * OpenGenisys Project
 */
namespace ipocket\tile;

use ipocket\block\Block;
use ipocket\inventory\DropperInventory;
use ipocket\inventory\InventoryHolder;
use ipocket\item\Item;
use ipocket\level\format\FullChunk;
use ipocket\level\particle\SmokeParticle;
use ipocket\math\Vector3;
use ipocket\nbt\NBT;
use ipocket\nbt\tag\DoubleTag;
use ipocket\nbt\tag\FloatTag;
use ipocket\nbt\tag\ShortTag;
use ipocket\entity\Item as ItemEntity;

use ipocket\nbt\tag\CompoundTag;
use ipocket\nbt\tag\EnumTag;
use ipocket\nbt\tag\IntTag;

use ipocket\nbt\tag\StringTag;

class Dropper extends Spawnable implements InventoryHolder, Container, Nameable{

	/** @var DropperInventory */
	protected $inventory;

	protected $nextUpdate = 0;

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		parent::__construct($chunk, $nbt);
		$this->inventory = new DropperInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof EnumTag)){
			$this->namedtag->Items = new EnumTag("Items", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i));
		}

		$this->scheduleUpdate();
	}

	public function close(){
		if($this->closed === false){
			foreach($this->getInventory()->getViewers() as $player){
				$player->removeWindow($this->getInventory());
			}

			foreach($this->getInventory()->getViewers() as $player){
				$player->removeWindow($this->getInventory());
			}
			parent::close();
		}
	}

	public function saveNBT(){
		$this->namedtag->Items = new EnumTag("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	/**
	 * @return int
	 */
	public function getSize(){
		return 9;
	}

	/**
	 * @param $index
	 *
	 * @return int
	 */
	protected function getSlotIndex($index){
		foreach($this->namedtag->Items as $i => $slot){
			if((int) $slot["Slot"] === (int) $index){
				return (int) $i;
			}
		}

		return -1;
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index
	 *
	 * @return Item
	 */
	public function getItem($index){
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return Item::get(Item::AIR, 0, 0);
		}else{
			return NBT::getItemHelper($this->namedtag->Items[$i]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int  $index
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItem($index, Item $item){
		$i = $this->getSlotIndex($index);

		$d = NBT::putItemHelper($item, $index);

		if($item->getId() === Item::AIR or $item->getCount() <= 0){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($this->namedtag->Items[$i])){
					break;
				}
			}
			$this->namedtag->Items[$i] = $d;
		}else{
			$this->namedtag->Items[$i] = $d;
		}

		return true;
	}

	/**
	 * @return DropperInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	public function getName() : string{
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Dropper";
	}

	public function hasName(){
		return isset($this->namedtag->CustomName);
	}

	public function setName($str){
		if($str === ""){
			unset($this->namedtag->CustomName);
			return;
		}

		$this->namedtag->CustomName = new StringTag("CustomName", $str);
	}

	public function getMotion(){
		$meta = $this->getBlock()->getDamage();
		switch($meta){
			case Vector3::SIDE_DOWN:
				return [0, -1, 0];
			case Vector3::SIDE_UP:
				return [0, 1, 0];
			case Vector3::SIDE_NORTH:
				return [0, 0, -1];
			case Vector3::SIDE_SOUTH:
				return [0, 0, 1];
			case Vector3::SIDE_WEST:
				return [-1, 0, 0];
			case Vector3::SIDE_EAST:
				return [1, 0, 0];
			default:
				return [0, 0, 0];
		}
	}

	public function activate(){
		$itemIndex = [];
		for($i = 0; $i < $this->getSize(); $i++){
			$item = $this->getInventory()->getItem($i);
			if($item->getId() != Item::AIR){
				$itemIndex[] = [$i, $item];
			}
		}
		$max = count($itemIndex) - 1;
		if($max < 0) $itemArr = null;
		elseif($max == 0) $itemArr = $itemIndex[0];
		else $itemArr = $itemIndex[mt_rand(0, $max)];

		if(is_array($itemArr)){
			/** @var Item $item */
			$item = $itemArr[1];
			$item->setCount($item->getCount() - 1);
			$this->getInventory()->setItem($itemArr[0], $item->getCount() > 0 ? $item : Item::get(Item::AIR));
			$motion = $this->getMotion();
			$needItem = Item::get($item->getId(), $item->getDamage());
			$block = $this->getLevel()->getBlock($this->add($motion[0], $motion[1], $motion[2]));
			switch($block->getId()){
				case Block::CHEST:
				case Block::TRAPPED_CHEST:
				case Block::DROPPER:
				case Block::DISPENSER:
				case Block::BREWING_STAND:
				case Block::FURNACE:
					$t = $this->getLevel()->getTile($block);
					/** @var Chest|Dispenser|Dropper|BrewingStand|Furnace $t */
					if($t instanceof Tile){
						if($t->getInventory()->canAddItem($needItem)){
							$t->getInventory()->addItem($needItem);
							return;
						}
					}
			}

			$itemTag = NBT::putItemHelper($needItem);
			$itemTag->setName("Item");


			$nbt = new CompoundTag("", [
				"Pos" => new EnumTag("Pos", [
					new DoubleTag("", $this->x + $motion[0] * 2 + 0.5),
					new DoubleTag("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
					new DoubleTag("", $this->z + $motion[2] * 2 + 0.5)
				]),
				"Motion" => new EnumTag("Motion", [
					new DoubleTag("", $motion[0]),
					new DoubleTag("", $motion[1]),
					new DoubleTag("", $motion[2])
				]),
				"Rotation" => new EnumTag("Rotation", [
					new FloatTag("", lcg_value() * 360),
					new FloatTag("", 0)
				]),
				"Health" => new ShortTag("Health", 5),
				"Item" => $itemTag,
				"PickupDelay" => new ShortTag("PickupDelay", 10)
			]);

			$f = 0.3;
			$itemEntity = new ItemEntity($this->chunk, $nbt, $this);
			$itemEntity->setMotion($itemEntity->getMotion()->multiply($f));
			$itemEntity->spawnToAll();

			for($i = 1; $i < 10; $i++){
				$this->getLevel()->addParticle(new SmokeParticle($this->add($motion[0] * $i * 0.3 + 0.5, $motion[1] == 0 ? 0.5 : $motion[1] * $i * 0.3, $motion[2] * $i * 0.3 + 0.5)));
			}
		}
	}

	public function getSpawnCompound(){
		$c = new CompoundTag("", [
			new StringTag("id", Tile::DROPPER),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z)
		]);

		if($this->hasName()){
			$c->CustomName = $this->namedtag->CustomName;
		}

		return $c;
	}
}