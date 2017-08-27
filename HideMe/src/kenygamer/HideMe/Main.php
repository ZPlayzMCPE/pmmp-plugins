<?php

/*
 * HideMe plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/pmmp-plugins/blob/master/HideMe>
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

namespace kenygamer\HideMe;

use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{
  
  const NAME = "HideMe";
  const VERSION = "1.2";
  
  /** @var array */
  private $nametags = [];
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
    new AutoNotifier($this, self::NAME, self::VERSION);
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  /**
   * @return void
   */
  public function onDisable(){
    $this->getLogger()->info(TF::RED."Disabling ".$this->getDescription()->getFullName()."...");
  }
  
  /**
   * Returns command prefix
   *
   * @return string
   */
  private function getPrefix() : string{
    return TF::GREEN."[HideMe]".TF::RESET;
  }
  
  /**
   * Hides or temporalily modifies
   * a player nametag
   *
   * @param Player $player
   * @param bool $cnt
   * @param string $nametag
   *
   * @return void
   */
  private function hide(Player $player, bool $cnt = false, string $nametag = ""){
    if($cnt){
      $this->nametags[$player->getName()] = $player->getNameTag(); //saves old nametag
      $player->setNameTag($nametag); //alters player nametag temporalily
      $player->sendMessage($this->getPrefix().TF::GREEN." Your tag has been successfully (temporalily) changed to ".TF::YELLOW.$nametag);
    }else{
      $player->setNameTagAlwaysVisible(false);
      $player->sendMessage($this->getPrefix().TF::GREEN." Your tag has been successfully hidden.");
    }
  }
  
  /**
   * Unhides a player nametag
   *
   * @param Player $player
   *
   * @return void
   */
  private function unhide(Player $player){
    if(isset($this->nametags[$player->getName()]) || !empty($this->nametags[$player->getName()])){
      $player->setNameTag($this->nametags[$player->getName()]);
    }
    $player->setNameTagAlwaysVisible(true);
    $player->sendMessage($this->getPrefix().TF::GREEN." Your tag has been successfully unhidden.");
  }
  
  /**
   * @param PlayerQuitEvent $event
   *
   * @return void
   */
  public function onQuit(PlayerQuitEvent $event){
    if(isset($this->nametags[$event->getPlayer()->getName()]) || !empty($this->nametags[$event->getPlayer()->getName()])){
      $event->getPlayer()->setNameTag($this->nametags[$event->getPlayer()->getName()]);
    }
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
    switch(strtolower($command->getName())){
      case "hide":
        if(!$sender->hasPermission("hideme.commands")){
          $sender->sendMessage($this->getPrefix().TF::RED." You do not have permission to use HideMe commands!");
          return true;
        }
        if(!$sender instanceof Player){
          $sender->sendMessage($this->getPrefix().TF::RED." Please run command in-game.");
          return true;
        }
        if(!isset($args[0]) || empty($nametag = $args[0])){
          $this->hide($sender);
        }else{
          $this->hide($sender, true, $nametag);
        }
        return true;
        break;
      case "unhide":
        if(!$sender->hasPermission("hideme.commands")){
          $sender->sendMessage($this->getPrefix().TF::RED." You do not have permission to use HideMe commands!");
        }
        if(!$sender instanceof Player){
          $sender->sendMessage($this->getPrefix().TF::RED." Please run command in-game.");
          return true;
        }
        $this->unhide($sender);
        return true;
        break;
      default:
        return false;
    }
  }
  
}
