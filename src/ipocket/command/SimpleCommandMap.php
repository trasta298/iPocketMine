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

namespace ipocket\command;

use ipocket\command\defaults\BanCommand;
use ipocket\command\defaults\BanIpCommand;
use ipocket\command\defaults\BanListCommand;
use ipocket\command\defaults\BiomeCommand;
use ipocket\command\defaults\DefaultGamemodeCommand;
use ipocket\command\defaults\DeopCommand;
use ipocket\command\defaults\DifficultyCommand;
use ipocket\command\defaults\DumpMemoryCommand;
use ipocket\command\defaults\EffectCommand;
use ipocket\command\defaults\EnchantCommand;
use ipocket\command\defaults\GamemodeCommand;
use ipocket\command\defaults\GarbageCollectorCommand;
use ipocket\command\defaults\GiveCommand;
use ipocket\command\defaults\HelpCommand;
use ipocket\command\defaults\KickCommand;
use ipocket\command\defaults\KillCommand;
use ipocket\command\defaults\ListCommand;
use ipocket\command\defaults\LoadPluginCommand;
use ipocket\command\defaults\LvdatCommand;
use ipocket\command\defaults\MeCommand;
use ipocket\command\defaults\OpCommand;
use ipocket\command\defaults\PardonCommand;
use ipocket\command\defaults\PardonIpCommand;
use ipocket\command\defaults\ParticleCommand;
use ipocket\command\defaults\PluginsCommand;
use ipocket\command\defaults\ReloadCommand;
use ipocket\command\defaults\SaveCommand;
use ipocket\command\defaults\SaveOffCommand;
use ipocket\command\defaults\SaveOnCommand;
use ipocket\command\defaults\SayCommand;
use ipocket\command\defaults\SeedCommand;
use ipocket\command\defaults\SetBlockCommand;
use ipocket\command\defaults\SetWorldSpawnCommand;
use ipocket\command\defaults\SpawnpointCommand;
use ipocket\command\defaults\StatusCommand;
use ipocket\command\defaults\StopCommand;
use ipocket\command\defaults\SummonCommand;
use ipocket\command\defaults\TeleportCommand;
use ipocket\command\defaults\TellCommand;
use ipocket\command\defaults\TimeCommand;
use ipocket\command\defaults\TimingsCommand;
use ipocket\command\defaults\VanillaCommand;
use ipocket\command\defaults\VersionCommand;
use ipocket\command\defaults\WhitelistCommand;
use ipocket\command\defaults\XpCommand;
use ipocket\command\defaults\FillCommand;
use ipocket\event\TranslationContainer;
use ipocket\Server;
use ipocket\utils\MainLogger;
use ipocket\utils\TextFormat;

use ipocket\command\defaults\MakeServerCommand;
use ipocket\command\defaults\ExtractPluginCommand;
use ipocket\command\defaults\ExtractPharCommand;
use ipocket\command\defaults\MakePluginCommand;
use ipocket\command\defaults\BancidbynameCommand;
use ipocket\command\defaults\BanipbynameCommand;
use ipocket\command\defaults\BanCidCommand;
use ipocket\command\defaults\PardonCidCommand;
use ipocket\command\defaults\WeatherCommand;

class SimpleCommandMap implements CommandMap{

	/**
	 * @var Command[]
	 */
	protected $knownCommands = [];

	/** @var Server */
	private $server;

	public function __construct(Server $server){
		$this->server = $server;
		$this->setDefaultCommands();
	}

	private function setDefaultCommands(){
		$this->register("ipocket", new WeatherCommand("weather"));

		$this->register("ipocket", new BanCidCommand("bancid"));
		$this->register("ipocket", new PardonCidCommand("pardoncid"));
		$this->register("ipocket", new BancidbynameCommand("bancidbyname"));
		$this->register("ipocket", new BanipbynameCommand("banipbyname"));

		$this->register("ipocket", new ExtractPharCommand("extractphar"));
		$this->register("ipocket", new ExtractPluginCommand("extractplugin"));
		$this->register("ipocket", new MakePluginCommand("makeplugin"));
		$this->register("ipocket", new MakeServerCommand("ms"));
		//$this->register("ipocket", new MakeServerCommand("makeserver"));

		$this->register("ipocket", new LoadPluginCommand("loadplugin"));
		$this->register("ipocket", new LvdatCommand("lvdat"));

		$this->register("ipocket", new BiomeCommand("biome"));

		$this->register("ipocket", new VersionCommand("version"));
		$this->register("ipocket", new FillCommand("fill"));
		$this->register("ipocket", new PluginsCommand("plugins"));
		$this->register("ipocket", new SeedCommand("seed"));
		$this->register("ipocket", new HelpCommand("help"));
		$this->register("ipocket", new StopCommand("stop"));
		$this->register("ipocket", new TellCommand("tell"));
		$this->register("ipocket", new DefaultGamemodeCommand("defaultgamemode"));
		$this->register("ipocket", new BanCommand("ban"));
		$this->register("ipocket", new BanIpCommand("ban-ip"));
		$this->register("ipocket", new BanListCommand("banlist"));
		$this->register("ipocket", new PardonCommand("pardon"));
		$this->register("ipocket", new PardonIpCommand("pardon-ip"));
		$this->register("ipocket", new SayCommand("say"));
		$this->register("ipocket", new MeCommand("me"));
		$this->register("ipocket", new ListCommand("list"));
		$this->register("ipocket", new DifficultyCommand("difficulty"));
		$this->register("ipocket", new KickCommand("kick"));
		$this->register("ipocket", new OpCommand("op"));
		$this->register("ipocket", new DeopCommand("deop"));
		$this->register("ipocket", new WhitelistCommand("whitelist"));
		$this->register("ipocket", new SaveOnCommand("save-on"));
		$this->register("ipocket", new SaveOffCommand("save-off"));
		$this->register("ipocket", new SaveCommand("save-all"));
		$this->register("ipocket", new GiveCommand("give"));
		$this->register("ipocket", new EffectCommand("effect"));
		$this->register("ipocket", new EnchantCommand("enchant"));
		$this->register("ipocket", new ParticleCommand("particle"));
		$this->register("ipocket", new GamemodeCommand("gamemode"));
		$this->register("ipocket", new KillCommand("kill"));
		$this->register("ipocket", new SpawnpointCommand("spawnpoint"));
		$this->register("ipocket", new SetWorldSpawnCommand("setworldspawn"));
		$this->register("ipocket", new SummonCommand("summon"));
		$this->register("ipocket", new TeleportCommand("tp"));
		$this->register("ipocket", new TimeCommand("time"));
		$this->register("ipocket", new TimingsCommand("timings"));
		$this->register("ipocket", new ReloadCommand("reload"));
		$this->register("ipocket", new XpCommand("xp"));
		$this->register("ipocket", new SetBlockCommand("setblock"));

		if($this->server->getProperty("debug.commands", false)){
			$this->register("ipocket", new StatusCommand("status"));
			$this->register("ipocket", new GarbageCollectorCommand("gc"));
			$this->register("ipocket", new DumpMemoryCommand("dumpmemory"));
		}
	}


	public function registerAll($fallbackPrefix, array $commands){
		foreach($commands as $command){
			$this->register($fallbackPrefix, $command);
		}
	}

	public function register($fallbackPrefix, Command $command, $label = null){
		if($label === null){
			$label = $command->getName();
		}
		$label = strtolower(trim($label));
		$fallbackPrefix = strtolower(trim($fallbackPrefix));

		$registered = $this->registerAlias($command, false, $fallbackPrefix, $label);

		$aliases = $command->getAliases();
		foreach($aliases as $index => $alias){
			if(!$this->registerAlias($command, true, $fallbackPrefix, $alias)){
				unset($aliases[$index]);
			}
		}
		$command->setAliases($aliases);

		if(!$registered){
			$command->setLabel($fallbackPrefix . ":" . $label);
		}

		$command->register($this);

		return $registered;
	}

	private function registerAlias(Command $command, $isAlias, $fallbackPrefix, $label){
		$this->knownCommands[$fallbackPrefix . ":" . $label] = $command;
		if(($command instanceof VanillaCommand or $isAlias) and isset($this->knownCommands[$label])){
			return false;
		}

		if(isset($this->knownCommands[$label]) and $this->knownCommands[$label]->getLabel() !== null and $this->knownCommands[$label]->getLabel() === $label){
			return false;
		}

		if(!$isAlias){
			$command->setLabel($label);
		}

		$this->knownCommands[$label] = $command;

		return true;
	}

	public function dispatch(CommandSender $sender, $commandLine){
		$args = explode(" ", $commandLine);

		if(count($args) === 0){
			return false;
		}

		$sentCommandLabel = strtolower(array_shift($args));
		$target = $this->getCommand($sentCommandLabel);

		if($target === null){
			return false;
		}

		$target->timings->startTiming();
		try{
			$target->execute($sender, $sentCommandLabel, $args);
		}catch(\Throwable $e){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.exception"));
			$this->server->getLogger()->critical($this->server->getLanguage()->translateString("ipocket.command.exception", [$commandLine, (string) $target, $e->getMessage()]));
			$logger = $sender->getServer()->getLogger();
			if($logger instanceof MainLogger){
				$logger->logException($e);
			}
		}
		$target->timings->stopTiming();

		return true;
	}

	public function clearCommands(){
		foreach($this->knownCommands as $command){
			$command->unregister($this);
		}
		$this->knownCommands = [];
		$this->setDefaultCommands();
	}

	public function getCommand($name){
		if(isset($this->knownCommands[$name])){
			return $this->knownCommands[$name];
		}

		return null;
	}

	/**
	 * @return Command[]
	 */
	public function getCommands(){
		return $this->knownCommands;
	}


	/**
	 * @return void
	 */
	public function registerServerAliases(){
		$values = $this->server->getCommandAliases();

		foreach($values as $alias => $commandStrings){
			if(strpos($alias, ":") !== false or strpos($alias, " ") !== false){
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("ipocket.command.alias.illegal", [$alias]));
				continue;
			}

			$targets = [];

			$bad = "";
			foreach($commandStrings as $commandString){
				$args = explode(" ", $commandString);
				$command = $this->getCommand($args[0]);

				if($command === null){
					if(strlen($bad) > 0){
						$bad .= ", ";
					}
					$bad .= $commandString;
				}else{
					$targets[] = $commandString;
				}
			}

			if(strlen($bad) > 0){
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("ipocket.command.alias.notFound", [$alias, $bad]));
				continue;
			}

			//These registered commands have absolute priority
			if(count($targets) > 0){
				$this->knownCommands[strtolower($alias)] = new FormattedCommandAlias(strtolower($alias), $targets);
			}else{
				unset($this->knownCommands[strtolower($alias)]);
			}

		}
	}


}
