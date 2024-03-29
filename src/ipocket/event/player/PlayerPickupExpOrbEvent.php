<?php

namespace ipocket\event\player;

use ipocket\event\Cancellable;
use ipocket\Player;

class PlayerPickupExpOrbEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	private $amount;

	public function __construct(Player $p, int $amount = 0){
		$this->player = $p;
		$this->amount = $amount;
	}

	public function getAmount() : int{
		return $this->amount;
	}

	public function setAmount(int $amount){
		$this->amount = $amount;
	}
}