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

namespace ipocket\entity;


use ipocket\block\Block;

use ipocket\block\Liquid;
use ipocket\event\entity\EntityBlockChangeEvent;
use ipocket\event\entity\EntityDamageEvent;

use ipocket\item\Item as ItemItem;
use ipocket\math\Vector3;
use ipocket\nbt\tag\ByteTag;
use ipocket\nbt\tag\IntTag;
use ipocket\network\Network;
use ipocket\network\protocol\AddEntityPacket;
use ipocket\Player;

class FallingSand extends Entity{
	const NETWORK_ID = 66;

	const DATA_BLOCK_INFO = 20;

	public $width = 0.98;
	public $length = 0.98;
	public $height = 0.98;

	protected $gravity = 0.04;
	protected $drag = 0.02;
	protected $blockId = 0;
	protected $damage;

	public $canCollide = false;

	protected function initEntity(){
		parent::initEntity();
		if(isset($this->namedtag->TileID)){
			$this->blockId = $this->namedtag["TileID"];
		}elseif(isset($this->namedtag->Tile)){
			$this->blockId = $this->namedtag["Tile"];
			$this->namedtag["TileID"] = new IntTag("TileID", $this->blockId);
		}

		if(isset($this->namedtag->Data)){
			$this->damage = $this->namedtag["Data"];
		}

		if($this->blockId === 0){
			$this->close();
			return;
		}

		$this->setDataProperty(self::DATA_BLOCK_INFO, self::DATA_TYPE_INT, $this->getBlock() | ($this->getDamage() << 8));
	}

	public function canCollideWith(Entity $entity){
		return false;
	}

	public function attack($damage, EntityDamageEvent $source){
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($damage, $source);
		}
	}

	public function onUpdate($currentTick){

		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0 and !$this->justCreated){
			return true;
		}

		$this->lastUpdate = $currentTick;

		$hasUpdate = $this->entityBaseTick($tickDiff);

		if($this->isAlive()){
			$pos = (new Vector3($this->x - 0.5, $this->y, $this->z - 0.5))->round();

			if($this->ticksLived === 1){
				$block = $this->level->getBlock($pos);
				if($block->getId() !== $this->blockId){
					$this->kill();
					return true;
				}
				$this->level->setBlock($pos, Block::get(0), true);
			}

			$this->motionY -= $this->gravity;

			$this->move($this->motionX, $this->motionY, $this->motionZ);

			$friction = 1 - $this->drag;

			$this->motionX *= $friction;
			$this->motionY *= 1 - $this->drag;
			$this->motionZ *= $friction;

			$pos = (new Vector3($this->x - 0.5, $this->y, $this->z - 0.5))->floor();

			if($this->onGround){
				$this->kill();
				$block = $this->level->getBlock($pos);
				if($block->getId() > 0 and !$block->isSolid() and !($block instanceof Liquid)){
					$this->getLevel()->dropItem($this, ItemItem::get($this->getBlock(), $this->getDamage(), 1));
				}else{
					$this->server->getPluginManager()->callEvent($ev = new EntityBlockChangeEvent($this, $block, Block::get($this->getBlock(), $this->getDamage())));
					if(!$ev->isCancelled()){
					    if(($block->getId() !== ItemItem::CHEST) && ($block->getId() !== ItemItem::TRAPPED_CHEST)){
					        $this->getLevel()->setBlock($pos, $ev->getTo(), true);
					    } else{
					        $pos -> setComponents ($pos -> getX (), round ($pos -> getY () + 1), $pos -> getZ ());
					        $this->getLevel()->setBlock($pos, $ev->getTo(), true);
					        $this->kill();
					    }
					}
				}
				$hasUpdate = true;
			}

			$this->updateMovement();
		}

		return $hasUpdate or !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
	}

	public function getBlock(){
		return $this->blockId;
	}

	public function getDamage(){
		return $this->damage;
	}

	public function saveNBT(){
		$this->namedtag->TileID = new IntTag("TileID", $this->blockId);
		$this->namedtag->Data = new ByteTag("Data", $this->damage);
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = FallingSand::NETWORK_ID;
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}
