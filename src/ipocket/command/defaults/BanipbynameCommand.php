<?php
namespace ipocket\command\defaults;

use ipocket\command\Command;
use ipocket\command\CommandSender;
use ipocket\event\TranslationContainer;
use ipocket\Player;
use ipocket\utils\TextFormat;

class BanipbynameCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%ipocket.command.banipbyname.description",
			"%commands.banipbyname.usage"
		);
		$this->setPermission("ipocket.command.banipbyname");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return \true;
		}

		if(\count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return \false;
		}

		$name = \array_shift($args);
		$reason = \implode(" ", $args);

		if ($sender->getServer()->getPlayer($name) instanceof Player) $target = $sender->getServer()->getPlayer($name);
		else return \false;

		$sender->getServer()->getIPBans()->addBan($target->getAddress(), $reason, \null, $sender->getName());

		if(($player = $sender->getServer()->getPlayerExact($name)) instanceof Player){
			$player->kick($reason !== "" ? "Banned by admin. Reason:" . $reason : "Banned by admin.");
		}

		Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.banipbyname.success", [$player !== \null ? $player->getName() : $name]));

		return \true;
	}
}
