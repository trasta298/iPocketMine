<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 14:44
 ]

 */

namespace ipocket\item;

class Beetroot extends Item {
	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::BEETROOT, $meta, $count, "Beetroot");
	}

}