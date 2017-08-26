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
  
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");                                                                                                                                          
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
