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

namespace ipocket\command\defaults;

use ipocket\command\Command;
use ipocket\command\CommandSender;
use ipocket\event\TranslationContainer;
use ipocket\Player;


class BanCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%ipocket.command.ban.player.description",
			"%commands.ban.usage"
		);
		$this->setPermission("ipocket.command.ban.player");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$name = array_shift($args);
		if(isset($args[0]) and isset($args[1])){
			$reason = $args[0];
			if($args[1] != null and is_numeric($args[1])){
				$until = $args[1] * 86400 + time();
			}else{
				$until = null;
			}

			$sender->getServer()->getNameBans()->addBan($name, $reason, $until, $sender->getName());
		}
		$sender->getServer()->getNameBans()->addBan($name, $reason = implode(" ", $args), null, $sender->getName());


		if(($player = $sender->getServer()->getPlayerExact($name)) instanceof Player){
			$player->kick($reason !== "" ? "Banned by admin. Reason: " . $reason : "Banned by admin." . "Banned Until:" . date('r', $until = "Forever"));
		}

		Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.ban.success", [$player !== null ? $player->getName() : $name]));

		return true;
	}
}