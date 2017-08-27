<?php

/*
 * CapsLimiter plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/CapsLimiter>
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

namespace kenygamer\CapsLimiter;

use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{
  
  const NAME = "CapsLimiter";
  const VERSION = "1.2";
  
  /** @var int */
  private $maxcaps;
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
    new AutoNotifier($this, self::NAME, self::VERSION);
    $this->loadConfig();
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
    $this->saveDefaultConfig();
    $this->maxcaps = $this->getConfig()->get("max-caps");
  }
  
  /**
   * Saves configuration file
   *
   * @return void
   */
  private function saveConfig(){
    $this->getConfig()->set("max-caps", $this->getMaxCaps());
    $this->getConfig()->save();
  }
  
  /**
   * Returns command prefix
   *
   * @return string
   */
  private function getPrefix(){
    return TF::GREEN."[CapsLimiter]".TF::RESET;
  }
  
  /**
   * Returns maximum caps
   *
   * @return int
   */
  private function getMaxCaps(){
    return $this->maxcaps;
  }
  
  /**
   * @param CommandSender $sender
   * @param Command $command
   * @param string $label
   * @param string[] $args
   *
   * @return bool
   */
  public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
    if(!$sender->hasPermission("capslimiter.command")){
      return false;
    }
    if(!is_array($args) or count($args) < 1){
      $sender->sendMessage($this->getPrefix()." /limit <value>");
      return true;
    }
    if(!is_array($args) or is_numeric($args[0]) > 0){
      $this->maxcaps = $args[0];
      $this->saveConfig();
      $sender->sendMessage($this->getPrefix().TF::GREEN." Maximum caps successfully set to ".$args[0]);
      return true;
    }
    $sender->sendMessage($this->getPrefix().TF::RED." Value must be in positive numeric form.");
    return false;
  }
  
  /**
   * @param PlayerChatEvent $event
   *
   * @return void
   */
  public function onChat(PlayerChatEvent $event){
    $player = $event->getPlayer();
    $message = $event->getMessage();
    $strlen = strlen($message);
    $asciiA = ord("A");
    $asciiZ = ord("Z");
    $count = 0;
    for($i = 0; $i < $strlen; $i++){
      $char = $message[$i];
      $ascii = ord($char);
      if($asciiA <= $ascii and $ascii <= $asciiZ){
        $count++;
      }
    }
    if($count > $this->getMaxCaps()){
      if(!$player->hasPermission("capslimiter.bypass")){
        $event->setCancelled();
        $player->sendMessage($this->getPrefix().TF::RED." Your message cannot be sent because you have used too many caps!");
      }
    }
  }
  
}
