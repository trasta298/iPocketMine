<?php
/**
 * Author: PeratX
 * Time: 2015/12/11 17:43
 ]

 */
namespace ipocket\block;

class StonePressurePlate extends PressurePlate{
	protected $id = self::STONE_PRESSURE_PLATE;

	public function getName() : string{
		return "Stone Pressure Plate";
	}
}