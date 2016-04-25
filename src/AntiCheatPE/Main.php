<?php

namespace AntiCheatPE;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{

    public $isElevating = [];
    public $players = [];
    public $kicks = [];
    public $options;
    public $speedpoints = [];

    public function onEnable(){
        $this->getLogger()->info(TextFormat::GREEN . "AntiCheatPE successfully enabled!");
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->saveDefaultConfig();
        $this->options = $this->getConfig()->getAll();
        if(!is_int($this->options["tags"]) or !is_int($this->options["kicks"]) or !is_int($this->options["points"])){
            $this->getLogger()->critical(TextFormat::RED . "Config file error: tags, kicks and points must be numerical! Disabling AntiCheatPE...");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    public static function isAirUnder(Position $pos) : bool{
        $under = [];
        $last = [];
        $y = floor($pos->y) - 1;

        $under[] = $pos->level->getBlockIdAt(floor($pos->x), $y, floor($pos->z));

        if(round($pos->x) === floor($pos->x)){
            $under[] = $pos->level->getBlockIdAt(floor($pos->x) - 1, $y, floor($pos->z));
            $last[0] = floor($pos->x) - 1;
        }elseif(round($pos->x) === ceil($pos->x)){
            $under[] = $pos->level->getBlockIdAt(ceil($pos->x), $y, floor($pos->z));
            $last[0] = ceil($pos->x);
        }

        if(round($pos->z) === floor($pos->z)){
            $under[] = $pos->level->getBlockIdAt(floor($pos->x), $y, floor($pos->z) - 1);
            $last[1] = floor($pos->z) - 1;
        }elseif(round($pos->z) === ceil($pos->z)){
            $under[] = $pos->level->getBlockIdAt(floor($pos->x), $y, ceil($pos->z));
            $last[1] = ceil($pos->z);
        }

        $under[] = $pos->level->getBlockIdAt($last[0], $y, $last[1]);

        return !array_filter($under);
    }
    
    public static function XZDistanceSquared(Vector3 $v1, Vector3 $v2){
        return ($v1->x - $v2->x) ** 2 + ($v1->z - $v2->z) ** 2;
    }

}
