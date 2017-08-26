<?php

/*
 * iProtector-v4.0 plugin for PocketMine-MP
 * Copyright (C) 2014 LDX-MCPE <https://github.com/LDX-MCPE/iProtector>
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

namespace kenygamer\iProtector;

use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\entity\{EntityDamageEvent, EntityExplodeEvent};
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{
  
  /** @var array */
  private $c;
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");                                                                                                                                          
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    if(!is_dir($this->getDataFolder())){
      @mkdir($this->getDataFolder());
    }
    if(!file_exists($this->getDataFolder()."areas.json")){
      file_put_contents($this->getDataFolder()."areas.json", "[]");
    }
    if(!file_exists($this->getDataFolder()."config.yml")){
      $c = $this->getResource("config.yml");
      $o = stream_get_contents($c);
      fclose($c);
      file_put_contents($this->getDataFolder()."config.yml", str_replace("DEFAULT", $this->getServer()->getDefaultLevel()->getName(), $o));
    }
    $this->areas = [];
    $data = json_decode(file_get_contents($this->getDataFolder()."areas.json"), true);
    foreach($data as $datum){
      $area = new Area($datum["name"], $datum["flags"], $datum["pos1"], $datum["pos2"], $datum["level"], $datum["whitelist"], $this);
    }
    $this->c = yaml_parse(file_get_contents($this->getDataFolder()."config.yml"));
    if($this->c["Settings"]["Enable"] === false || $this->c["Settings"]["Enable"] !== true){
      $this->getPluginLoader()->disablePlugin($this);
    }
    $this->god = $this->c["Default"]["God"];
    $this->edit = $this->c["Default"]["Edit"];
    $this->tnt = $this->c["Default"]["TNT"];
    $this->touch = $this->c["Default"]["Touch"];
    $this->levels = [];
    foreach($this->c["Worlds"] as $level => $flags){
      $this->levels[$level] = $flags;
    }
    return true;
  }
  
  /**
   * @return void
   */
  public function onDisable(){
    $this->getLogger()->info(TF::RED."Disabling ".$this->getDescription()->getFullName()."...");
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
    if(!$sender instanceof Player){
      $sender->sendMessage($this->getPrefix().TF::RED." Please run this command in-game.");
      return true;
    }
    if(!isset($args[0])){
      return false;
    }
    $n = strtolower($sender->getName());
    $action = strtolower($args[0]);
    switch($action){
      case "pos1":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.pos1")){
          if(isset($this->sel1[$n]) || isset($this->sel2[$n])){
            $o = $this->getPrefix().TF::RED." You're already selecting a position!";
          }else{
            $this->sel1[$n] = true;
            $o = $this->getPrefix().TF::AQUA." Please place or break the first position.";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      case "pos2":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.pos2")){
          if(isset($this->sel1[$n]) || isset($this->sel2[$n])){
            $o = $this->getPrefix().TF::RED." You're already selecting a position!";
          }else{
            $this->sel2[$n] = true;
            $o = $this->getPrefix().TF::AQUA." Please place or break the second position.";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      case "create":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.create")){
          if(isset($args[1])){
            if(isset($this->pos1[$n]) && isset($this->pos2[$n])){
              if(!isset($this->areas[strtolower($args[1])])){
                $area = new Area(strtolower($args[1]), [
                  "edit" => true,
                  "god" => false,
                  "tnt" => false,
                  "touch" => true
                  ], [
                  $this->pos1[$n]->getX(),
                  $this->pos1[$n]->getY(),
                  $this->pos1[$n]->getZ()
                  ], [
                  $this->pos2[$n]->getX(),
                  $this->pos2[$n]->getY(),
                  $this->pos2[$n]->getZ()
                  ], $sender->getLevel()->getName(), [
                  $n], $this];
