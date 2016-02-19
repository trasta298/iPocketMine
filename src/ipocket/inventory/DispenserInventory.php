<?php

namespace ipocket\inventory;

use ipocket\tile\Dispenser;

class DispenserInventory extends ContainerInventory{
	public function __construct(Dispenser $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::DISPENSER));
	}

	/**
	 * @return Dispenser
	 */
	public function getHolder(){
		return $this->holder;
	}
}