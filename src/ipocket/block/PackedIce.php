<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 14:26
 ]

 */

namespace ipocket\block;

use ipocket\item\Item;
use ipocket\item\Tool;

class PackedIce extends Solid {

	protected $id = self::PACKED_ICE;

	public function __construct() {

	}

	public function getName() : string{
		return "Packed Ice";
	}

	public function getHardness() {
		return 0.5;
	}

	public function getToolType() {
		return Tool::TYPE_PICKAXE;
	}

}
