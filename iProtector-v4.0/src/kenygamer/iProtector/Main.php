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

use pocketmine\block\Block;
use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\entity\{EntityDamageEvent, EntityExplodeEvent};
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\{Level, Position};
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{
  
  /** Plugin name */
  const PLUGIN_NAME = "iProtector-v4.0";
  /** Plugin version */
  const PLUGIN_VERSION = "4.0.3";
  
  /** @var array */
  private $c;
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
    new AutoNotifier($this);
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    if(!is_dir($this->getDataFolder())){
      @mkdir($this->getDataFolder());
    }
    if(!file_exists($this->getDataFolder()."areas.json")){
      file_put_contents($this->getDataFolder()."areas.json", "[]");
    }
    if(!file_exists($this->getDataFolder()."config.yml")){
      $c = $this->getResource("config.yml");
      $o = stream_get_contents($c);
      fclose($c);
      file_put_contents($this->getDataFolder()."config.yml", str_replace("DEFAULT", $this->getServer()->getDefaultLevel()->getName(), $o));
    }
    $this->areas = [];
    $data = json_decode(file_get_contents($this->getDataFolder()."areas.json"), true);
    foreach($data as $datum){
      $area = new Area($datum["name"], $datum["flags"], $datum["pos1"], $datum["pos2"], $datum["level"], $datum["whitelist"], $this);
    }
    $this->c = yaml_parse(file_get_contents($this->getDataFolder()."config.yml"));
    if($this->c["Settings"]["Enable"] === false || $this->c["Settings"]["Enable"] !== true){
      $this->getPluginLoader()->disablePlugin($this);
    }
    $this->god = $this->c["Default"]["God"];
    $this->edit = $this->c["Default"]["Edit"];
    $this->tnt = $this->c["Default"]["TNT"];
    $this->touch = $this->c["Default"]["Touch"];
    $this->levels = [];
    foreach($this->c["Worlds"] as $level => $flags){
      $this->levels[$level] = $flags;
    }
    return true;
  }
  
  /**
   * @return void
   */
  public function onDisable(){
    $this->getLogger()->info(TF::RED."Disabling ".$this->getDescription()->getFullName()."...");
  }
  
  /**
   * Returns command prefix
   *
   * @return string
   */
  private function getPrefix() : string{
    return TF::GREEN."[iProtector:kenygamer]".TF::RESET;
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
    if(!$sender instanceof Player){
      $sender->sendMessage($this->getPrefix().TF::RED." Please run this command in-game.");
      return true;
    }
    if(!isset($args[0])){
      return false;
    }
    $n = strtolower($sender->getName());
    $action = strtolower($args[0]);
    switch($action){
      case "pos1":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.pos1")){
          if(isset($this->sel1[$n]) || isset($this->sel2[$n])){
            $o = $this->getPrefix().TF::RED." You're already selecting a position!";
          }else{
            $this->sel1[$n] = true;
            $o = $this->getPrefix().TF::AQUA." Please place or break the first position.";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      case "pos2":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.pos2")){
          if(isset($this->sel1[$n]) || isset($this->sel2[$n])){
            $o = $this->getPrefix().TF::RED." You're already selecting a position!";
          }else{
            $this->sel2[$n] = true;
            $o = $this->getPrefix().TF::AQUA." Please place or break the second position.";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      case "create":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.create")){
          if(isset($args[1])){
            if(isset($this->pos1[$n]) && isset($this->pos2[$n])){
              if(!isset($this->areas[strtolower($args[1])])){
                $area = new Area(strtolower($args[1]), [
                  "edit" => true,
                  "god" => false,
                  "tnt" => false,
                  "touch" => true
                  ], [
                  $this->pos1[$n]->getX(),
                  $this->pos1[$n]->getY(),
                  $this->pos1[$n]->getZ()
                  ], [
                  $this->pos2[$n]->getX(),
                  $this->pos2[$n]->getY(),
                  $this->pos2[$n]->getZ()
                  ], $sender->getLevel()->getName(), [
                  $n], $this);
                $this->saveAreas();
                unset($this->pos1[$n]);
                unset($this->pos2[$n]);
                $o = $this->getPrefix().TF::GREEN." Area created!";
              }else{
                $o = $this->getPrefix().TF::RED." An area with that name already exists.";
              }
            }else{
              $o = $this->getPrefix().TF::RED." Please select both positions first.";
            }
          }else{
            $o = $this->getPrefix().TF::RED." Please specify a name for this area.";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      case "here":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.here")){
          $contains = false;
          foreach($this->areas as $area){
            if($area->contains(new Vector3($sender->getX(), $sender->getY(), $sender->getZ()), $sender->getLevel()->getName())){
              $contains = true;
              $o = $this->getPrefix().TF::GREEN." You are standing on area ".$area->getName().".\n".TF::GRAY."pos1:\n".TF::GOLD."X: ".TF::BLUE.$area->getPos1()[0]."\n".TF::GOLD."Y: ".TF::BLUE.$area->getPos1()[1]."\n".TF::GOLD."Z: ".TF::BLUE.$area->getPos2()[2]."\n".TF::GRAY."pos2:\n".TF::GOLD."X: ".TF::BLUE.$area->getPos2()[0]."\n".TF::GOLD."Y: ".TF::BLUE.$area->getPos2()[1]."\n".TF::GOLD."Z: ".TF::BLUE.$area->getPos2()[2];
            }
          }
          if(!$contains){
            $o = $this->getPrefix().TF::RED." You are not standing in any area.";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      case "list":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.list")){
          $o = $this->getPrefix().TF::AQUA." Areas:".TF::GOLD;
          foreach($this->areas as $area){
            $o = $o." ".$area->getName().";";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      case "flag":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.flag")){
          if(isset($args[1])){
            if(isset($this->areas[strtolower($args[1])])){
              $area = $this->areas[strtolower($args[1])];
              if(isset($args[2])){
                if(isset($area->flags[strtolower($args[2])])){
                  $flag = strtolower($args[2]);
                  if(isset($args[3])){
                    $mode = strtolower($args[3]);
                    if($mode == "true" || $mode == "on"){
                      $mode = true;
                    }else{
                      $mode = false;
                    }
                    $area->setFlag($flag, $mode);
                  }else{
                    $area->toggleFlag($flag);
                  }
                  if($area->getFlag($flag)){
                    $status = "on";
                  }else{
                    $status = "off";
                  }
                  $o = $this->getPrefix().TF::GREEN." Flag $flag set to $status for area ".$area->getName()."!";
                }else{
                  $o = $this->getPrefix().TF::RED." Flag not found. (Flags: edit, god, tnt, touch)";
                }
              }else{
                $o = $this->getPrefix().TF::RED." Please specify a flag. (Flags: edit, god, tnt, touch";
              }
            }else{
              $o = $this->getPrefix().TF::RED." Area doesn't exist.";
            }
          }else{
            $o = $this->getPrefix().TF::RED." Please specify the area you would like to flag.";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      case "delete":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.delete")){
          if(isset($args[1])){
            if(isset($this->areas[strtolower($args[1])])){
              $area = $this->areas[strtolower($args[1])];
              $area->delete();
              $o = $this->getPrefix().TF::GREEN." Area deleted!";
            }else{
              $o = $this->getPrefix().TF::RED." Area does not exist.";
            }
          }else{
            $o = $this->getPrefix().TF::RED." Please specify an area to delete.";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      case "whitelist":
        if($sender->hasPermission("iprotector") || $sender->hasPermission("iprotector.command") || $sender->hasPermission("iprotector.command.area") || $sender->hasPermission("iprotector.command.area.whitelist")){
          if(isset($args[1]) && isset($this->areas[strtolower($args[1])])){
            $area = $this->areas[strtolower($args[1])];
            if(isset($args[2])){
              $action = strtolower($args[2]);
              switch($action){
                case "add":
                  $w = ($this->getServer()->getPlayer($args[3]) instanceof Player ? strtolower($this->getServer()->getPlayer($args[3])->getName()) : strtolower($args[3]));
                  if(!$area->isWhitelisted($w)){
                    $area->setWhitelisted($w, Area::WHITELIST_ACTION_ADD);
                    $o = $this->getPrefix().TF::GREEN." Player $w has been whitelisted in area ".$area->getName().".";
                  }else{
                    $o = $this->getPrefix().TF::RED." Player $w is already whitelisted in area ".$area->getName().".";
                  }
                  break;
                case "list":
                  $o = $this->getPrefix().TF::AQUA.$area->getName()."'s whitelist:\n";
                  foreach($area->getWhitelist() as $w){
                    $o .= " $w;";
                  }
                  break;
                case "delete":
                case "remove":
                  $w = ($this->getServer()->getPlayer($args[3]) instanceof Player ? strtolower($this->getServer()->getPlayer($args[3])->getName()) : strtolower($args[3]));
                  if($area->isWhitelisted($w)){
                    $area->setWhitelisted($w, Area::WHITELIST_ACTION_REMOVE);
                    $o = $this->getPrefix().TF::GREEN." Player $w has been unwhitelisted in area ".$area->getName().".";
                  }else{
                    $o = $this->getPrefix().TF::RED." $w is already unwhitelisted in area ".$area->getName().".";
                  }
                  break;
                default:
                  $o = $this->getPrefix().TF::RED." Please specify a valid action. Usage: /area whitelist ".$area->getName()." <add/list/remove> [player]";
                  break;
              }
            }else{
              $o = $this->getPrefix().TF::RED." Please specify an action. Usage: /area whitelist ".$area->getName()." <add/list/remove> [player]";
            }
          }else{
            $o = $this->getPrefix().TF::RED." Area doesn't exist. Usage: /area whitelist <area> <add/list/remove> [player]";
          }
        }else{
          $o = $this->getPrefix().TF::RED." You do not have permission to use this subcommand.";
        }
        break;
      default:
        return false;
        break;
    }
    $sender->sendMessage($o);
    return true;
  }
  
  /**
   * @param EntityDamageEvent
   *
   * @return void
   */
  public function onHurt(EntityDamageEvent $event){
    if($event->getEntity() instanceof Player){
      $player = $event->getEntity();
      if(!$this->canGetHurt($player)){
        if($this->c["Messages"]["Hurt"]["Enable"]){
          $player->sendMessage(str_replace("{player}", $player->getName(), $this->c["Messages"]["Hurt"]["Message"]));
        }
        $event->setCancelled();
      }
    }
  }
  
  /**
   * @param BlockBreakEvent $event
   *
   * @return void
   */
  public function onBlockBreak(BlockBreakEvent $event){
    $player = $event->getPlayer();
    $block = $event->getBlock();
    $n = strtolower($player->getName());
    if(isset($this->sel1[$n])){
      unset($this->sel1[$n]);
      $this->pos1[$n] = new Vector3($block->getX(), $block->getY(), $block->getZ());
      $player->sendMessage($this->getPrefix().TF::GREEN." Position 1 set to: (".$this->pos1[$n]->getX().", ".$this->pos1[$n]->getY().", ".$this->pos1[$n]->getZ().")");
      $event->setCancelled();
    }elseif(isset($this->sel2[$n])){
      unset($this->sel2[$n]);
      $this->pos2[$n] = new Vector3($block->getX(), $block->getY(), $block->getZ());
      $player->sendMessage($this->getPrefix().TF::GREEN." Position 2 set to: (".$this->pos2[$n]->getX().", ".$this->pos2[$n]->getY().", ".$this->pos2[$n]->getZ().")");
      $event->setCancelled();
    }else{
      if(!$this->canEdit($player, $block)){
        if($this->c["Messages"]["Break"]["Enable"]){
          $player->sendMessage(str_replace("{block}", $block->getName(), $this->c["Messages"]["Break"]["Message"]));
        }
        $event->setCancelled();
      }
    }
  }
  
  /**
   * @param BlockPlaceEvent $event
   *
   * @return bool
   */
  public function onBlockPlace(BlockPlaceEvent $event){
    $player = $event->getPlayer();
    $block = $event->getBlock();
    $n = strtolower($player->getName());
    if(isset($this->sel1[$n])){
      unset($this->sel1[$n]);
      $this->pos1[$n] = new Vector3($block->getX(), $block->getY(), $block->getZ());
      $player->sendMessage($this->getPrefix().TF::GREEN." Position 1 set to: (".$this->pos1[$n]->getX().", ".$this->pos1[$n]->getY().", ".$this->pos1[$n]->getZ().")");
      $event->setCancelled();
    }elseif(isset($this->sel2[$n])){
      unset($this->sel2[$n]);
      $this->pos2[$n] = new Vector3($block->getX(), $block->getY(), $block->getZ());
      $player->sendMessage($this->getPrefix().TF::GREEN." Position 2 set to: (".$this->pos2[$n]->getX().", ".$this->pos2[$n]->getY().", ".$this->pos2[$n]->getZ().")");
      $event->setCancelled();
    }else{
      if(!$this->canEdit($player, $block)){
        if($this->c["Messages"]["Place"]["Enable"]){
          $player->sendMessage(str_replace("{block}", $block->getName(), $this->c["Messages"]["Place"]["Message"]));
        }
        $event->setCancelled();
      }
    }
  }
  
  /**
   * @param PlayerInteractEvent $event
   *
   * @return void
   */
  public function onBlockTouch(PlayerInteractEvent $event){
    $player = $event->getPlayer();
    $block = $event->getBlock();
    if(!$this->canTouch($player, $block)){
      if($this->c["Messages"]["Touch"]["Enable"]){
        $player->sendMessage(str_replace("{block}", $block->getName(), $this->c["Messages"]["Touch"]["Message"]));
      }
      $event->setCancelled();
    }
  }
  
  /**
   * @param EntityExplodeEvent $event
   *
   * @return void
   */
  public function onEntityExplode(EntityExplodeEvent $event){
    $entity = $event->getEntity(); //primed tnt is counted as an entity.
    $position = $event->getPosition();
    if(!$this->canExplode($position, $entity->getLevel())){
      $event->setCancelled();
    }
  }
  
  /**
   * @param DataPacketReceiveEvent $event
   *
   * @return void
   */
  public function onItemFrameDropPacket(DataPacketReceiveEvent $event){
    $packet = $event->getPacket();
    if($packet instanceof ItemFrameDropItemPacket){
      $player = $event->getPlayer();
      if(!$this->canDropItemFrame($player, new Vector3($packet->x, $packet->y, $packet->z))){
        $event->setCancelled();
      }
    }
  }
  
  /**
   * Checks if given player can edit area
   *
   * @param Player $player
   * @param Block $block
   *
   * @return bool
   */
  public function canEdit(Player $player, Block $block) : bool{
    if($player->hasPermission("iprotector") || $player->hasPermission("iprotector.access")){
      return true;
    }
    $o = true;
    $g = (isset($this->levels[$block->getLevel()->getName()]) ? $this->levels[$block->getLevel()->getName()]["Edit"] : $this->edit);
    if ($g){
      $o = false;
    }
    foreach($this->areas as $area){
      if($area->contains(new Vector3($block->getX(), $block->getY(), $block->getZ()), $block->getLevel()->getName())){
        if($area->getFlag("edit")){
          $o = false;
        }
        if($area->isWhitelisted(strtolower($player->getName()))){
          $o = true;
          break;
        }
        if(!$area->getFlag("edit") && $g){
          $o = true;
          break;
        }
      }
    }
  return $o;
  }
  
  /**
   * Checks if given player can touch area
   *
   * @param Player $player
   * @param Block $block
   *
   * @return bool
   */
  public function canTouch(Player $player, Block $block) : bool{
    if($player->hasPermission("iprotector") || $player->hasPermission("iprotector.access")){
      return true;
    }
    $o = true;
    $g = (isset($this->levels[$block->getLevel()->getName()]) ? $this->levels[$block->getLevel()->getName()]["Touch"] : $this->touch);
    if ($g){
      $o = false;
    }
    foreach($this->areas as $area){
      if($area->contains(new Vector3($block->getX(), $block->getY(), $block->getZ()), $block->getLevel()->getName())){
        if($area->getFlag("touch")){
          $o = false;
        }
        if($area->isWhitelisted(strtolower($player->getName()))){
          $o = true;
          break;
        }
        if(!$area->getFlag("edit") && $g){
          $o = true;
          break;
        }
      }
    }
  return $o;
  }
  
  /**
   * Checks if given player can drop item frame
   *
   * @param Player $player
   * @param Vector3 $position
   *
   * @return bool
   */
  public function canDropItemFrame(Player $player, Vector3 $position){
    if($player->hasPermission("iprotector") || $player->hasPermission("iprotector.access")){
      return true;
    }
    $o = true;
    $g = (isset($this->levels[$player->getLevel()->getName()]) ? $this->levels[$player->getLevel()->getName()]["Touch"] : $this->touch);
    if ($g){
      $o = false;
    }
    foreach($this->areas as $area){
      if($area->contains($position, $player->getLevel()->getName())){
        if($area->getFlag("touch")){
          $o = false;
        }
        if($area->isWhitelisted(strtolower($player->getName()))){
          $o = true;
          break;
        }
        if(!$area->getFlag("edit") && $g){
          $o = true;
          break;
        }
      }
    }
  return $o;
  }
  
  /**
   * Checks if given player can get hurt on area
   *
   * @param Player $player
   *
   * @return bool
   */
  public function canGetHurt(Player $player) : bool{
    $o = true;
    $g = (isset($this->levels[$player->getLevel()->getName()]) ? $this->levels[$player->getLevel()->getName()]["God"] : $this->god);
    if($g){
      $o = false;
    }
    foreach($this->areas as $area){
      if($area->contains(new Vector3($player->getX(), $player->getY(), $player->getZ()), $player->getLevel()->getName())){
        if(!$area->getFlag("god") && $g){
          $o = true;
          break;
        }
        if($area->getFlag("god")){
          $o = false;
        }
      }
    }
    return $o;
  }
  
  /**
   * Checks if given entity can explode area
   *
   * @param Position $position
   * @param Level $level
   *
   * @return bool
   */
  public function canExplode(Position $position, Level $level) : bool{
    $o = true;
    $g = (isset($this->levels[$level->getName()]) ? $this->levels[$level->getName()]["TNT"] : $this->tnt);
    if($g){
      $o = false;
    }
    foreach($this->areas as $area){
      if($area->contains(new Vector3($position->getX(), $position->getY(), $position->getZ()), $level->getName())){
        if($area->getFlag("tnt")){
          $o = false;
          break;
        }
        if($area->getFlag("tnt") && $g){
          $o = true;
          break;
        }
      }
    }
    return $o;
  }
  
  /**
   * Saves areas to file
   *
   * @return void
   */
  public function saveAreas(){
    $areas = [];
    foreach($this->areas as $area){
      $areas[] = [
        "name" => $area->getName(),
        "flags" => $area->getFlags(),
        "pos1" => $area->getPos1(),
        "pos2" => $area->getPos2(),
        "level" => $area->getLevel(),
        "whitelist" => $area->getWhitelist()
        ];
    }
    $jpp = (bool) $this->c["Settings"]["JPP"];
    if($jpp){
      file_put_contents($this->getDataFolder()."areas.json", json_encode($areas, JSON_PRETTY_PRINT));
    }else{
      file_put_contents($this->getDataFolder()."areas.json", json_encode($areas));
    }
  }
  
}
