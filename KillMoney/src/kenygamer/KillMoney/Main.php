<?php

/*
 * KillMoney plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/pmmp-plugins/blob/master/KillMoney>
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

namespace kenygamer\KillMoney;

use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{
  
  const NAME = "KillMoney";
  const VERSION = "1.1.3";
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
    new AutoNotifier($this, self::NAME, self::VERSION);
    $this->loadConfig();
    $enable = (bool) $this->getConfig()->get("enable-plugin");
    if(!$enable){
      $this->getLogger()->info(TF::RED."Disabling plugin, enable-plugin is set to false");
      $this->getPluginLoader()->disablePlugin($this);
      return;
    }
    if(!is_dir($this->getServer()->getDataPath()."plugins/EconomyAPI")){
      $this->getLogger()->warning("Disabling plugin, EconomyAPI dependency not found");
      $this->getPluginLoader()->disablePlugin($this);
      return;
    }
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  /**
   * @return void
   */
  public function onDisable(){
    $this->getLogger()->info(TF::RED."Disabling ".$this->getDescription()->getFullName()."...");
  }
  
  /**
   * @param PlayerDeathEvent $event
   *
   * @return void
   */
  public function onPlayerDeath(PlayerDeathEvent $event){
    $player = $event->getPlayer();
    if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent){
      if($player->getLastDamageCause()->getDamager() instanceof Player){
        $killer = $player->getLastDamageCause()->getDamager();
        $killerMoney = $this->getConfig()->get("killer-money");
        $victimTakeMoney = $this->getConfig()->get("victim-take-money");
        $victimMoney = $this->getConfig()->get("victim-money");
        $victimMinimumMoney = $this->getConfig()->get("victim-minimum-money");
        if(!is_numeric($killerMoney) || !is_numeric($victimTakeMoney) || !is_numeric($victimMoney) || !is_numeric($victimMinimumMoney)){
          $this->getLogger()->error("Couldn't give money: non-numeric value(s) found in config");
          return;
        }
        
