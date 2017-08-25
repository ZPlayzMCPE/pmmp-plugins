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

use pocketmine\Player;

use kenygamer\ChatLogger\Main;

class Report{
  
  const LOG_PATH = "data/log/db.json";
  const REPORT_PATH = "data/reports";
  
  /** @var Main */
  private $plugin;
  /** @var string */
  private $player;
  /** @var string */
  private $date;
  
  public function __construct(Main $plugin, string $player, string $date){
    $this->plugin = $plugin;
    $this->player = strtolower($player); //not case sensitive
    $this->date = $date;
    /**/ /**/ /**/ /**/ /**/
    $this->generate();
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
   * Generates a report
   *
   * @return void
   */
  private function generate(){
    $reportPath = $this->plugin->getDataFolder().self::REPORT_PATH."/".$this->date."_".$this->player;
    if(file_exists($reportPath)){
      $this->plugin->getLogger()->error("Report couldn't be generated: already exists $reportPath");
      return;
    }
    $this->plugin->debug("[REPORT] Reading logger...");
    $logs = $this->getChatLogs();
    $this->plugin->debug("[REPORT] Initializing counters...");
    $matches = [];
    $num = 0;
    foreach($logs as $chatLog){
      if($chatLog['date']['date'] === $this->date and $chatLog['player'] === $this->player){
        ++$num;
        $this->plugin->debug("[REPORT] Pushing match $num...");
        $matches[] = $chatLog;
      }
    }
    if(empty($matches)){
      $this->plugin->debug("[REPORT] Process completed without finding matches");
    }
    $this->plugin->debug("[REPORT] Saving ".$this->player"'s report...");
    if($this->save($reportPath, $matches)){
      $this->plugin->debug("[REPORT] ".$this->player."'s report successfully saved to $reportPath");
    }else{
      $this->plugin->debug("[REPORT] Error while saving ".$this->player."'s report");
    }
  }
  
  /**
   * Saves a report to file
   *
   * @param string $path
   * @param array $report
   *
   * @return bool
   */
  private function save(string $path, array $report) : bool{
    $prettySave = (bool) $this->plugin->getConfig()->get("pretty-save");
    if($prettySave){
      if(file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT)) === false){
        return false;
      }
      return true;
    }else{
      if(file_put_contents($path, json_encode($report)) === false){
        return false;
      }
      return true;
    }
  }
  
}
