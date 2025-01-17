<?php
declare(strict_types=1);

namespace Ifera\FactionsProScore;

use FactionsPro\FactionMain;
use Ifera\FactionsProScore\listeners\TagResolveListener;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use function strval;

class Main extends PluginBase{

	/** @var FactionMain */
	private FactionMain $owningPlugin;

	public function onEnable() : void{
		$this->saveDefaultConfig();
		$this->owningPlugin = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");
		$this->getServer()->getPluginManager()->registerEvents(new TagResolveListener($this), $this);

		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			foreach($this->getServer()->getOnlinePlayers() as $player){
				if(!$player->isOnline()){
					continue;
				}

				(new PlayerTagUpdateEvent($player, new ScoreTag("factionsproscore.faction", strval($this->getPlayerFaction($player)))))->call();
				(new PlayerTagUpdateEvent($player, new ScoreTag("factionsproscore.power", strval($this->getFactionPower($player)))))->call();
			}
		}), 20);
	}

	public function getPlayerFaction(Player $player) : string{
		$factionName = $this->owningPlugin->getPlayerFaction($player->getName());

		if($factionName === null){
			return "No Faction";
		}

		return $factionName;
	}

	public function getFactionPower(Player $player) : string{
		$factionsPro = $this->owningPlugin;
		$factionName = $factionsPro->getPlayerFaction($player->getName());

		if($factionName === null){
			return "No Faction";
		}

		return $factionsPro->getFactionPower($factionName);
	}
}