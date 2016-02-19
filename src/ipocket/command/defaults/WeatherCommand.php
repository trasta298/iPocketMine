<?php
namespace ipocket\command\defaults;

use ipocket\command\Command;
use ipocket\command\CommandSender;
use ipocket\event\TranslationContainer;
use ipocket\level\Level;
use ipocket\Player;
use ipocket\utils\TextFormat;
use ipocket\level\weather\WeatherManager;

class WeatherCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%ipocket.command.weather.description",
			"%ipocket.command.weather.usage"
		);
		$this->setPermission("ipocket.command.weather");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		if($sender instanceof Player){
			$wea = (int)$args[0];
			if($wea >= 0 and $wea <= 3){
				if(WeatherManager::isRegistered($sender->getLevel())){
					$sender->getLevel()->getWeather()->setWeather($wea);
					$sender->sendMessage(new TranslationContainer("ipocket.command.weather.changed", [$sender->getLevel()->getFolderName()]));
					return true;
				}else{
					$sender->sendMessage(new TranslationContainer("ipocket.command.weather.noregistered", [$sender->getLevel()->getFolderName()]));
					return false;
				}
			}else{
				$sender->sendMessage(TextFormat::RED . "%ipocket.command.weather.invalid");
				return false;
			}
		}

		if(count($args) < 2){
			$sender->sendMessage(TextFormat::RED . "%ipocket.command.weather.wrong");
			return false;
		}

		$level = $sender->getServer()->getLevelByName($args[0]);
		if(!$level instanceof Level){
			$sender->sendMessage(TextFormat::RED . "%ipocket.command.weather.invalid.level");
			return false;
		}

		$wea = (int)$args[1];
		if($wea >= 0 and $wea <= 3){
			if(WeatherManager::isRegistered($level)){
				$level->getWeather()->setWeather($wea);
				$sender->sendMessage(new TranslationContainer("ipocket.command.weather.changed", [$level->getFolderName()]));
				return true;
			}else{
				$sender->sendMessage(new TranslationContainer("ipocket.command.weather.noregistered", [$level->getFolderName()]));
				return false;
			}
		}else{
			$sender->sendMessage(TextFormat::RED . "%ipocket.command.weather.invalid");
			return false;
		}

		return true;
	}
}
