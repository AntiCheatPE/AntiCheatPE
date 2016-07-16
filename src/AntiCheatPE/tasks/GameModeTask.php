<?php

namespace AntiCheatPE\tasks;

use pocketmine\scheduler\PluginTask;
use AntiCheatPE\Main;

class GameModeTask extends PluginTask{
  
  private $antiCheat;
  
  public function __construct(Main $antiCheat){
    parent::__construct($antiCheat);
    $this->antiCheat = $antiCheat;
  }
  
  public function onRun($currentTick){
    foreach($this->antiCheat->getServer()->getOnlinePlayers() as $player){
      if($player->getGamemode() == $this->antiCheat->getConfig()->get("gamemode")){
        return;
      }
      if(Level.getGameMode() == $this->antiCheat->getConfig()->get("gamemode")){
        return;
      }elseif(Level.getGameMode() !== $this->antiCheat->getConfig()->get("gamemode")){
        if($this->antiCheat->getConfig()->get("gamemode-action") == "kick"){
          $player->kick($this->antiCheat->getConfig()->get("gamemode-message"));
        }elseif($this->antiCheat->getConfig()->get("gamemode-action") == "ban"){
          $this->antiCheat->getServer()->getNameBans()->addBan($player->getName(), $this->antiCheat->getConfig()->get("gamemode-message")); 
        }elseif($this->antiCheat->getConfig()->get("gamemode-action") == "ban-ip"){
          $this->antiCheat->getServer()->getIPBans()->addBan($player->getAddress(), $this->antiCheat()->getConfig()->get("gamemode-message"));
        }
      }else{
        return;
      }
    }
  }
  
}
