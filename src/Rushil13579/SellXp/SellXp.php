<?php

namespace Rushil13579\SellXp;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use onebone\economyapi\EconomyAPI;
use jojoe77777\FormAPI\FormAPI;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;

class SellXp extends PluginBase {

    public function onEnable(){
        $this->saveDefaultConfig();
        $this->getResource("config.yml");
    }

    public function onCommand(CommandSender $s, Command $cmd, String $label, Array $args) : bool {

        switch($cmd->getName()){
            case "sellxp":
            if($s instanceof Player){
                if(!isset($args[0])){
                    if($this->getConfig()->get("formapi-support") == true && $this->getServer()->getPluginManager()->getPlugin("FormAPI") !== null){
                      if($s->getXpLevel() > 0){
                          $this->sellXp($s);
                      } else {
                        $s->sendMessage($this->getConfig()->get("not-enough-xp-msg"));
                      }
                    } else {
                      $s->sendMessage(C::RED . "Usage: /sellxp [amount]");
                    }
                } else {
                    $amount = $args[0];
                    if(is_numeric($args[0])) {
                        if($amount == round($amount)){
                            if ($s->getXpLevel() >= $amount) {
                                $s->subtractXpLevels((int)$amount);
                                EconomyAPI::getInstance()->addMoney($s, $amount * $this->getConfig()->get("amount-per-xp"));
                                $format = str_replace(["{xp}", "{amount}"], [$amount, $amount * $this->getConfig()->get("amount-per-xp")], $this->getConfig()->get("sellxp-msg"));
                                $s->sendMessage($format);
                            } else {
                                $s->sendMessage($this->getConfig()->get("not-enough-xp-msg"));
                            }
                        } else {
                            $s->sendMessage($this->getConfig()->get("non-integer-given"));
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

    public function sellXp($player){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createCustomForm(function (Player $player, array $data = null){
            if($data === null){
                return true;
            }
            $amount = $data[0];
            if($player->getXpLevel() >= $amount){
                $player->subtractXpLevels((int)$amount);
                EconomyAPI::getInstance()->addMoney($player, $amount * $this->getConfig()->get("amount-per-xp"));
                $format = str_replace(["{xp}", "{amount}"], [$amount, $amount * $this->getConfig()->get("amount-per-xp")], $this->getConfig()->get("sellxp-msg"));
                $player->sendMessage($format);
            } else {
                $player->sendMessage($this->getConfig()->get("not-enough-xp-msg"));
            }
        });
        $form->setTitle($this->getConfig()->get("form-title"));
        $format = str_replace("{xp-money-exchange-rate}", $this->getConfig()->get("amount-per-xp"), $this->getConfig()->get("slider-name"));
        $form->addSlider($format, 1, $player->getXpLevel(), 1);
        $form->sendToPlayer($player);
        return $form;
    }
}
