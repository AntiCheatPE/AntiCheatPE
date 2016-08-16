<?php

namespace AntiCheatPE;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\Player;

class EventListener implements Listener{

    private $antiCheat;
    private $isElevating = [];
    private $flyTags = [];
    private $kicks = [];
    private $speedpoints = [];

    public function __construct(Main $antiCheat){
        $this->antiCheat = $antiCheat;
    }

    public function onMove(PlayerMoveEvent $event){
        $p = $event->getPlayer();

        if($p->isCreative() or $p->isSpectator() or $p->getAllowFlight() or $p->hasEffect(Effect::JUMP) or ($this->antiCheat->combatLogger !== null and isset($this->antiCheat->combatLogger->tasks[$p->getName()])) or $p->hasPermission("anticheat.admin")) return;

        $name = $p->getName();
        $isAirUnder = Main::isAirUnder($p);

        if(!$isAirUnder and isset($this->isElevating[$name])){
            unset($this->isElevating[$name]);
        }else{
            $fromY = $event->getFrom()->y;
            $toY = $event->getTo()->y;

            if($toY < $fromY and isset($this->isElevating[$name])){
                $this->isElevating[$name] -= $fromY - $toY;
                if($this->isElevating[$name] <= 0){
                    unset($this->isElevating[$name]);
                }
            }

            elseif($toY > $fromY){
                isset($this->isElevating[$name]) ?
                    $this->isElevating[$name] += $toY - $fromY
                    :
                    $this->isElevating[$name] = $toY - $fromY
                ;

                if(
                    $isAirUnder and
                    $this->isElevating[$name] > 1.5
                ){
                    $this->antiCheat->options["tags"] !== -1 and ++$this->flyTags[$name];
                    $p->sendSettings();
                }
            }

            elseif(
                round($fromY, 5) === round($toY, 5) and
                $isAirUnder
            ){
                $this->antiCheat->options["tags"] !== -1 and ++$this->flyTags[$name];
                $p->sendSettings();
            }

            if(isset($this->flyTags[$name]) and $this->flyTags[$name] === $this->antiCheat->options["tags"]){
                if((isset($this->kicks[$name]) and $this->kicks[$name] < $this->antiCheat->options["kicks"] - 1) or !isset($this->kicks[$name])){
                    unset($this->flyTags[$name]);
                    ++$this->kicks[$name];
                    $event->getPlayer()->kick($this->antiCheat->options["kick message"], false);
                }else{
                    unset($this->kicks[$name]);
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

        if($this->antiCheat->options["points"] === -1 or $p->hasEffect(Effect::SPEED) or $p->hasPermission("anticheat.admin")) return;

        if(($d = Main::XZDistanceSquared($event->getFrom(), $event->getTo())) > 1.4){
            ++$this->speedpoints[$name];
        }elseif($d > 3){
            $this->speedpoints[$name] += 2;
        }elseif($d > 0){
            $this->speedpoints[$name] -= 1;
        }

        if(isset($this->speedpoints[$name]) and $this->speedpoints[$name] === $this->antiCheat->options["points"]){
            if((isset($this->kicks[$name]) and $this->kicks[$name] < $this->antiCheat->options["kicks"] - 1) or !isset($this->kicks[$name])){
                unset($this->speedpoints[$name]);
                ++$this->kicks[$name];
                $event->getPlayer()->kick($this->antiCheat->options["kick message"], false);
            }else{
                unset($this->kicks[$name]);
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

    public function onDamage(EntityDamageEvent $event){
        if($event instanceof EntityDamageByEntityEvent and $event->getEntity() instanceof Player and $event->getDamager() instanceof Player){
            if($event->getEntity()->distanceSquared($event->getDamager()) > $this->antiCheat->options["max-hit-distance"]){
                $event->setCancelled();
            }
        }
    }
  
}
