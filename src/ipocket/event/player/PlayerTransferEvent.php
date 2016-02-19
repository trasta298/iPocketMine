<?php

/*
 * FastTransfer plugin for iPocket-MP
 * Copyright (C) 2015 Shoghi Cervantes <https://github.com/shoghicp/FastTransfer>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

namespace ipocket\event\player;

use ipocket\event\Cancellable;
use ipocket\event\player\PlayerEvent;
use ipocket\Player;

class PlayerTransferEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	/** @var string */
	private $address;
	/** @var int */
	private $port;

	/**
	 * @param Player $player
	 * @param string $address
	 * @param int    $port
	 * @param string $message
	 */
	public function __construct(Player $player, $address, $port = 19132){
		$this->player = $player;
		$this->address = $address;
		$this->port = (int) $port;
	}

	/**
	 * @return int
	 */
	public function getPort(){
		return $this->port;
	}

	/**
	 * @return string
	 */
	public function getAddress(){
		return $this->address;
	}

	/**
	 * @param int $port
	 */
	public function setPort($port){
		$this->port = (int) $port;
	}

	/**
	 * @param string $address
	 */
	public function setAddress($address){
		$this->address = $address;
	}
}