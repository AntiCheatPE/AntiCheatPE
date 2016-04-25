<?php

namespace AntiCheatPE;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
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

        if($p->isCreative() or $p->isSpectator() or $p->getAllowFlight() or $p->hasEffect(8) or $p->hasPermission("anticheat.admin")) return;

        if(!Main::isAirUnder($p) and isset($this->antiCheat->isElevating[$p->getId()])){
            unset($this->antiCheat->isElevating[$p->getId()]);
        }

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
                $this->antiCheat->players[$name]++;
            }else{
                $this->antiCheat->players[$name] = 0;
            }
        }

        elseif(
            round($fromY, 5) === round($toY, 5) and
            Main::isAirUnder($p)
        ){
            $this->antiCheat->players[$name]++;
        }else{
            $this->antiCheat->players[$name] = 0;
        }

        if(isset($this->antiCheat->players[$name]) and $this->antiCheat->players[$name] === $this->antiCheat->options["tags"]){
            unset($this->antiCheat->players[$name]);
            $this->antiCheat->kicks[$name] ++;
            $event->getPlayer()->kick($this->antiCheat->options["kick message"], false);
            return;
        }

        if(isset($this->antiCheat->kicks[$name]) and $this->antiCheat->kicks[$name] === $this->antiCheat->options["kicks"]){
            unset($this->antiCheat->kicks[$name]);
            switch($this->antiCheat->options["Action"]){
                case "ban-ip":
                    $this->antiCheat->getServer()->getIPBans()->addBan($event->getPlayer()->getAddress());
                    $event->getPlayer()->kick(TextFormat::RED . "[AntiCheat] " . TextFormat::YELLOW . "You were banned for using mods/hacks.", false);
                    break;
                case "ban":
                    $this->antiCheat->getServer()->getNameBans()->addBan($event->getPlayer()->getName());
                    $event->getPlayer()->kick(TextFormat::RED . "[AntiCheat] " . TextFormat::YELLOW . "You were banned for using mods/hacks.", false);
                    break;
                case "ban-client":
                    if(($banclientplugin = $this->antiCheat->getServer()->getPluginManager()->getPlugin("BanClient")) !== null){
                        $banclientplugin->banClient($event->getPlayer(), TextFormat::RED . "[AntiCheat] " . TextFormat::YELLOW . "You were banned for using mods/hacks.", false, true);
                    }else{
                        $this->antiCheat->getServer()->getLogger()->warning("[AntiCheat] BanClient plugin not found!");
                    }
                    break;
                case "custom":
                    foreach($this->antiCheat->options["Commands"] as $commands){
                        $this->antiCheat->getServer()->dispatchCommand(new ConsoleCommandSender(), "$commands");
                    }
            }
            return;
        }

        if(!$p->hasEffect(Effect::SPEED) or !$p->hasPermission("anticheat.admin")){
            if (Main::XZDistanceSquared($event->getFrom(), $event->getTo()) > 1.4) {
                $this->antiCheat->speedpoints[$name]++;
            }elseif(Main::XZDistanceSquared($event->getFrom(), $event->getTo()) > 3){
                $this->antiCheat->speedpoints[$name] += 2;
            }
        }

        if(isset($this->antiCheat->speedpoints[$name]) and $this->antiCheat->speedpoints[$name] === $this->antiCheat->options["points"]){
            unset($this->antiCheat->speedpoints[$name]);
            $this->antiCheat->kicks[$name] ++;
            $event->getPlayer()->kick(TextFormat::RED . "[AntiCheat] " . TextFormat::YELLOW . "You were kicked for using mods/hacks. Please disable them to play on this server!", false);
            return;
        }

        if(isset($this->antiCheat->kicks[$name]) and $this->antiCheat->kicks[$name] === $this->antiCheat->options["kicks"]){
            switch($this->antiCheat->options["Action"]){
                case "ban-ip":
                    $this->antiCheat->getServer()->getIPBans()->addBan($event->getPlayer()->getAddress());
                    $event->getPlayer()->kick(TextFormat::RED . "[AntiCheat] " . TextFormat::YELLOW . "You were banned for using mods/hacks.", false);
                    break;
                case "ban":
                    $this->antiCheat->getServer()->getNameBans()->addBan($event->getPlayer()->getName());
                    $event->getPlayer()->kick(TextFormat::RED . "[AntiCheat] " . TextFormat::YELLOW . "You were banned for using mods/hacks.", false);
                    break;
                case "ban-client":
                    if(($banclientplugin = $this->antiCheat->getServer()->getPluginManager()->getPlugin("BanClient")) !== null){
                        $banclientplugin->banClient($event->getPlayer(), TextFormat::RED . "[AntiCheat] " . TextFormat::YELLOW . "You were banned for using mods/hacks.", false, true);
                    }else{
                        $this->antiCheat->getServer()->getLogger()->warning("[AntiCheat] BanClient plugin not found!");
                    }
                    break;
                case "custom":
                    foreach($this->antiCheat->options["Commands"] as $commands){
                        $this->antiCheat->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $event->getPlayer(), $commands));
                    }
            }
        }
    }
}
