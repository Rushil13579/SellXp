<?php

namespace Rushil13579\SellXp;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;

class SellXp extends PluginBase {

    public function onEnable(){
        $this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $this->saveDefaultConfig();
        $this->getResource("config.yml");
    }

    public function onCommand(CommandSender $s, Command $cmd, String $label, Array $args) : bool {

        switch($cmd->getName()){
            case "sellxp":
                if($s instanceof Player){
                    if(!isset($args[0])){
                        if($this->getConfig()->get("form-support") == true) {
                            if ($this->getServer()->getPluginManager()->getPlugin("FormAPI") == null) {
                                $s->sendMessage(C::RED . "Usage: /sellxp [amount]");
                            } else {
                                $this->SellXpForm($s);
                            }
                        } else {
                            $s->sendMessage(C::RED . "Usage: /sellxp [amount]");
                        }
                    } else {
                            $amount = $args[0];
                            if(is_numeric($args[0])) {
                                $amount = round($amount);
                                if ($s->getXpLevel() >= $amount) {
                                    $s->subtractXpLevels((int) $amount);
                                    EconomyAPI::getInstance()->addMoney($s, $amount * $this->getConfig()->get("amount-per-xp"));
                                    $format = str_replace(["{xp}", "{amount}"], [$amount, $amount * $this->getConfig()->get("amount-per-xp")], $this->getConfig()->get("sellxp-msg"));
                                    $s->sendMessage($format);
                            } else {
                                    $s->sendMessage($this->getConfig()->get("not-enough-xp-msg"));
                                }
                            } else {
                                $s->sendMessage($this->getConfig()->get("non-numeric-argument"));
                            }
                    }
                } else {
                    $s->sendMessage(C::RED . "Please use this command in-game");
                }
        }
        return true;
    }

    public function SellXpForm($player){
        $form = $this->formapi->createCustomForm(function (Player $player, $data){
            if($data !== null){
                $amount = $data[0];
                if(is_numeric($amount)){
                    $amount = round($amount);
                    if($player->getXpLevel() >= $amount){
                        $player->subtractXpLevels((int) $amount);
                        EconomyAPI::getInstance()->addMoney($player, $amount * $this->getConfig()->get("amount-per-xp"));
                        $format = str_replace(["{xp}", "{amount}"], [$amount, $amount * $this->getConfig()->get("amount-per-xp")], $this->getConfig()->get("sellxp-msg"));
                        $player->sendMessage($format);
                    } else {
                        $player->sendMessage($this->getConfig()->get("not-enough-xp-msg"));
                    }
                } else {
                    $player->sendMessage($this->getConfig()->get("non-numeric-argument"));
                }
            } else {
                $player->sendMessage($this->getConfig()->get("no-amount-given"));
            }
        });
        $form->setTitle("§l§9§k||§r§l§cSellXp§9§k||§r");
        $form->addLabel("§eSell Your Xp!\n§cCurrent Xp-Money Exchange Price: 1 XpLevel = " . $this->getConfig()->get("amount-per-xp") . "$");
        $form->addInput("§bEnter The Amount Of Xp To Sell Here!");
        $form->sendToPlayer($player);
        return $form;
    }
}