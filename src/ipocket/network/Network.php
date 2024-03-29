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

/**
 * Network-related classes
 */
namespace ipocket\network;

use ipocket\network\protocol\AddEntityPacket;
use ipocket\network\protocol\AddItemEntityPacket;
use ipocket\network\protocol\AddPaintingPacket;
use ipocket\network\protocol\AddPlayerPacket;
use ipocket\network\protocol\AdventureSettingsPacket;
use ipocket\network\protocol\AnimatePacket;
use ipocket\network\protocol\BatchPacket;
use ipocket\network\protocol\ContainerClosePacket;
use ipocket\network\protocol\ContainerOpenPacket;
use ipocket\network\protocol\ContainerSetContentPacket;
use ipocket\network\protocol\ContainerSetDataPacket;
use ipocket\network\protocol\ContainerSetSlotPacket;
use ipocket\network\protocol\CraftingDataPacket;
use ipocket\network\protocol\CraftingEventPacket;
use ipocket\network\protocol\ChangeDimensionPacket;
use ipocket\network\protocol\DataPacket;
use ipocket\network\protocol\DropItemPacket;
use ipocket\network\protocol\FullChunkDataPacket;
use ipocket\network\protocol\Info;
use ipocket\network\protocol\SetEntityLinkPacket;
use ipocket\network\protocol\BlockEntityDataPacket;
use ipocket\network\protocol\EntityEventPacket;
use ipocket\network\protocol\ExplodePacket;
use ipocket\network\protocol\HurtArmorPacket;
use ipocket\network\protocol\Info as ProtocolInfo;
use ipocket\network\protocol\InteractPacket;
use ipocket\network\protocol\LevelEventPacket;
use ipocket\network\protocol\DisconnectPacket;
use ipocket\network\protocol\LoginPacket;
use ipocket\network\protocol\PlayStatusPacket;
use ipocket\network\protocol\TextPacket;
use ipocket\network\protocol\MoveEntityPacket;
use ipocket\network\protocol\MovePlayerPacket;
use ipocket\network\protocol\PlayerActionPacket;
use ipocket\network\protocol\MobArmorEquipmentPacket;
use ipocket\network\protocol\MobEquipmentPacket;
use ipocket\network\protocol\RemoveBlockPacket;
use ipocket\network\protocol\RemoveEntityPacket;
use ipocket\network\protocol\RemovePlayerPacket;
use ipocket\network\protocol\RespawnPacket;
use ipocket\network\protocol\SetDifficultyPacket;
use ipocket\network\protocol\SetEntityDataPacket;
use ipocket\network\protocol\SetEntityMotionPacket;
use ipocket\network\protocol\SetHealthPacket;
use ipocket\network\protocol\SetPlayerGameTypePacket;
use ipocket\network\protocol\SetSpawnPositionPacket;
use ipocket\network\protocol\SetTimePacket;
use ipocket\network\protocol\StartGamePacket;
use ipocket\network\protocol\TakeItemEntityPacket;
use ipocket\network\protocol\BlockEventPacket;
use ipocket\network\protocol\UpdateBlockPacket;
use ipocket\network\protocol\UseItemPacket;
use ipocket\network\protocol\PlayerListPacket;
use ipocket\network\protocol\PlayerInputPacket;
use ipocket\Player;
use ipocket\Server;
use ipocket\utils\Binary;
use ipocket\utils\MainLogger;

class Network {

	public static $BATCH_THRESHOLD = 512;

	/** @deprecated */
	const CHANNEL_NONE = 0;
	/** @deprecated */
	const CHANNEL_PRIORITY = 1; //Priority channel, only to be used when it matters
	/** @deprecated */
	const CHANNEL_WORLD_CHUNKS = 2; //Chunk sending
	/** @deprecated */
	const CHANNEL_MOVEMENT = 3; //Movement sending
	/** @deprecated */
	const CHANNEL_BLOCKS = 4; //Block updates or explosions
	/** @deprecated */
	const CHANNEL_WORLD_EVENTS = 5; //Entity, level or tile entity events
	/** @deprecated */
	const CHANNEL_ENTITY_SPAWNING = 6; //Entity spawn/despawn channel
	/** @deprecated */
	const CHANNEL_TEXT = 7; //Chat and other text stuff
	/** @deprecated */
	const CHANNEL_END = 31;

	/** @var \SplFixedArray */
	private $packetPool;

	/** @var Server */
	private $server;

	/** @var SourceInterface[] */
	private $interfaces = [];

	/** @var AdvancedSourceInterface[] */
	private $advancedInterfaces = [];

	private $upload = 0;
	private $download = 0;

	private $name;

	public function __construct(Server $server) {

		$this->registerPackets();

		$this->server = $server;
	}

	public function addStatistics($upload, $download) {
		$this->upload += $upload;
		$this->download += $download;
	}

	public function getUpload() {
		return $this->upload;
	}

	public function getDownload() {
		return $this->download;
	}

	public function resetStatistics() {
		$this->upload = 0;
		$this->download = 0;
	}

	/**
	 * @return SourceInterface[]
	 */
	public function getInterfaces() {
		return $this->interfaces;
	}

	public function processInterfaces() {
		foreach ($this->interfaces as $interface) {
			try {
				$interface->process();
			} catch (\Throwable $e) {
				$logger = $this->server->getLogger();
				if (\ipocket\DEBUG > 1) {
					if ($logger instanceof MainLogger) {
						$logger->logException($e);
					}
				}

				$interface->emergencyShutdown();
				$this->unregisterInterface($interface);
				$logger->critical($this->server->getLanguage()->translateString("ipocket.server.networkError", [get_class($interface), $e->getMessage()]));
			}
		}
	}

	/**
	 * @param SourceInterface $interface
	 */
	public function registerInterface(SourceInterface $interface) {
		$this->interfaces[$hash = spl_object_hash($interface)] = $interface;
		if ($interface instanceof AdvancedSourceInterface) {
			$this->advancedInterfaces[$hash] = $interface;
			$interface->setNetwork($this);
		}
		$interface->setName($this->name);
	}

	/**
	 * @param SourceInterface $interface
	 */
	public function unregisterInterface(SourceInterface $interface) {
		unset($this->interfaces[$hash = spl_object_hash($interface)],
			$this->advancedInterfaces[$hash]);
	}

	/**
	 * Sets the server name shown on each interface Query
	 *
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = (string)$name;
		foreach ($this->interfaces as $interface) {
			$interface->setName($this->name);
		}
	}

	public function getName() {
		return $this->name;
	}

	public function updateName() {
		foreach ($this->interfaces as $interface) {
			$interface->setName($this->name);
		}
	}

	/**
	 * @param int        $id 0-255
	 * @param DataPacket $class
	 */
	public function registerPacket($id, $class) {
		$this->packetPool[$id] = new $class;
	}

	public function getServer() {
		return $this->server;
	}

	public function processBatch(BatchPacket $packet, Player $p) {
		$str = zlib_decode($packet->payload, 1024 * 1024 * 64); //Max 64MB
		$len = strlen($str);
		$offset = 0;
		try {
			while ($offset < $len) {
				$pkLen = Binary::readInt(substr($str, $offset, 4));
				$offset += 4;

				$buf = substr($str, $offset, $pkLen);
				$offset += $pkLen;

				if (($pk = $this->getPacket(ord($buf{1}))) !== null) {
					if ($pk::NETWORK_ID === Info::BATCH_PACKET) {
						throw new \InvalidStateException("Invalid BatchPacket inside BatchPacket");
					}

					$pk->setBuffer($buf, 2);

					$pk->decode();
					$p->handleDataPacket($pk);

					if ($pk->getOffset() <= 0) {
						return;
					}
				}
			}
		} catch (\Throwable $e) {
			if (\ipocket\DEBUG > 1) {
				$logger = $this->server->getLogger();
				if ($logger instanceof MainLogger) {
					$logger->debug("BatchPacket " . " 0x" . bin2hex($packet->payload));
					$logger->logException($e);
				}
			}
		}
	}

	/**
	 * @param $id
	 *
	 * @return DataPacket
	 */
	public function getPacket($id) {
		/** @var DataPacket $class */
		$class = $this->packetPool[$id];
		if ($class !== null) {
			return clone $class;
		}
		return null;
	}


	/**
	 * @param string $address
	 * @param int    $port
	 * @param string $payload
	 */
	public function sendPacket($address, $port, $payload) {
		foreach ($this->advancedInterfaces as $interface) {
			$interface->sendRawPacket($address, $port, $payload);
		}
	}

	/**
	 * Blocks an IP address from the main interface. Setting timeout to -1 will block it forever
	 *
	 * @param string $address
	 * @param int    $timeout
	 */
	public function blockAddress($address, $timeout = 300) {
		foreach ($this->advancedInterfaces as $interface) {
			$interface->blockAddress($address, $timeout);
		}
	}

	private function registerPackets() {
		$this->packetPool = new \SplFixedArray(256);

		$this->registerPacket(ProtocolInfo::LOGIN_PACKET, LoginPacket::class);
		$this->registerPacket(ProtocolInfo::PLAY_STATUS_PACKET, PlayStatusPacket::class);
		$this->registerPacket(ProtocolInfo::DISCONNECT_PACKET, DisconnectPacket::class);
		$this->registerPacket(ProtocolInfo::BATCH_PACKET, BatchPacket::class);
		$this->registerPacket(ProtocolInfo::TEXT_PACKET, TextPacket::class);
		$this->registerPacket(ProtocolInfo::SET_TIME_PACKET, SetTimePacket::class);
		$this->registerPacket(ProtocolInfo::START_GAME_PACKET, StartGamePacket::class);
		$this->registerPacket(ProtocolInfo::ADD_PLAYER_PACKET, AddPlayerPacket::class);
		$this->registerPacket(ProtocolInfo::REMOVE_PLAYER_PACKET, RemovePlayerPacket::class);
		$this->registerPacket(ProtocolInfo::ADD_ENTITY_PACKET, AddEntityPacket::class);
		$this->registerPacket(ProtocolInfo::REMOVE_ENTITY_PACKET, RemoveEntityPacket::class);
		$this->registerPacket(ProtocolInfo::ADD_ITEM_ENTITY_PACKET, AddItemEntityPacket::class);
		$this->registerPacket(ProtocolInfo::TAKE_ITEM_ENTITY_PACKET, TakeItemEntityPacket::class);
		$this->registerPacket(ProtocolInfo::MOVE_ENTITY_PACKET, MoveEntityPacket::class);
		$this->registerPacket(ProtocolInfo::MOVE_PLAYER_PACKET, MovePlayerPacket::class);
		$this->registerPacket(ProtocolInfo::REMOVE_BLOCK_PACKET, RemoveBlockPacket::class);
		$this->registerPacket(ProtocolInfo::UPDATE_BLOCK_PACKET, UpdateBlockPacket::class);
		$this->registerPacket(ProtocolInfo::ADD_PAINTING_PACKET, AddPaintingPacket::class);
		$this->registerPacket(ProtocolInfo::EXPLODE_PACKET, ExplodePacket::class);
		$this->registerPacket(ProtocolInfo::LEVEL_EVENT_PACKET, LevelEventPacket::class);
		$this->registerPacket(ProtocolInfo::BLOCK_EVENT_PACKET, BlockEventPacket::class);
		$this->registerPacket(ProtocolInfo::ENTITY_EVENT_PACKET, EntityEventPacket::class);
		$this->registerPacket(ProtocolInfo::MOB_EQUIPMENT_PACKET, MobEquipmentPacket::class);
		$this->registerPacket(ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET, MobArmorEquipmentPacket::class);
		$this->registerPacket(ProtocolInfo::INTERACT_PACKET, InteractPacket::class);
		$this->registerPacket(ProtocolInfo::USE_ITEM_PACKET, UseItemPacket::class);
		$this->registerPacket(ProtocolInfo::PLAYER_ACTION_PACKET, PlayerActionPacket::class);
		$this->registerPacket(ProtocolInfo::HURT_ARMOR_PACKET, HurtArmorPacket::class);
		$this->registerPacket(ProtocolInfo::SET_ENTITY_DATA_PACKET, SetEntityDataPacket::class);
		$this->registerPacket(ProtocolInfo::SET_ENTITY_MOTION_PACKET, SetEntityMotionPacket::class);
		$this->registerPacket(ProtocolInfo::SET_ENTITY_LINK_PACKET, SetEntityLinkPacket::class);
		$this->registerPacket(ProtocolInfo::SET_HEALTH_PACKET, SetHealthPacket::class);
		$this->registerPacket(ProtocolInfo::SET_SPAWN_POSITION_PACKET, SetSpawnPositionPacket::class);
		$this->registerPacket(ProtocolInfo::ANIMATE_PACKET, AnimatePacket::class);
		$this->registerPacket(ProtocolInfo::RESPAWN_PACKET, RespawnPacket::class);
		$this->registerPacket(ProtocolInfo::DROP_ITEM_PACKET, DropItemPacket::class);
		$this->registerPacket(ProtocolInfo::CONTAINER_OPEN_PACKET, ContainerOpenPacket::class);
		$this->registerPacket(ProtocolInfo::CONTAINER_CLOSE_PACKET, ContainerClosePacket::class);
		$this->registerPacket(ProtocolInfo::CONTAINER_SET_SLOT_PACKET, ContainerSetSlotPacket::class);
		$this->registerPacket(ProtocolInfo::CONTAINER_SET_DATA_PACKET, ContainerSetDataPacket::class);
		$this->registerPacket(ProtocolInfo::CONTAINER_SET_CONTENT_PACKET, ContainerSetContentPacket::class);
		$this->registerPacket(ProtocolInfo::CRAFTING_DATA_PACKET, CraftingDataPacket::class);
		$this->registerPacket(ProtocolInfo::CRAFTING_EVENT_PACKET, CraftingEventPacket::class);
		$this->registerPacket(ProtocolInfo::ADVENTURE_SETTINGS_PACKET, AdventureSettingsPacket::class);
		$this->registerPacket(ProtocolInfo::BLOCK_ENTITY_DATA_PACKET, BlockEntityDataPacket::class);
		$this->registerPacket(ProtocolInfo::FULL_CHUNK_DATA_PACKET, FullChunkDataPacket::class);
		$this->registerPacket(ProtocolInfo::SET_DIFFICULTY_PACKET, SetDifficultyPacket::class);
		$this->registerPacket(ProtocolInfo::PLAYER_LIST_PACKET, PlayerListPacket::class);
		$this->registerPacket(ProtocolInfo::PLAYER_INPUT_PACKET, PlayerInputPacket::class);
		$this->registerPacket(ProtocolInfo::SET_PLAYER_GAMETYPE_PACKET, SetPlayerGameTypePacket::class);
		$this->registerPacket(ProtocolInfo::CHANGE_DIMENSION_PACKET, ChangeDimensionPacket::class);
	}
}
