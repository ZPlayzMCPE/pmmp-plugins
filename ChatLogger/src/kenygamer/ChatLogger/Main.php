<?php

/*
 * ChatLogger plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/pmmp-plugins/blob/master/ChatLogger>
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

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

use kenygamer\ChatLogger\classes\Log;
use kenygamer\ChatLogger\classes\Report;

class Main extends PluginBase implements Listener{
  
  const BYPASS_PERMISSION = "chatlogger.bypass";
  
  /** @var bool */
  private $debug = false;
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
    $this->loadConfig();
    @mkdir($this->getDataFolder()."data");
    @mkdir($this->getDataFolder()."data/log");
    @mkdir($this->getDataFolder()."data/reports");
    $debug = (int) $this->getConfig()->get("enable-debug");
    $this->debug = ($debug === 0) ? false : true;
    $report = (bool) $this->getConfig()->get("generate-report");
    if($report){
      $player = (string) $this->getConfig()->get("report-player");
      $date = (string) $this->getConfig()->get("report-date");
      if(empty($player) || empty($date)){
        $this->getLogger()->error("Report couldn't be generated: empty field(s)");
      }else{
        new Report($this, $player, $date);
      }
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
   * Debugs a message to console
   *
   * @param string $message
   *
   * @return bool
   */
  public function debug(string $message) : bool{
    if($this->debug){
      $this->getLogger()->debug($message);
      return true;
    }
    return false;
  }
  
  /**
   * @param PlayerChatEvent $event
   *
   * @return void
   */
  public function onChat(PlayerChatEvent $event){
    if($event->isCancelled()){
      $this->debug("Event is canceled.");
      return;
    }
    $player = $event->getPlayer();
    if($player->hasPermission(self::BYPASS_PERMISSION)){
      $this->debug("Player ".$player->getName()." not logged: has ".self::BYPASS_PERMISSION." permission");
      return;
    }
    $this->debug("Logging ".$player->getName()."'s message...");
    new Log($this, $player, $event->getMessage());
  }
  
}
