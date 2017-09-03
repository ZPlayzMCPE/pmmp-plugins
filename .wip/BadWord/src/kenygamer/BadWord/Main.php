<?php

/*
 * BadWord plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/pmmp-plugins/blob/master/BadWord>
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

namespace kenygamer\BadWord;

use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

use kenygamer\BadWord\Profile;

class Main extends PluginBase implements Listener{
  
  /** Plugin name */
  const PLUGIN_NAME = "BadWord";
  /** Plugin version */
  const PLUGIN_VERSION = "1.0";
  
  /** @var Profiles[] */
  private $profiles = [];
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
    new AutoNotifier($this);
    $this->loadConfig();
    if(!file_exists($this->getDataFolder()."profiles.json")){
      file_put_contents($this->getDataFolder()."profiles.json", "[]");
    }
    $data = json_decode(file_get_contents($this->getDataFolder()."profiles.json"), true);
    foreach($data as $datum){
      $this->profiles[] = new Profile($datum["name"], $datum["words"]);
    }
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  /**
   * @return void
   */
  public function onDisable(){
    $this->getLogger()->info(TF::RED."Disabling ".$this->getDescription()->getFullName()."...");
    $this->saveProfiles();
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
   * Returns command prefix
   *
   * @return string
   */
  private function getPrefix() : string{
    return TF::GREEN."[BadWord]".TF::RESET;
  }
  
  /**
   * Saves profiles to file path
   *
   * @return void
   */
  private function saveProfiles(){
    foreach($this->profiles as $profile){
      $profiles[] = [
        "name" => $profile->getName(),
        "words" => $profile->getWords()
        ];
    }
    if(file_put_contents($this->getDataFolder()."profiles.json", json_encode($profiles, JSON_PRETTY_PRINT)) === false){
      $this->getLogger()->critical("Failed to save profiles");
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
      case "badword":
      case "bw":
        if(!$sender->hasPermission("badword.command")){
          $sender->sendMessage($this->getPrefix().TF::RED." You do not have permission to use this command.");
          return true;
        }
        if(!$sender instanceof Player){
          $sender->sendMessage($this->getPrefix().TF::RED." Please run command in-game.");
          return true;
        }
        if(isset($args[0])){
          // Configuration values
          $as = (bool) $this->getConfig()->get("anti-spam");
          $mw = (int) $this->getConfig()->get("max-words");
          $ap = (bool) $this->getConfig()->get("award-player");
          $apc = (array) $this->getConfig()->get("award-player-commands");
          //
          $exists = false;
          foreach($this->profiles as $profile){
            if($profile->getName() === strtolower($sender->getName())){
              $exists = true;
            }
          }
          if(!$exists){
            $this->profiles[strtolower($sender->getName())] = new Profile($sender->getName(), []);
            $this->saveProfiles();
          }
          $profile = $this->profiles[strtolower($sender->getName())];
          if($as && count($profile->getWords(false)) > $mw){
            $sender->sendMessage($this->getPrefix().TF::RED." You have already submitted a word recently, please wait for it to be approved.");
            return true;
          }
          foreach($this->profiles as $profile){
            foreach($profile->getWords() as $word){
              if($word[0] === $args[0]){
                $sender->sendMessage($this->getPrefix().TF::RED." Looks like this word is already added.");
                return true;
              }
            }
          }
          $words = $profile->getWords();
          array_push($words, [$args[0], false]);
          $profile->unsetWords();
          foreach($words as $word){
            $profile->setWord($word);
          }
          $this->saveProfiles();
          $sender->sendMessage($this->getPrefix().TF::GREEN." Thanks for your suggestion!");
          return true;
        }
        $sender->sendMessage($this->getPrefix().TF::RED." Please specify a word.");
        return true;
        break;
      case "bwadmin":
        if(!$sender->hasPermission("badword.admin.command")){
          $sender->sendMessage($this->getPrefix().TF::RED." You do not have permission to use this command.");
          return true;
        }
        if(!$sender instanceof Player){
          $sender->sendMessage($this->getPrefix().TF::RED." Please run command in-game.");
          return true;
        }
        if(!isset($args[0])){
          return false;
        }
        $action = strtolower($args[0]);
        switch($action){
          case "approve":
            if(!isset($args[1])){
              $sender->sendMessage("Usage: /bwadmin approve <word>");
              return true;
            }
            $exists = false;
            foreach($this->profiles as $profile){
              foreach(($words = $profile->getWords()) as $word){
                if($word[0] === $args[1]){
                  $exists = true;
                  $name = $profile->getName();
                  $profile->unsetWords();
                  foreach($words as $word){
                    if($word[0] === $args[1] && !$word[1]){
                      $word[1] = true;
                    }
                    $profile->setWord($word);
                  }
                }
              }
            }
            if(!$a){
              $sender->sendMessage($this->getPrefix().TF::RED." Word not found or already approved. Please make sure it is case sensitive.");
              return true;
            }
            $this->saveProfiles();
            $sender->sendMessage($this->getPrefix().TF::GREEN." ".$name."'s word successfully approved.");
            return true;
            break;
          case "list":
            $sender->sendMessage(TF::WHITE."--- Word List ---");
            foreach($this->profiles as $profile){
              foreach($profile->getWords(false) as $word){
                $sender->sendMessage(TF::DARK_GREEN."- ".TF::WHITE.$word[0].TF::RESET);
              }
            }
            return true;
            break;
          default:
            return false;
        }
      default:
        return false;
    }
  }
  
}
