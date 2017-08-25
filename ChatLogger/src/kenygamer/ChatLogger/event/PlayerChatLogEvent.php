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

namespace kenygamer\ChatLogger\event;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\Player;

class PlayerChatLogEvent extends Event implements Cancellable{
  
  public static $handlerList = null;
  
  /** @var int */
  private $id;
  /** @var string[] */
  private $date;
  /** @var Player */
  private $player;
  /** @var string */
  private $message;
  
  public function __construct(int $id, array $date, Player $player, string $message){
    $this->id = $id;
    $this->date = $date;
    $this->player = $player;
    $this->message = $message;
  }
  
  /**
   * Returns the chat log ID
   *
   * @return int
   */
  public function getId() : int{
    return $this->id;
  }
  
  /**
   * Returns an associative array
   * with time and date
   *
   * @return string[]
   */
  public function getDate() : array{
    return $this->date;
  }
  
  /**
   * Returns a Player object
   *
   * @return Player
   */
  public function getPlayer() : Player{
    return $this->player;
  }
  
  /**
   * Returns chat message
   *
   * @return string
   */
  public function getMessage() : string{
    return $this->message;
  }
  
}
