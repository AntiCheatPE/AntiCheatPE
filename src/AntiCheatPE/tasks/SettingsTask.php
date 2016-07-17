<?php

namespace AntiCheatPE\tasks;

use pocketmine\scheduler\PluginTask;
use AntiCheatPE\Main;

class SettingsTask extends PluginTask{
  
  private $antiCheat;
  
  public function __construct(Main $antiCheat){
    parent::__construct($antiCheat);
    $this->antiCheat = $antiCheat;
  }
  
  public function onRun($currentTick){
    foreach($this->antiCheat->getServer()->getOnlinePlayers() as $player){
      $player->sendSettings();
    }
  }
  
}
