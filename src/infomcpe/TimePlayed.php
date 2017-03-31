<?php
namespace infomcpe;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\utils\Utils; 
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TF;
use pocketmine\scheduler\CallbackTask;

class TimePlayed extends PluginBase implements Listener {
       public function onEnable(){
             @mkdir($this->getDataFolder());
             if(!file_exists($this->getDataFolder()."timeplayed.db")){
                $this->saveResource("timeplayed.db");
            }
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "update")), 20*60);
            $this->session = $this->getServer()->getPluginManager()->getPlugin("SessionAPI");
            $this->pureperms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
            if ($this->getServer()->getPluginManager()->getPlugin("PluginDownloader")) {
            $this->getServer()->getScheduler()->scheduleAsyncTask(new CheckVersionTask($this, 338));
                    }
            @$this->db()->query("CREATE TABLE `TimePlayed` (
	`player` TEXT NOT NULL,
	`timeplayed` INT(11) NOT NULL
)
;
");

         $this->getServer()->getPluginManager()->registerEvents($this, $this);
   }
   public function update() {
       foreach ($this->getServer()->getOnlinePlayers() as $player){
           $nickname = strtolower($player->getName());
           $lastData = $this->db()->query("SELECT * FROM `TimePlayed` WHERE player ='{$nickname}'")->fetchArray(SQLITE3_ASSOC);
           if($lastData['player'] != null){
               
           $addtime = $lastData['timeplayed'] + 1;
           $this->db()->query("UPDATE `TimePlayed` SET `timeplayed`='{$addtime}' WHERE  `player`='{$nickname}' ");
           } else {
               $this->db()->query("INSERT INTO `TimePlayed` (`player`, `timeplayed`) VALUES ('{$nickname}', '0')");
           }
       }
   }
   public function getTime($player) {
       if($player instanceof \pocketmine\Player){
           $nickname = $player->getName();
       }else{
           $nickname = $player;
       }
       $data = $this->db()->query("SELECT * FROM `TimePlayed` WHERE player ='{$nickname}'")->fetchArray(SQLITE3_ASSOC);
       return $data['timeplayed'];
   }
   private function db() {
          return new \SQLite3($this->getDataFolder()."timeplayed.db");
     }
}