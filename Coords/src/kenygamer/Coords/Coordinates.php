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

use pocketmine\Player;

class Coordinates{
 
 /** @var Player */
 private $player;
 
 public function __construct(Player $player){
  $this->player = $player;
 }
 
 /**
  * Returns a numeric array with player
  * coordinates
  * 
  *
  * @return array
  */
 public function getCoords(){
  $coords[0] = $this->roundVal($this->player->getX());
  $coords[1] = $this->roundVal($this->player->getY());
  $coords[2] = $this->roundVal($this->player->getZ());
  $coords[3] = $this->player->getLevel()->getName();
  /*for($i = 0; $i < count($coords) - 1; $i++){
   $coords[$i] = $this->roundVal($coords[$i]);
  }*/
  return $coords;
 }
 
 /**
  * Returns a rounded value
  *
  * @param mixed $value
  *
  * @return int|float
  */
 private function roundVal($value){
  if(is_int($value)){
   return $value;
  }elseif(is_float($value)){
   return round($value, 1);
  }else{
   return 0;
  }
 }
 
}
