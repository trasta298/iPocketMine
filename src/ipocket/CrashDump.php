<?php

namespace ipocket;

use ipocket\network\protocol\Info;
use ipocket\plugin\PluginBase;
use ipocket\plugin\PluginLoadOrder;
use ipocket\utils\Utils;
use ipocket\utils\VersionString;
use raklib\RakLib;

class CrashDump{

	/** @var Server */
	private $server;
	private $fp;
	private $time;
	private $data = [];
	private $encodedData = null;
	private $path;

	public function __construct(Server $server){
		$this->time = time();
		$this->server = $server;
		$this->path = $this->server->getCrashPath() . "CrashDump_" . date("D_M_j-H.i.s-T_Y", $this->time) . ".log";
		$this->fp = @fopen($this->path, "wb");
		if(!is_resource($this->fp)){
			throw new RuntimeException("Could not create Crash Dump");
		}
		$this->data["time"] = $this->time;
		$this->addLine($this->server->getName() . " Crash Dump " . date("D M j H:i:s T Y", $this->time));
		$this->addLine();
		$this->baseCrash();
		$this->generalData();
		$this->pluginsData();

		$this->extraData();
	}

	public function getPath(){
		return $this->path;
	}

	public function getEncodedData(){
		return $this->encodedData;
	}

	public function getData(){
		return $this->data;
	}

	private function encodeData(){
		$this->addLine();
		$this->addLine("----------------------REPORT THE DATA BELOW THIS LINE-----------------------");
		$this->addLine();
		$this->addLine("===BEGIN CRASH DUMP===");
		$this->encodedData = zlib_encode(json_encode($this->data, JSON_UNESCAPED_SLASHES), ZLIB_ENCODING_DEFLATE, 9);
		foreach(str_split(base64_encode($this->encodedData), 76) as $line){
			$this->addLine($line);
		}
		$this->addLine("===END CRASH DUMP===");
	}

	private function pluginsData(){
		if(class_exists("ipocket\\plugin\\PluginManager", false)){
			$this->addLine();
			$this->addLine("Loaded plugins:");
			$this->data["plugins"] = [];
			foreach($this->server->getPluginManager()->getPlugins() as $p){
				$d = $p->getDescription();
				$this->data["plugins"][$d->getName()] = [
					"name" => $d->getName(),
					"version" => $d->getVersion(),
					"authors" => $d->getAuthors(),
					"api" => $d->getCompatibleApis(),
					"enabled" => $p->isEnabled(),
					"depends" => $d->getDepend(),
					"softDepends" => $d->getSoftDepend(),
					"main" => $d->getMain(),
					"load" => $d->getOrder() === PluginLoadOrder::POSTWORLD ? "POSTWORLD" : "STARTUP",
					"website" => $d->getWebsite()
				];
				$this->addLine($d->getName() . " " . $d->getVersion() . " by " . implode(", ", $d->getAuthors()) . " for API(s) " . implode(", ", $d->getCompatibleApis()));
			}
		}
	}

	private function extraData(){
		global $arguments;

		if($this->server->getProperty("auto-report.send-settings", true) !== false){
			$this->data["parameters"] = (array) $arguments;
			$this->data["server.properties"] = @file_get_contents($this->server->getDataPath() . "server.properties");
			$this->data["server.properties"] = preg_replace("#^rcon\\.password=(.*)$#m", "rcon.password=******", $this->data["server.properties"]);
			$this->data["ipocket.yml"] = @file_get_contents($this->server->getDataPath() . "ipocket.yml");
		}else{
			$this->data["ipocket.yml"] = "";
			$this->data["server.properties"] = "";
			$this->data["parameters"] = [];
		}
		$extensions = [];
		foreach(get_loaded_extensions() as $ext){
			$extensions[$ext] = phpversion($ext);
		}
		$this->data["extensions"] = $extensions;

		if($this->server->getProperty("auto-report.send-phpinfo", true) !== false){
			ob_start();
			phpinfo();
			$this->data["phpinfo"] = ob_get_contents();
			ob_end_clean();
		}
	}

	private function baseCrash(){
		global $lastExceptionError, $lastError;

		if(isset($lastExceptionError)){
			$error = $lastExceptionError;
		}else{
			$error = (array) error_get_last();
			$error["trace"] = @getTrace(3);
			$errorConversion = [
				E_ERROR => "E_ERROR",
				E_WARNING => "E_WARNING",
				E_PARSE => "E_PARSE",
				E_NOTICE => "E_NOTICE",
				E_CORE_ERROR => "E_CORE_ERROR",
				E_CORE_WARNING => "E_CORE_WARNING",
				E_COMPILE_ERROR => "E_COMPILE_ERROR",
				E_COMPILE_WARNING => "E_COMPILE_WARNING",
				E_USER_ERROR => "E_USER_ERROR",
				E_USER_WARNING => "E_USER_WARNING",
				E_USER_NOTICE => "E_USER_NOTICE",
				E_STRICT => "E_STRICT",
				E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
				E_DEPRECATED => "E_DEPRECATED",
				E_USER_DEPRECATED => "E_USER_DEPRECATED",
			];
			$error["fullFile"] = $error["file"];
			$error["file"] = cleanPath($error["file"]);
			$error["type"] = isset($errorConversion[$error["type"]]) ? $errorConversion[$error["type"]] : $error["type"];
			if(($pos = strpos($error["message"], "\n")) !== false){
				$error["message"] = substr($error["message"], 0, $pos);
			}
		}

		if(isset($lastError)){
			$this->data["lastError"] = $lastError;
		}

		$this->data["error"] = $error;
		unset($this->data["error"]["fullFile"]);
		unset($this->data["error"]["trace"]);
		$this->addLine("Error: " . $error["message"]);
		$this->addLine("File: " . $error["file"]);
		$this->addLine("Line: " . $error["line"]);
		$this->addLine("Type: " . $error["type"]);

		if(strpos($error["file"], "src/ipocket/") === false and strpos($error["file"], "src/raklib/") === false and file_exists($error["fullFile"])){
			$this->addLine();
			$this->addLine("THIS CRASH WAS CAUSED BY A PLUGIN");
			$this->addLine("此次出错由插件引起");
			$this->data["plugin"] = true;

			$reflection = new \ReflectionClass(PluginBase::class);
			$file = $reflection->getProperty("file");
			$file->setAccessible(true);
			foreach($this->server->getPluginManager()->getPlugins() as $plugin){
				$filePath = \ipocket\cleanPath($file->getValue($plugin));
				if(strpos($error["file"], $filePath) === 0){
					$this->data["plugin"] = $plugin->getName();
					$this->addLine("BAD PLUGIN : " . $plugin->getDescription()->getFullName());
					break;
				}
			}
		}else{
			$this->data["plugin"] = false;
		}

		$this->addLine();
		$this->addLine("Code:");
		$this->data["code"] = [];

		if($this->server->getProperty("auto-report.send-code", true) !== false){
			$file = @file($error["fullFile"], FILE_IGNORE_NEW_LINES);
			for($l = max(0, $error["line"] - 10); $l < $error["line"] + 10; ++$l){
				$this->addLine("[" . ($l + 1) . "] " . @$file[$l]);
				$this->data["code"][$l + 1] = @$file[$l];
			}
		}

		$this->addLine();
		$this->addLine("Backtrace:");
		foreach(($this->data["trace"] = $error["trace"]) as $line){
			$this->addLine($line);
		}
		$this->addLine();
	}

	private function generalData(){
		$version = new VersionString();
		$this->data["general"] = [];
		$this->data["general"]["version"] = $version->get(false);
		$this->data["general"]["build"] = $version->getBuild();
		$this->data["general"]["protocol"] = Info::CURRENT_PROTOCOL;
		$this->data["general"]["api"] = \ipocket\API_VERSION;
		$this->data["general"]["git"] = \ipocket\GIT_COMMIT;
		$this->data["general"]["raklib"] = RakLib::VERSION;
		$this->data["general"]["uname"] = php_uname("a");
		$this->data["general"]["php"] = phpversion();
		$this->data["general"]["zend"] = zend_version();
		$this->data["general"]["php_os"] = PHP_OS;
		$this->data["general"]["os"] = Utils::getOS();
		$this->addLine("iPocketMine version: " . $version->get(false) . " #" . $version->getBuild() . " [Protocol " . Info::CURRENT_PROTOCOL . "; API " . API_VERSION . "]");
		$this->addLine("uname -a: " . php_uname("a"));
		$this->addLine("PHP version: " . phpversion());
		$this->addLine("Zend version: " . zend_version());
		$this->addLine("OS : " . PHP_OS . ", " . Utils::getOS());
	}

	public function addLine($line = ""){
		fwrite($this->fp, $line . PHP_EOL);
	}

	public function add($str){
		fwrite($this->fp, $str);
	}

}