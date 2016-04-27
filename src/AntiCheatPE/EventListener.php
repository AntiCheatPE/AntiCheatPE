<?php

namespace AntiCheatPE;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

class EventListener implements Listener{

    private $antiCheat;

    public function __construct(Main $antiCheat){
        $this->antiCheat = $antiCheat;
    }

    public function onMove(PlayerMoveEvent $event){
        $p = $event->getPlayer();
        $name = $event->getPlayer()->getName();

        if($p->isCreative() or $p->isSpectator() or $p->getAllowFlight() or $p->hasEffect(8) or ($this->antiCheat->combatLogger !== null and isset($this->antiCheat->combatLogger->tasks[$p->getName()])) or $p->hasPermission("anticheat.admin")) return;

        $isAirUnder = Main::isAirUnder($p);

        if(!$isAirUnder and isset($this->antiCheat->isElevating[$name])){
            unset($this->antiCheat->isElevating[$name]);
        }else{
            $fromY = $event->getFrom()->y;
            $toY = $event->getTo()->y;

            if($toY < $fromY and isset($this->antiCheat->isElevating[$name])){
                $this->antiCheat->isElevating[$name] -= $fromY - $toY;
                if($this->antiCheat->isElevating[$name] <= 0){
                    unset($this->antiCheat->isElevating[$name]);
                }
            }

            elseif($toY > $fromY){
                isset($this->antiCheat->isElevating[$name]) ?
                    $this->antiCheat->isElevating[$name] += $toY - $fromY
                    :
                    $this->antiCheat->isElevating[$name] = $toY - $fromY
                ;

                if(
                    $isAirUnder and
                    $this->antiCheat->isElevating[$name] > 1.5
                ){
                    $this->antiCheat->players[$name]++;
                }else{
                    $this->antiCheat->players[$name] = 0;
                }
            }

            elseif(
                round($fromY, 5) === round($toY, 5) and
                $isAirUnder
            ){
                $this->antiCheat->players[$name]++;
            }else{
                $this->antiCheat->players[$name] = 0;
            }

            if(isset($this->antiCheat->players[$name]) and $this->antiCheat->players[$name] === $this->antiCheat->options["tags"]){
                if((isset($this->antiCheat->kicks[$name]) and $this->antiCheat->kicks[$name] < $this->antiCheat->options["kicks"] - 1) or !isset($this->antiCheat->kicks[$name])){
                    unset($this->antiCheat->players[$name]);
                    ++$this->antiCheat->kicks[$name];
                    $event->getPlayer()->kick($this->antiCheat->options["kick message"], false);
                }else{
                    unset($this->antiCheat->kicks[$name]);
                    switch($this->antiCheat->options["Action"]){
                        case "ban-ip":
                            $this->antiCheat->getServer()->getIPBans()->addBan($event->getPlayer()->getAddress());
                            $event->getPlayer()->kick($this->antiCheat->options["ban message"], false);
                            break;
                        case "ban":
                            $this->antiCheat->getServer()->getNameBans()->addBan($event->getPlayer()->getName());
                            $event->getPlayer()->kick($this->antiCheat->options["ban message"], false);
                            break;
                        case "ban-client":
                            if(($banclientplugin = $this->antiCheat->getServer()->getPluginManager()->getPlugin("BanClient")) !== null){
                                $banclientplugin->banClient($event->getPlayer(), $this->antiCheat->options["ban message"], false, true);
                            }else{
                                $this->antiCheat->getServer()->getLogger()->warning("[AntiCheat] BanClient plugin not found!");
                            }
                            break;
                        case "custom":
                            foreach($this->antiCheat->options["Commands"] as $commands){
                                $this->antiCheat->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $event->getPlayer()->getName(), $commands));
                            }
                    }
                }
                return;
            }
        }

        if($p->hasEffect(Effect::SPEED) or $p->hasPermission("anticheat.admin")) return;

        if(($d = Main::XZDistanceSquared($event->getFrom(), $event->getTo())) > 1.4){
            $this->antiCheat->speedpoints[$name]++;
        }elseif($d > 3){
            $this->antiCheat->speedpoints[$name] += 2;
        }elseif($d > 0){
            $this->antiCheat->speedpoints[$name] -= 1;
        }

        if(isset($this->antiCheat->speedpoints[$name]) and $this->antiCheat->speedpoints[$name] === $this->antiCheat->options["points"]){
            if((isset($this->antiCheat->kicks[$name]) and $this->antiCheat->kicks[$name] < $this->antiCheat->options["kicks"] - 1) or !isset($this->antiCheat->kicks[$name])){
                unset($this->antiCheat->speedpoints[$name]);
                $this->antiCheat->kicks[$name]++;
                $event->getPlayer()->kick($this->antiCheat->options["kick message"], false);
            }else{
                unset($this->antiCheat->kicks[$name]);
                switch($this->antiCheat->options["Action"]){
                    case "ban-ip":
                        $this->antiCheat->getServer()->getIPBans()->addBan($event->getPlayer()->getAddress());
                        $event->getPlayer()->kick($this->antiCheat->options["ban message"], false);
                        break;
                    case "ban":
                        $this->antiCheat->getServer()->getNameBans()->addBan($event->getPlayer()->getName());
                        $event->getPlayer()->kick($this->antiCheat->options["ban message"], false);
                        break;
                    case "ban-client":
                        if(($banclientplugin = $this->antiCheat->getServer()->getPluginManager()->getPlugin("BanClient")) !== null){
                            $banclientplugin->banClient($event->getPlayer(), $this->antiCheat->options["ban message"], false, true);
                        }else{
                            $this->antiCheat->getServer()->getLogger()->warning("[AntiCheat] BanClient plugin not found!");
                        }
                        break;
                    case "custom":
                        foreach($this->antiCheat->options["Commands"] as $commands){
                            $this->antiCheat->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $event->getPlayer()->getName(), $commands));
                        }
                }
            }
            return;
        }
    }
}
