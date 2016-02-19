<?php

/**
 * Author: PeratX
 * OpenGenisys Project
 */

namespace ipocket\command\defaults;

use ipocket\command\Command;
use ipocket\command\CommandSender;
use ipocket\event\TranslationContainer;

use ipocket\level\format\generic\BaseLevelProvider;
use ipocket\level\generator\Generator;
use ipocket\nbt\NBT;
use ipocket\nbt\tag\IntTag;
use ipocket\nbt\tag\StringTag;
use ipocket\nbt\tag\LongTag;
use ipocket\nbt\tag\CompoundTag;
use ipocket\math\Vector3;

class LvdatCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%ipocket.command.lvdat.description",
			"/lvdat <level-name> <opts|help>"
		);
		$this->setPermission("ipocket.command.lvdat");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}
		$levname = array_shift($args);
		if($levname == ""){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}
		if(!$this->autoLoad($sender, $levname)){
			$sender->sendMessage(new TranslationContainer("ipocket.command.lvdat.nofound", [$levname]));
			return false;
		}
		$level = $sender->getServer()->getLevelByName($levname);
		if(!$level){
			$sender->sendMessage(new TranslationContainer("ipocket.command.lvdat.nofound", [$levname]));
			return false;
		}
		/** @var BaseLevelProvider $provider */
		$provider = $level->getProvider();
		$o = array_shift($args);
		$p = array_shift($args);
		switch($o){
			case "fixname":
				$provider->getLevelData()->LevelName = new StringTag("LevelName", $level->getFolderName());
				$sender->sendMessage(new TranslationContainer("ipocket.command.lvdat.fixname", [$level->getFolderName()]));
				break;
			case "help":
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
				$sender->sendMessage("/lvdat %commands.generic.level fixname");
				$sender->sendMessage("/lvdat %commands.generic.level seed %commands.generic.seed");
				$sender->sendMessage("/lvdat %commands.generic.level name %commands.generic.name");
				$sender->sendMessage("/lvdat %commands.generic.level generator %commands.generic.generator");
				$sender->sendMessage("/lvdat %commands.generic.level preset %ipocket.command.lvdat.preset");
				break;
			case "seed":
				if($p == ""){
					$sender->sendMessage("%commands.generic.opt.missing");
					return false;
				}
				$provider->setSeed($p);
				$sender->sendMessage(new TranslationContainer("ipocket.command.lvdat.changed", [$level->getFolderName(), $o]));
				break;
			case "name":
				if($p == ""){
					$sender->sendMessage("%commands.generic.opt.missing");
					return false;
				}
				$provider->getLevelData()->LevelName = new StringTag("LevelName", $p);
				$sender->sendMessage(new TranslationContainer("ipocket.command.lvdat.changed", [$level->getFolderName(), $o]));
				break;
			case "generator":
				if($p == ""){
					$sender->sendMessage("%commands.generic.opt.missing");
					return false;
				}
				$provider->getLevelData()->generatorName = new StringTag("generatorName", $p);
				$sender->sendMessage(new TranslationContainer("ipocket.command.lvdat.changed", [$level->getFolderName(), $o]));
				break;
			case "preset":
				if($p == ""){
					$sender->sendMessage("%commands.generic.opt.missing");
					return false;
				}
				$provider->getLevelData()->generatorOptions = new StringTag("generatorOptions", $p);
				$sender->sendMessage(new TranslationContainer("ipocket.command.lvdat.changed", [$level->getFolderName(), $o]));
				break;
			default:
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
				return false;
		}
		$provider->saveLevelData();
		return true;
	}

	public function autoLoad(CommandSender $c, $world){
		if($c->getServer()->isLevelLoaded($world)) return true;
		if(!$c->getServer()->isLevelGenerated($world)){
			return false;
		}
		$c->getServer()->loadLevel($world);
		return $c->getServer()->isLevelLoaded($world);
	}
}
