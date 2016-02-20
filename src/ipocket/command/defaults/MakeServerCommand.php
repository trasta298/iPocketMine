<?php
namespace ipocket\command\defaults;

use ipocket\command\CommandSender;
use ipocket\plugin\Plugin;
use ipocket\Server;
use ipocket\utils\TextFormat;
use ipocket\network\protocol\Info;

class MakeServerCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Creates a iPocket Phar",
			"/makeserver"
		);
		$this->setPermission("ipocket.command.makeserver");
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}

		$server = $sender->getServer();
		$pharPath = Server::getInstance()->getPluginPath().DIRECTORY_SEPARATOR . "iPocket" . DIRECTORY_SEPARATOR . $server->getName()."_".$server->getiPocketVersion().".phar";
		if(file_exists($pharPath)){
			$sender->sendMessage("Phar file already exists, overwriting...");
			@unlink($pharPath);
		}
		$phar = new \Phar($pharPath);
		$phar->setMetadata([
			"name" => $server->getName(),
			"version" => $server->getiPocketVersion(),
			"api" => $server->getApiVersion(),
			"minecraft" => $server->getVersion(),
			"protocol" => Info::CURRENT_PROTOCOL
		]);
		$phar->setStub('<?php define("ipocket\\\\PATH", "phar://". __FILE__ ."/"); require_once("phar://". __FILE__ ."/src/ipocket/iPocket.php");  __HALT_COMPILER();');
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$phar->startBuffering();

		$filePath = substr(\iPocket\PATH, 0, 7) === "phar://" ? \iPocket\PATH : realpath(\iPocket\PATH) . "/";
		$filePath = rtrim(str_replace("\\", "/", $filePath), "/") . "/";
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath . "src")) as $file){
			$path = ltrim(str_replace(["\\", $filePath], ["/", ""], $file), "/");
			if($path{0} === "." or strpos($path, "/.") !== false or substr($path, 0, 4) !== "src/"){
				continue;
			}
			$phar->addFile($file, $path);
			$sender->sendMessage("[iPocket] Adding $path");
		}
		foreach($phar as $file => $finfo){
			/** @var \PharFileInfo $finfo */
			if($finfo->getSize() > (1024 * 512)){
				$finfo->compress(\Phar::GZ);
			}
		}
		//$phar->compressFiles(\Phar::GZ);
		//$phar->stopBuffering();

		$sender->sendMessage($server->getName() . " " . $server->getiPocketVersion() . " Phar file has been created on ".$pharPath);

		return true;
	}
}
