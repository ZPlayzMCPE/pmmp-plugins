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
  
  /** Plugin name */
  const PLUGIN_NAME = "KillMoney";
  /** Plugin version */
  const PLUGIN_VERSION = "1.1.3";
  
  const KILLER_PERMISSION = "killmoney.killer.receive.money";
  const VICTIM_PERMISSION = "killmoney.victim.lose.money";
  
  /** @var EconomyAPI */
  private $economyAPI;
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
    new AutoNotifier($this);
    $this->loadConfig();
    $enable = (bool) $this->getConfig()->get("enable-plugin");
    if(!$enable){
      $this->getLogger()->info(TF::RED."Disabling plugin, enable-plugin is set to false");
      $this->getPluginLoader()->disablePlugin($this);
      return;
    }
    $this->economyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    if(!$this->economyAPI instanceof onebone\economyapi\EconomyAPI && !$this->economyAPI->isEnabled()){
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
   * Returns a string with its tags translated
   *
   * @param array $values
   * @param string $message
   *
   * @return string
   */
  private function translate(array $values, string $message) : string{
    $tags = [
      "%KILLER%",
      "%VICTIM%",
      "%MONEY%"
      ];
    return str_replace($tags, $values, $message);
  }
  
  /**
   * @param PlayerDeathEvent $event
   *
   * @return void
   */
  public function onPlayerDeath(PlayerDeathEvent $event){
    $victim = $event->getPlayer();
    if($victim->getLastDamageCause() instanceof EntityDamageByEntityEvent){
      if($victim->getLastDamageCause()->getDamager() instanceof Player){
        $killer = $victim->getLastDamageCause()->getDamager();
        // Configuration values
        $km = (int) $this->getConfig()->get("killer-money");
        $vtm = (bool) $this->getConfig()->get("victim-take-money");
        $vm = (int) $this->getConfig()->get("victim-money");
        $vmm = (int) $this->getConfig()->get("victim-minimum-money");
        $em = (bool) $this->getConfig()->get("enable-messages");
        //
        if($killer->hasPermission(self::KILLER_PERMISSION)){
          $this->economyAPI->addMoney($killer->getName(), $km);
          if($em){
            $killer->sendMessage($this->translate([$killer->getName(), $victim->getName(), $km], (string) $this->getConfig()->get("killer-message")));
          }
        }
        if($vtm && $victim->hasPermission(self::VICTIM_PERMISSION)){
          if(!$this->economyAPI->myMoney($victim->getName()) < $vmm){
            $this->economyAPI->reduceMoney($victim->getName(), $vm);
            if($em){
              $victim->sendMessage($this->translate([$killer->getName(), $victim->getName(), $vm], (string) $this->getConfig()->get("victim-message")));
            }
          }
        }
      }
    }
  }
  
  public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
    if($command->getName() === "killmoney"){
      if($sender->hasPermission("killmoney.command")){
        $sender->sendMessage(TF::GREEN."[KillMoney]".TF::GOLD." This server is running ".TF::GREEN.$this->getDescription()->getFullName()."\n".TF::AQUA."Author: @XxKenyGamerxX\nLink: github.com/kenygamer/pmmp-plugins");
        return true;
      }
    }
    return false;
  }
  
}
