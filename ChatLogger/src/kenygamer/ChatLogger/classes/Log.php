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

namespace kenygamer\ChatLogger\classes;

use pockemine\Player;

use kenygamer\ChatLogger\event\PlayerChatLogEvent;

class Log{
  
  const LOG_PATH = "data/log/db.json";
  
  /** @var Main */
  private $plugin;
  /** @var Player */
  private $player;
  /** @var string */
  private $message;
  
  public function __construct(Main $plugin, Player $player, string $message){
    $this->plugin = $plugin;
    $this->player = $player;
    $this->message = $message;
    /**/ /**/ /**/ /**/ /**/
    $this->log();
  }
  
  /**
   * Returns all logs in form of array
   *
   * @return array
   */
  private function getChatLogs() : array{
    if(!file_exists($logs = $this->getDataFolder().self::LOG_PATH)){
      @file_put_contents($logs, "[]");
      return [];
    }
    return json_decode(file_get_contents($logs), true);
  }
  
  /**
   * Logs a chat message
   *
   * @return void
   */
  private function log(){
    $time = date('H:i:s');
    $date = date('d-m-Y');
    $name = strtolower($this->player->getName());
    $logs = $this->getChatLogs();
    $id = ++(end($logs))['id'];
    $log = [
      'id' => $id,
      'date' => [
        'time' => $time,
        'date' => $date
        ],
      'player' => $name,
      'message' => $this->message
      ];
    $this->plugin->debug("Saving $name's message...");
    if($this->save($log)){
      $this->plugin->debug("$name's message successfully saved");
    }else{
      $this->plugin->debug("Error while saving $name's message");
    }
  }
  
  /**
   * Saves message in log
   *
   * @param array $log
   *
   * @return bool
   */
  private function save(array $log) : bool{
    $event = new PlayerChatLogEvent($log['id'], $log['date'], $this->player, $this->message);
    $this->plugin->getServer()->getPluginManager()->callEvent($event);
    if($event->isCancelled()){
      $this->plugin->debug("PlayerChatLogEvent event was canceled.");
      return false;
    }
    foreach($this->getChatLogs() as $chatLog){
      $logs[] = $chatLog;
    }
    $logs[] = $log;
    $prettySave = (bool) $this->plugin->getConfig()->get("pretty-save");
    if($prettySave){
      if(file_put_contents(self::LOG_PATH, json_encode($logs, JSON_PRETTY_PRINT)) === false){
        return false;
      }
      return true;
    }else{
      if(file_put_contents(self::LOG_PATH, json_encode($logs)) === false){
        return false;
      }
      return true;
    }
  }
  
}
