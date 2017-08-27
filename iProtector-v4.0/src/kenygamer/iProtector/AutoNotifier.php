<?php

namespace kenygamer\iProtector;

use pocketmine\utils\Utils;

class AutoNotifier{
  
  /** @var Main */
  private $plugin;
  /** @var string */
  private $name;
  /** @var string */
  private $version;
  /** @var array */
  private $releases;
  
  public function __construct(Main $plugin, string $name, string $version){
    $this->plugin = $plugin;
    $this->name = $name;
    $this->version = $version;
    $releases = Utils::getURL("https://raw.githubusercontent.com/kenygamer/pmmp-plugins/master/".$name."/releases.json");
    if($releases === false){
      $plugin->getLogger()->error("[AutoNotifier] Plugin not found");
      return;
    }
    $releases = json_decode($releases, true);
    if(json_last_error() !== JSON_ERROR_NONE){
      $plugin->getLogger()->error("[AutoNotifier] An error occurred while parsing plugin releases");
      return;
    }
    foreach($releases as $release){
      $releases[] = $release;
    }
    $this->releases = $releases;
    $this->check();
  }
  
  /**
   * Checks version status
   *
   * @return void
   */
  private function check(){
    if(!$this->isOutdated()){
      $this->plugin->getLogger()->warning("[AutoNotifier] ".$this->name." is up to date");
      return;
    }
    $last = end($this->releases);
    $this->plugin->getLogger()->warning("[AutoNotifier] There's a new version of ".$this->name." available (v".$last["version"].")");
    $this->plugin->getLogger()->warning("Features:");
    foreach($last["features"] as $feature){
      $this->plugin->getLogger()->warning("- ".$feature);
    }
    $this->plugin->getLogger()->warning("Consider upgrading now: https://kenygamer.com/update-plugin.php");
  }
  
  /**
   * Checks if current version is outdated
   *
   * @return bool
   */
  private function isOutdated() : bool{
    if(version_compare($this->version, end($this->releases)["version"]) === -1){
      return true;
    }
    return false;
  }
  
}
