<?php

namespace AntiCheatPE;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\utils\TextFormat;

class EventListener implements Listener{

    private $antiCheat;

    public function __construct(Main $antiCheat){
        $this->antiCheat = $antiCheat;
    }

    public function onMove(PlayerMoveEvent $event){
        $p = $event->getPlayer();

        //if($p->isCreative() or $p->isAdventure()) return;

        if(!Main::isAirUnder($p) and isset($this->antiCheat->isElevating[$p->getId()])) unset($this->antiCheat->isElevating[$p->getId()]);

        $fromY = $event->getFrom()->y;
        $toY = $event->getTo()->y;

        if($toY < $fromY and isset($this->antiCheat->isElevating[$p->getId()])){
            $this->antiCheat->isElevating[$p->getId()] -= $fromY - $toY;
            if($this->antiCheat->isElevating[$p->getId()] <= 0){
                unset($this->antiCheat->isElevating[$p->getId()]);
            }
        }

        elseif($toY > $fromY){
            isset($this->antiCheat->isElevating[$p->getId()]) ?
                $this->antiCheat->isElevating[$p->getId()] += $toY - $fromY
                :
                $this->antiCheat->isElevating[$p->getId()] = $toY - $fromY
            ;

            if(
                Main::isAirUnder($p) and
                $this->antiCheat->isElevating[$p->getId()] > 1.3
            ){
                $p->sendMessage(TextFormat::RED."Hax <3");
            }
        }

        elseif(
            round($fromY, 5) === round($toY, 5) and
            Main::isAirUnder($p)
        ){
            $p->sendMessage(TextFormat::RED."Hax <3");
        }
    }

}