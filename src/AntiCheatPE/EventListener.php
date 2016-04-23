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

        if($p->isCreative() or $p->isSpectator() or $p->getAllowFlight() or $p->hasEffect(8)) return;

        if(!Main::isAirUnder($p) and isset($this->antiCheat->isElevating[$p->getId()])) unset($this->antiCheat->isElevating[$p->getId()]);

        $fromY = $event->getFrom()->y;
        $toY = $event->getTo()->y;
        $name = $event->getPlayer()->getName();

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
                $this->antiCheat->isElevating[$p->getId()] > 1.5
            ){
                $this->antiCheat->players[$name] ++;
            }else{
                $this->antiCheat->players[$name] = 0;
            }
        }

        elseif(
            round($fromY, 5) === round($toY, 5) and
            Main::isAirUnder($p)
        ){
            $this->antiCheat->players[$name] ++;
        }else{
            $this->antiCheat->players[$name] = 0;
        }

        if($this->antiCheat->players[$name] === 3){
            $event->getPlayer()->kick(TextFormat::RED . "[AntiCheat] " . TextFormat::YELLOW . "You were kicked for using mods/hacks. Please disable them to play on this server!", false);
            $this->antiCheat->kicks[$name] ++;
        }

        if($this->antiCheat->kicks[$name] === 3){
            $this->antiCheat->getServer()->getIPBans()->addBan($p->getAddress());
        }
    }

}
