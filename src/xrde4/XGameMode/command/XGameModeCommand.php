<?php

declare(strict_types=1);

namespace xrde4\XGameMode\command;

use pocketmine\Server;
use xrde4\XGameMode\XGameMode;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\command\{Command, CommandSender};
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\player\GameMode;

class XGameModeCommand extends Command implements PluginOwned{
		use PluginOwnedTrait {
		__construct as setOwningPlugin;
	}
	
	public function __construct(private XGameMode $plugin){
        $this->setOwningPlugin($plugin);
		parent::__construct("gm", $plugin->LanguageMessage("description"), "/gm");
	 	$this->setPermission('xgamemode.perms');
		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){

		if (!$sender->hasPermission('xgamemode.perms')) {
            $sender->sendMessage($this->plugin->LanguageMessage("no_permissions"));
            return FALSE;
        }	

        if(!$sender instanceof Player){
        	$sender->sendMessage("§f§l§o[§c!§f] §8§l§o§fG§eM§r§8§7 :: §c Exclusively in the game.");
        	return;
        }
		
        if (!file_exists($this->plugin->getDataFolder() . "Players/" . $sender->getName() . ".yml")) {
            new Config($this->plugin->getDataFolder() . "Players/" . $sender->getName() . ".yml", Config::YAML, array(
                "GAMEMODE" => $sender->getGamemode()->getEnglishName(),
				"ARMOR_INVENTORY" => null,
				"OFFHAND_INVENTORY" => null,
				"INVENTORY" => null
            ));
        }
		$playerInventory = new Config($this->plugin->getDataFolder() . "Players/" . $sender->getName() . ".yml", Config::YAML);
		
		if ($sender->getGamemode() === GameMode::SURVIVAL()) {
			if($sender->getGamemode()->getEnglishName() != $playerInventory->get("GAMEMODE")){
				$sender->sendMessage("§f§l§o[§c!§f] §8§l§o§fG§eM§r§8§7 :: §fYour mode §cdoes not match §fthe game mode of the last modification.");
			return;
			}
        $invContent = $sender->getInventory()->getContents();
		$armorContent = $sender->getArmorInventory()->getContents();
		$offhandInventory = $sender->getoffHandInventory()->getContents();

		$inv64 = $this->plugin->serialize($invContent);
		$armor64 = $this->plugin->serialize($armorContent); 
		$offhand64 = $this->plugin->serialize($offhandInventory); 
		
		$sender->getInventory()->clearAll();
		$sender->getArmorInventory()->clearAll();
		$sender->getoffHandInventory()->clearAll();
		$sender->setGamemode(GameMode::CREATIVE());
		
		if(is_string($playerInventory->get("INVENTORY"))){
		$sender->getInventory()->setContents($this->plugin->deSerialize(base64_decode($playerInventory->get("INVENTORY"))));
		}
		if(is_string($playerInventory->get("ARMOR_INVENTORY"))){
		$sender->getArmorInventory()->setContents($this->plugin->deSerialize(base64_decode($playerInventory->get("ARMOR_INVENTORY"))));
		}
     	if(is_string($playerInventory->get("OFFHAND_INVENTORY"))){
		$sender->getOffHandInventory()->setContents($this->plugin->deSerialize(base64_decode($playerInventory->get("OFFHAND_INVENTORY"))));
		} 
		
		$playerInventory->setNested("ARMOR_INVENTORY", base64_encode($armor64));
		$playerInventory->setNested("INVENTORY", base64_encode($inv64));
		$playerInventory->setNested("OFFHAND_INVENTORY", base64_encode($offhand64));
		$playerInventory->setNested("GAMEMODE", $sender->getGamemode()->getEnglishName());
		$playerInventory->save();
		$sender->sendMessage($this->plugin->LanguageMessage("change_gm_message"));
		return;
		
	}elseif($sender->getGamemode() === GameMode::CREATIVE()) {
        if($sender->getGamemode()->getEnglishName() != $playerInventory->get("GAMEMODE")){
			$sender->sendMessage("§f§l§o[§c!§f] §8§l§o§fG§eM§r§8§7 :: §fYour mode §cdoes not match §fthe game mode of the last modification.");
		 return;
		 }
        $invContent = $sender->getInventory()->getContents();
		$armorContent = $sender->getArmorInventory()->getContents();
		$offhandInventory = $sender->getoffHandInventory()->getContents();

		$inv64 = $this->plugin->serialize($invContent);
		$armor64 = $this->plugin->serialize($armorContent); 
		$offhand64 = $this->plugin->serialize($offhandInventory); 
		
		$sender->getInventory()->clearAll();
		$sender->getArmorInventory()->clearAll();
		$sender->getoffHandInventory()->clearAll();
		$sender->setGamemode(GameMode::SURVIVAL());
		
		if(is_string($playerInventory->get("INVENTORY"))){
		$sender->getInventory()->setContents($this->plugin->deSerialize(base64_decode($playerInventory->get("INVENTORY"))));
		}
		if(is_string($playerInventory->get("ARMOR_INVENTORY"))){
		$sender->getArmorInventory()->setContents($this->plugin->deSerialize(base64_decode($playerInventory->get("ARMOR_INVENTORY"))));
		}
		if(is_string($playerInventory->get("OFFHAND_INVENTORY"))){
		$sender->getOffHandInventory()->setContents($this->plugin->deSerialize(base64_decode($playerInventory->get("OFFHAND_INVENTORY"))));
		} 
		
		$playerInventory->setNested("ARMOR_INVENTORY", base64_encode($armor64));
		$playerInventory->setNested("INVENTORY", base64_encode($inv64));
		$playerInventory->setNested("OFFHAND_INVENTORY", base64_encode($offhand64));
		$playerInventory->setNested("GAMEMODE", $sender->getGamemode()->getEnglishName());
		$playerInventory->save();		
		$sender->sendMessage($this->plugin->LanguageMessage("change_gm_message"));
		return;
}else{
				$sender->sendMessage("§f§l§o[§c!§f] §8§l§o§fG§eM§r§8§7 :: §fYour mode §cdoes not match §fany of the available modes");
		 return;
}
	}
}
		
