<?php

/**
 * GNU LESSER GENERAL PUBLIC LICENSE v3.0
 
██╗░░██╗██████╗░██████╗░███████╗██╗░░░██╗
╚██╗██╔╝██╔══██╗██╔══██╗██╔════╝██║░░░██║
░╚███╔╝░██████╔╝██║░░██║█████╗░░╚██╗░██╔╝
░██╔██╗░██╔══██╗██║░░██║██╔══╝░░░╚████╔╝░
██╔╝╚██╗██║░░██║██████╔╝███████╗░░╚██╔╝░░
╚═╝░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝░░░╚═╝░░░
 
 If you find my plugin helpful, could you please consider giving it a star on my profile? 
 Your support means a lot to me! 🌟 Thank you! (https://github.com/xrde4)"
 */
 
declare(strict_types=1);
 
namespace xrde4\XGameMode;

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\item\Item;
use xrde4\XGameMode\command\XGameModeCommand;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\item\{StringToItemParser, LegacyStringToItemParser};
use function zlib_decode;
use function zlib_encode;
use const ZLIB_ENCODING_GZIP;

class XGameMode extends PluginBase implements Listener
{
	
	private const TAG_NAME = "contents";
	public Config $users;
		
    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->users = new Config($this->getDataFolder()."config.yml", Config::YAML,[
		    "language" => "ENG",
			"change_gm_message_ua" => "§f§l§o[§c!§f] §8§l§o§fG§eM§r§8§7 :: §fВи успішно §aзмінили §fрежим гри.",
			"no_permissions_ua" => "§f§l§o[§c!§f] §8§l§o§fG§eM§r§8§7 :: §fУ вас §cнедостатньо §fправ для використання цієї команди.",
			"description_ua" => "Перемкнути режим гри.",			
			"change_gm_message" => "§f§l§o[§c!§f] §8§l§o§fX§eC§r§8§7 :: §fYou §asuccessfully §fchanged §fgamemode.",
			"no_permissions" => "§f§l§o[§c!§f] §8§l§o§fG§eM§r§8§7 :: §fYou not have permissions!",
			"description" => "Change game mode.."]);
		if (!file_exists($this->getDataFolder() . "Players")) {
            mkdir($this->getDataFolder() . "Players");
        }
	   	 $this->getServer()->getCommandMap()->register("XGameMode", new XGameModeCommand($this));
    }
	
	
 	public function serialize(array $contents) : string{
		if(count($contents) === 0){
			return "";
		}

		$contents_tag = [];
		foreach($contents as $slot => $item){
			$contents_tag[] = $item->nbtSerialize($slot);
		}
		return (new BigEndianNbtSerializer())->write(new TreeRoot(CompoundTag::create()->setTag(self::TAG_NAME, new ListTag($contents_tag, NBT::TAG_Compound))));
	}
	
	public function deSerialize(string $string) : array{
		if($string == ""){
			return [];
		}

		$tag = (new BigEndianNbtSerializer())->read($string)->mustGetCompoundTag()->getListTag(self::TAG_NAME) ?? throw new InvalidArgumentException("Invalid serialized string specified");

		$contents = [];
		foreach($tag as $value){
			try{ 
				$item = Item::nbtDeserialize($value);
			}catch(SavedDataLoadingException){
				continue;
			}
			$contents[$value->getByte("Slot")] = $item;
		}
		return $contents;
	} 
	

	public function LanguageMessage($message){
		if($this->users->getNested("language") == "UA"){
			if($message == "change_gm_message"){
				return $this->users->getNested("change_gm_message_ua");
			}elseif($message == "no_permissions"){	
				return $this->users->getNested("no_permissions_ua");
			}elseif($message == "description"){	
				return $this->users->getNested("description_ua");				
			}
		}else{
			return $this->users->getNested($message);
		}
	}
}
	
