<?php
namespace ipocket\event\player;

use ipocket\event\Cancellable;
use ipocket\Player;

class PlayerHungerChangeEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	public $data;

	public function __construct(Player $player, $data){
		$this->data = $data;
	}

	public function getData(){
		return $this->data;
	}

	public function setData($data){
		$this->data = $data;
	}

}
