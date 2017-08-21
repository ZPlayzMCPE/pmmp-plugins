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
  
  private $id;
  private $date;
  private $player;
  private $message;
  
  public function __construct(int $id, array $date, Player $player, string $message){
    $this->id = $id;
    $this->date = $date;
    $this->player = $player;
    $this->message = $message;
  }
  
  public function getId() : int{
    return $this->id;
  }
  
  public function getDate() : array{
    return $this->date;
  }
  
  public function getPlayer() : Player{
    return $this->player;
  }
  
  public function getMessage() : string{
    return $this->message;
  }
  
}
