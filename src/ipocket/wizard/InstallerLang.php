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

namespace ipocket\wizard;


class InstallerLang{
	public static $languages = [
		"jpn" => "日本語",
		"en" => "English",
		"chs" => "简体中文",
		"zho" => "繁體中文",
		"rus" => "Русский"
	];
	private $texts = [];
	private $lang;
	private $langfile;

	public function __construct($lang = ""){
		if(file_exists(\ipocket\PATH . "src/ipocket/lang/Installer/" . $lang . ".ini")){
			$this->lang = $lang;
			$this->langfile = \ipocket\PATH . "src/ipocket/lang/Installer/" . $lang . ".ini";
		}else{
			$files = [];
			foreach(new \DirectoryIterator(\ipocket\PATH . "src/ipocket/lang/Installer/") as $file){
				if($file->getExtension() === "ini" and substr($file->getFilename(), 0, 2) === $lang){
					$files[$file->getFilename()] = $file->getSize();
				}
			}

			if(count($files) > 0){
				arsort($files);
				reset($files);
				$l = key($files);
				$l = substr($l, 0, -4);
				$this->lang = isset(self::$languages[$l]) ? $l : $lang;
				$this->langfile = \ipocket\PATH . "src/ipocket/lang/Installer/" . $l . ".ini";
			}else{
				$this->lang = "en";
				$this->langfile = \ipocket\PATH . "src/ipocket/lang/Installer/en.ini";
			}
		}

		$this->loadLang(\ipocket\PATH . "src/ipocket/lang/Installer/en.ini", "en");
		if($this->lang !== "en"){
			$this->loadLang($this->langfile, $this->lang);
		}

	}

	public function getLang(){
		return ($this->lang);
	}

	public function loadLang($langfile, $lang = "en"){
		$this->texts[$lang] = [];
		$texts = explode("\n", str_replace(["\r", "\\/\\/"], ["", "//"], file_get_contents($langfile)));
		foreach($texts as $line){
			$line = trim($line);
			if($line === ""){
				continue;
			}
			$line = explode("=", $line);
			$this->texts[$lang][trim(array_shift($line))] = trim(str_replace(["\\n", "\\N",], "\n", implode("=", $line)));
		}
	}

	public function get($name, $search = [], $replace = []){
		if(!isset($this->texts[$this->lang][$name])){
			if($this->lang !== "en" and isset($this->texts["en"][$name])){
				return $this->texts["en"][$name];
			}else{
				return $name;
			}
		}elseif(count($search) > 0){
			return str_replace($search, $replace, $this->texts[$this->lang][$name]);
		}else{
			return $this->texts[$this->lang][$name];
		}
	}

	public function __get($name){
		return $this->get($name);
	}

}
