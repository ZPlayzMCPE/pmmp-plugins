<?php

/*
 * VModifier plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/pmmp-plugins/blob/master/V>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

namespace kenygamer\VModifier;

use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{
  
  /** Plugin name */
  const PLUGIN_NAME = "VModifier";
  /** Plugin version */
  const PLUGIN_VERSION = "2.0";
  
  const MSG_NONE = 0;
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
    new AutoNotifier($this);
    $this->loadConfig();
    $enable = (bool) $this->getConfig()->get("enable-plugin");
    if(!$enable){
      $this->getLogger()->info(TF::RED."Disabling plugin, enable-plugin is set to false");
      $this->getPluginLoader()->disablePlugin($this);
      return;
    }
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  /**
   * @return void
   */
  public function onDisable(){
    $this->getLogger()->info(TF::RED."Disabling ".$this->getDescription()->getFullName()."...");
  }
  
  /**
   * Loads configuration file
   *
   * @return void
   */
  private function loadConfig(){
    if(!is_dir($this->getDataFolder())){
      @mkdir($this->getDataFolder());
    }
    if(!file_exists($this->getDataFolder()."config.yml")){
      $this->saveDefaultConfig();
    }
  }
  
  /**
   * @param PlayerCommandPreprocessEvent $event
   *
   * @return void
   */
  public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
    $player = $event->getPlayer();
    $command = explode(" ", strtolower($event->getMessage()));
    switch($command[0]){
      case "/version":
        $message = (string) $this->getConfig()->get("messages")["version"];
        break;
      case "/ver":
        $message = (string) $this->getConfig()->get("messages")["ver"];
        break;
      case "/pocketmine:version":
        $message = (string) $this->getConfig()->get("messages")["version"];
        break;
      case "/pocketmine:ver":
        $message = (string) $this->getConfig()->get("messages")["ver"];
        break;
      default:
        $message = self::MSG_NONE;
    }
    if($message !== self::MSG_NONE){
      $player->sendMessage($message);
      $event->setCancelled(true);
    }
  }
  
}
