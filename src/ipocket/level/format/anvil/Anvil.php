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

namespace ipocket\level\format\anvil;

use ipocket\level\format\FullChunk;
use ipocket\level\format\mcregion\McRegion;
use ipocket\level\Level;
use ipocket\nbt\NBT;
use ipocket\nbt\tag\ByteTag;
use ipocket\nbt\tag\ByteArrayTag;
use ipocket\nbt\tag\CompoundTag;
use ipocket\network\protocol\FullChunkDataPacket;
use ipocket\tile\Spawnable;
use ipocket\utils\BinaryStream;
use ipocket\utils\ChunkException;


class Anvil extends McRegion{

	/** @var RegionLoader[] */
	protected $regions = [];

	/** @var Chunk[] */
	protected $chunks = [];

	public static function getProviderName(){
		return "anvil";
	}

	public static function getProviderOrder(){
		return self::ORDER_YZX;
	}

	public static function usesChunkSection(){
		return true;
	}

	public static function isValid($path){
		$isValid = (file_exists($path . "/level.dat") and is_dir($path . "/region/"));

		if($isValid){
			$files = glob($path . "/region/*.mc*");
			foreach($files as $f){
				if(strpos($f, ".mcr") !== false){ //McRegion
					$isValid = false;
					break;
				}
			}
		}

		return $isValid;
	}

	public function requestChunkTask($x, $z){
		$chunk = $this->getChunk($x, $z, false);
		if(!($chunk instanceof Chunk)){
			throw new ChunkException("Invalid Chunk sent");
		}

		if($this->getServer()->asyncChunkRequest){
			$task = new ChunkRequestTask($this->getLevel(), $chunk);
			$this->getServer()->getScheduler()->scheduleAsyncTask($task);
		}else{
			$tiles = "";

			if(count($chunk->getTiles()) > 0){
				$nbt = new NBT(NBT::LITTLE_ENDIAN);
				$list = [];
				foreach($chunk->getTiles() as $tile){
					if($tile instanceof Spawnable){
						$list[] = $tile->getSpawnCompound();
					}
				}
				$nbt->setData($list);
				$tiles = $nbt->write();
			}

			$extraData = new BinaryStream();
			$extraData->putLInt(count($chunk->getBlockExtraDataArray()));
			foreach($chunk->getBlockExtraDataArray() as $key => $value){
				$extraData->putLInt($key);
				$extraData->putLShort($value);
			}

			$ordered = $chunk->getBlockIdArray() .
				$chunk->getBlockDataArray() .
				$chunk->getBlockSkyLightArray() .
				$chunk->getBlockLightArray() .
				pack("C*", ...$chunk->getHeightMapArray()) .
				pack("N*", ...$chunk->getBiomeColorArray()) .
				$extraData->getBuffer() .
				$tiles;

			$this->getLevel()->chunkRequestCallback($x, $z, $ordered, FullChunkDataPacket::ORDER_LAYERED);
		}

		return null;
	}

	/**
	 * @param $x
	 * @param $z
	 *
	 * @return RegionLoader
	 */
	protected function getRegion($x, $z){
		return isset($this->regions[$index = Level::chunkHash($x, $z)]) ? $this->regions[$index] : null;
	}

	/**
	 * @param int  $chunkX
	 * @param int  $chunkZ
	 * @param bool $create
	 *
	 * @return Chunk
	 */
	public function getChunk($chunkX, $chunkZ, $create = false){
		return parent::getChunk($chunkX, $chunkZ, $create);
	}

	public function setChunk($chunkX, $chunkZ, FullChunk $chunk){
		if(!($chunk instanceof Chunk)){
			throw new ChunkException("Invalid Chunk class");
		}

		$chunk->setProvider($this);

		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ);

		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->chunks[Level::chunkHash($chunkX, $chunkZ)] = $chunk;
	}

	public function getEmptyChunk($chunkX, $chunkZ){
		return Chunk::getEmptyChunk($chunkX, $chunkZ, $this);
	}

	public static function createChunkSection($Y){
		return new ChunkSection(new CompoundTag("", [
			"Y" => new ByteTag("Y", $Y),
			"Blocks" => new ByteArrayTag("Blocks", str_repeat("\x00", 4096)),
			"Data" => new ByteArrayTag("Data", str_repeat("\x00", 2048)),
			"SkyLight" => new ByteArrayTag("SkyLight", str_repeat("\xff", 2048)),
			"BlockLight" => new ByteArrayTag("BlockLight", str_repeat("\x00", 2048))
		]));
	}

	public function isChunkGenerated($chunkX, $chunkZ){
		if(($region = $this->getRegion($chunkX >> 5, $chunkZ >> 5)) !== null){
			return $region->chunkExists($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32) and $this->getChunk($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32, true)->isGenerated();
		}

		return false;
	}

	protected function loadRegion($x, $z){
		if(isset($this->regions[$index = Level::chunkHash($x, $z)])){
			return true;
		}

		$this->regions[$index] = new RegionLoader($this, $x, $z);

		return true;
	}
}