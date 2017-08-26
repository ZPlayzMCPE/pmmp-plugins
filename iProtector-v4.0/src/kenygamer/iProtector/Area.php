<?php

/*
 * iProtector-v4.0 plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/pmmp-plugins/blob/master/iProtector-v4.0>
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

use pocketmine\math\Vector3;
use pocketmine\Player;

use kenygamer\iProtector\Main;

class Area{
  
  const WHITELIST_ACTION_ADD = 0;
  const WHITELIST_ACTION_REMOVE = 1;
  
  /** @var string */
  private $name;
  /** @var array */
  private $flags;
  /** @var Vector3 */
  private $pos1;
  /** @var Vector3 */
  private $pos2;
  /** @var string */
  private $level;
  /** @var string[] */
  private $whitelist;
  /** @var Main */
  private $plugin;
  
  public function __construct(string $name, array $flags, Vector3 $pos1, Vector3 $pos2, string $level, array $whitelist, Main $plugin){
    $this->name = strtolower($name);
    $this->flags = $flags;
    $this->pos1 = new Vector3($pos1[0], $pos1[1], $pos1[2]);
    $this->pos2 = new Vector3($pos2[0], $pos2[1], $pos2[2]);
    $this->level = $level;
    $this->whitelist = $whitelist;
    $this->plugin = $plugin;
    $this->save();
  }
  
  /**
   * Returns the area name
   *
   * @return string
   */
  public function getName() : string{
    return $this->name;
  }
  
  /**
   * Returns area 1st positions
   *
   * @return array
   */
  public function getPos1() : array{
    return [
      $this->pos1->getX(),
      $this->pos1->getY(),
      $this->pos1->getZ()
      ];
  }
  
  /**
   * Returns area 2nd positions
   *
   * @return array
   */
  public function getPos2() : array{
    return [
      $this->pos2->getX(),
      $this->pos2->getY(),
      $this->pos2->getZ()
      ];
  }
  
  /**
   * Returns area flags
   *
   * @return array
   */
  public function getFlags() : array{
    return $this->flags;
  }
  
  /**
   * Returns area flag value
   *
   * @param string $flag
   *
   * @return bool
   */
  public function getFlag(string $flag) : bool{
    if(isset($this->flags[$flag])){
      return $this->flags[$flag];
    }
    return false;
  }
  
  /**
   * Sets area flag to given value
   *
   * @param string $flag
   * @param bool $value
   *
   * @return bool
   */
  public function setFlag(string $flag, bool $value) : bool{
    if(isset($this->flags[$flag])){
      $this->flags[$flag] = $value;
      $this->plugin->saveAreas();
      return true;
    }
    return false;
  }
  
  /**
   * Toggles area flag value
   *
   * @param string $flag
   *
   * @return bool
   */
  public function toggleFlag(string $flag) : bool{
    if(isset($this->flags[$flag])){
      $this->flags[$flag] = !$this->flags[$flag];
      $this->plugin->saveAreas();
      return $this->flags[$flag];
    }
    return false;
  }
  
  /**
   * Checks if area is inside given position
   *
   * @param Position $position
   * @param string $level
   *
   * @return bool
   */
  public function contains(Position $position, string $level) : bool{
    if((min($this->pos1->getX(), $this->pos2->getX()) <= $position->getX()) && (max($this->pos1->getX(), $this->pos2->getX()) >= $position->getX()) && (min($this->pos1->getY(), $this->pos2->getY()) <= $position->getY()) && (max($this->pos1->getY(), $this->pos2->getY()) >= $position->getY()) && (min($this->pos1->getZ(), $this->pos2->getZ()) <= $position->getZ()) && (max($this->pos1->getZ(), $this->pos2->getZ()) >= $position->getZ()) && ($this->level == $level)) {
      return true;
    }
    return false;
  }
  
  /**
   * Returns area level name
   *
   * @return string
   */
  public function getLevel() : string{
    return $this->level;
  }
  
  /**
   * Checks if given player is whitelisted
   *
   * @param string $player
   *
   * @return bool
   */
  public function isWhitelisted(string $player) : bool{
    if(in_array($player, $this->whitelist)){
      return true;
    }
    return false;
  }
  
  /**
   * Adds/removes player from area whitelist
   *
   * @param string $player
   * @param bool $action
   *
   * @return bool
   */
  public function setWhitelisted(string $player, bool $action) : bool{
    if($action === self::WHITELIST_ACTION_ADD){
      if(!in_array($player, $this->whitelist)){
        array_push($this->whitelist, $player);
        $this->plugin->saveAreas();
        return true;
      }
    }elseif($action === self::WHITELIST_ACTION_REMOVE){
      if(in_array($player, $this->whitelist)){
        $key = array_search($player, $this->whitelist);
        array_splice($this->whitelist, $key, 1);
        $this->plugin->saveAreas();
        return true;
      }
    }
    return false;
  }
  
  /**
   * Returns area whitelist
   *
   * @return string[]
   */
  public function getWhitelist() : array{
    return $this->whitelist;
  }
  
  /**
   * Saves the area
   *
   * @return bool
   */
  public function save() : bool{
    $this->plugin->areas[$this->name] = $this;
    return true;
  }
  
  /**
   * Deletes the area
   *
   * @return bool
   */
  public function delete() : bool{
    unset($this->plugin->areas[$this->getName()]);
    $this->plugin->saveAreas();
    return true;
  }
  
}
