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
 * @link http://ipocket.link/
 *
 *
*/

namespace ipocket\command\defaults;

use ipocket\command\CommandSender;
use ipocket\event\TranslationContainer;
use ipocket\Server;


class BanListCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%ipocket.command.banlist.description",
			"%commands.banlist.usage"
		);
		$this->setPermission("ipocket.command.ban.list");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return \true;
		}
		$list = $sender->getServer()->getNameBans();
		if(isset($args[0])){
			$args[0] = \strtolower($args[0]);
			if($args[0] === "ips"){
				$list = $sender->getServer()->getIPBans();
			}elseif($args[0] === "players"){
				$list = $sender->getServer()->getNameBans();
			}elseif($args[0] === "cids") {
				$list = $sender->getServer()->getCIDBans();
			}else{
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

				return \false;
			}
		}

		$message = "";
		$list = $list->getEntries();
		foreach($list as $entry){
			$message .= $entry->getName() . ", ";
		}

		if(!isset($args[0])) return \false;
		if($args[0] === "ips"){
			$sender->sendMessage(Server::getInstance()->getLanguage()->translateString("commands.banlist.ips", [\count($list)]));
		}elseif($args[0] === "players"){
			$sender->sendMessage(Server::getInstance()->getLanguage()->translateString("commands.banlist.players", [\count($list)]));
		}else $sender->sendMessage("共有 ".\count($list)."被ban");

		$sender->sendMessage(\substr($message, 0, -2));

		return \true;
	}
}