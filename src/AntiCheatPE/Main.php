<?php

namespace AntiCheatPE;

use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

    public $isElevating = [];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
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

}