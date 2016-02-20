<?php

namespace ipocket\plugin;

use ipocket\event\plugin\PluginDisableEvent;
use ipocket\event\plugin\PluginEnableEvent;
use ipocket\plugin\Plugin;
use ipocket\plugin\PluginBase;
use ipocket\plugin\PluginDescription;
use ipocket\plugin\PluginLoader;
use ipocket\Server;
use ipocket\utils\MainLogger;
use ipocket\utils\TextFormat;

class FolderPluginLoader implements PluginLoader{

	/** @var Server */
	private $server;

	/**
	 * @param Server $server
	 */
	public function __construct(Server $server){
		$this->server = $server;
	}

	/**
	 * Loads the plugin contained in $file
	 *
	 * @param string $file
	 *
	 * @return Plugin
	 */
	public function loadPlugin($file){
		if(is_dir($file) and file_exists($file . "/plugin.yml") and file_exists($file . "/src/")){
			if(($description = $this->getPluginDescription($file)) instanceof PluginDescription){
				MainLogger::getLogger()->info(TextFormat::LIGHT_PURPLE . "読込中... " . $description->getFullName());
				$dataFolder = dirname($file) . DIRECTORY_SEPARATOR . $description->getName();
				if(file_exists($dataFolder) and !is_dir($dataFolder)){
					trigger_error("プロジェクトのデータディレクトリ '" . $dataFolder . "' へ " . $description->getName() . " 読み込みができませんでした。", E_USER_WARNING);

					return null;
				}


				$className = $description->getMain();
				$this->server->getLoader()->addPath($file . "/src");

				if(class_exists($className, true)){
					$plugin = new $className();
					$this->initPlugin($plugin, $description, $dataFolder, $file);

					return $plugin;
				}else{
					trigger_error("ソースウィジェットをロードできませんでした。 " . $description->getName() . "： マスタークラスが見つかりません。", E_USER_WARNING);

					return null;
				}
			}
		}

		return null;
	}

	/**
	 * Gets the PluginDescription from the file
	 *
	 * @param string $file
	 *
	 * @return PluginDescription
	 */
	public function getPluginDescription($file){
		if(is_dir($file) and file_exists($file . "/plugin.yml")){
			$yaml = @file_get_contents($file . "/plugin.yml");
			if($yaml != ""){
				return new PluginDescription($yaml);
			}
		}

		return null;
	}

	/**
	 * Returns the filename patterns that this loader accepts
	 *
	 * @return array
	 */
	public function getPluginFilters() : string{
		return "/[^\\.]/";
	}

	/**
	 * @param PluginBase        $plugin
	 * @param PluginDescription $description
	 * @param string            $dataFolder
	 * @param string            $file
	 */
	private function initPlugin(PluginBase $plugin, PluginDescription $description, $dataFolder, $file){
		$plugin->init($this, $this->server, $description, $dataFolder, $file);
		$plugin->onLoad();
	}

	/**
	 * @param Plugin $plugin
	 */
	public function enablePlugin(Plugin $plugin){
		if($plugin instanceof PluginBase and !$plugin->isEnabled()){
			MainLogger::getLogger()->info(TextFormat::LIGHT_PURPLE."有効化中... " . $plugin->getDescription()->getFullName());

			$plugin->setEnabled(true);

			Server::getInstance()->getPluginManager()->callEvent(new PluginEnableEvent($plugin));
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function disablePlugin(Plugin $plugin){
		if($plugin instanceof PluginBase and $plugin->isEnabled()){
			MainLogger::getLogger()->info("無効化中... " . $plugin->getDescription()->getFullName());

			Server::getInstance()->getPluginManager()->callEvent(new PluginDisableEvent($plugin));

			$plugin->setEnabled(false);
		}
	}
}
