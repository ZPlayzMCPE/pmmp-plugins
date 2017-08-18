<?php

/*
 * Coords plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/pmmp-plugins/blob/master/Coords>
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

namespace kenygamer\Coords;

use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

use kenygamer\Coords\Coordinates;

class Main extends PluginBase implements Listener{
  
  const VERSION = "3.1";
  
  /** @var string */
  private $format;
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
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
    if(!is_dir($this->getDataFolder())){
      @mkdir($this->getDataFolder());
    }
    if(!file_exists($this->getDataFolder()."config.yml")){
      $this->saveDefaultConfig();
    }
    if(!file_exists($this->getFormatPath())){
      $this->format = "X: %X% Y: %Y% Z: %Z% %LINE%Level: %LEVEL%";
      file_put_contents($this->getFormatPath(), $this->format);
    }
  }
  
  /**
   * Returns command prefix
   *
   * @return string
   */
  private function getPrefix(){
    return TF::GREEN."[Coords]".TF::RESET;
  }
  
  /**
   * Returns format path
   *
   * @return string
   */
  private function getFormatPath(){
    return $this->getDataFolder()."coords.message";
  }
  
  /**
   * Returns a string with its tags translated
   *
   * @param string $message
   * @param array $coords
   *
   * @return string
   */
  private function translate(string $message, array $coords){
   $tags = [
     "%LINE%",
     "%X%",
     "%Y%",
     "%Z%",
     "%LEVEL%"
     ];
    $values = [
      "\n",
      $coords[0],
      $coords[1],
      $coords[2],
      $coords[3]
      ];
    return str_replace($tags, $values, $message);
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
    if($command->getName() === "coords"){
      if(isset($args[0])){
        if(!$sender->hasPermission("coords.command.see")){
          $sender->sendMessage($this->getPrefix().TF::RED." You do not have permission to use the see command!");
          return true;
        }
        $player = $this->getServer()->getPlayer(strtolower($args[0]));
        if($player instanceof Player){
          $coords = new Coordinates($player);
          $sender->sendMessage($this->getPrefix().TF::AQUA." ".strtolower($args[0])."'s".TF::YELLOW." coordinates:\n".TF::GOLD."X: ".TF::BLUE.$coords[0]."\n".TF::GOLD."Y: ".TF::BLUE.$coords[1]."\n".TF::GOLD."Z: ".TF::BLUE.$coords[2]."\n".TF::GOLD."Level: ".TF::BLUE.$coords[3]);
          return true;
        }else{
          $sender->sendMessage($this->getPrefix().TF::RED." The requested player is not online!");
          return true;
        }
      }
      if(!$sender->hasPermission("coords.command.coords")){
        $sender->sendMessage($this->getPrefix().TF::RED." You do not have permission to use the coords command!");
        return true;
      }
      if(!$sender instanceof Player){
        $sender->sendMessage($this->getPrefix().TF::RED." You must run command in-game.");
        return true;
      }
      $sender->sendMessage($this->getPrefix().TF::GREEN." Getting your coordinates...");
      $coords = new Coordinates($sender);
      $dm = (int) $this->getConfig()->get("display-method");
      switch($dm){
        case 1:
          $sender->sendMessage($this->translate($this->format, $coords));
          return true;
          break;
        case 2:
          $sender->sendPopup($this->translate($this->format, $coords));
          return true;
          break;
        case 3:
          $sender->sendTip($this->translate($this->format, $coords));
          return true;
          break;
        default:
          $this->getLogger()->warning("Invalid display mode $dm, resetting to message");
          $this->getConfig()->set("display-method", 1);
          $this->getConfig()->save();
          $sender->sendMessage($this->translate($this->format, $coords));
          return true;
      }
    }elseif($command->getName() === "coordtags"){
      if(!$sender->hasPermission("coords.command.tags")){
        $sender->sendMessage($this->getPrefix().TF::RED." You do not have permission to use the tags command!");
      }
      $sender->sendMessage($this->getPrefix().TF::AQUA." Available tags:\n".TF::GOLD."%X%".TF::WHITE." - Sender's X\n".TF::GOLD."%Y%".TF::WHITE." - Sender's Y\n".TF::GOLD."%Z%".TF::WHITE." - Sender's Z\n".TF::GOLD."%LEVEL%".TF::WHITE." - Sender's world\n".TF::GOLD."%LINE%".TF::WHITE." - Line break");
      return true;
    }elseif($command->getName() === "coordupdate"){
      if(!$sender->hasPermission("coords.command.update")){
        $sender->sendMessage($this->getPrefix().TF::RED." You do not have permission to use the update command!");
      }
      if(empty($message = $args[0])){
        $sender->sendMessage("Usage: /coordupdate <message>");
        return true;
      }
      file_put_contents($this->getFormatPath(), implode(" ", $message));
      $sender->sendMessage($this->getPrefix().TF::GREEN." Message successfully updated!");
      return true;
    }
  }
  
}
