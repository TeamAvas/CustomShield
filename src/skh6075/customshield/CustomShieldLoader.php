<?php

namespace skh6075\customshield;

use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\sound\DoorBumpSound;
use skh6075\customshield\event\PlayerUseShieldEvent;
use function file_exists;
use function file_put_contents;
use function file_get_contents;
use function json_encode;
use function json_decode;

final class CustomShieldLoader extends PluginBase implements Listener{
    use SingletonTrait;

    private array $config = [];

    protected function onLoad(): void{
        self::setInstance($this);
    }

    protected function onEnable(): void{
        if (!file_exists($file = $this->getDataFolder() . "config.json")) {
            file_put_contents($file, json_encode([]));
        }

        $this->config = json_decode(file_get_contents($file), true);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    protected function onDisable(): void{
        file_put_contents($this->getDataFolder() . "config.json", json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void{
        if (!$this->isExistsPlayer($player = $event->getPlayer()))
            $this->setLeftShield($player, 0);
    }

    /** @priority LOWEST */
    public function onEntityDamage(EntityDamageEvent $event): void{
        if ($event->isCancelled())
            return;

        /** @var EntityDamageByEntityEvent|EntityDamageByChildEntityEvent $event */
        if (!$this->isConsistentEvent($event) or !($player = $event->getEntity()) instanceof Player)
            return;

        /** @var Player $player */
        $ev = new PlayerUseShieldEvent($player, $event->getDamager(), 1);
        $ev->call();
        if ($ev->isCancelled())
            return;

        $this->setLeftShield($player, $this->getLeftShield($player) - $ev->getAmount());

        $player->getWorld()->addSound($player->getPosition(), new DoorBumpSound());
        $player->sendActionBarMessage("Youd Left Shield: " . $this->getLeftShield($player));
    }

    private function isConsistentEvent(EntityDamageEvent $event): bool{
        return $event instanceof EntityDamageByEntityEvent or $event instanceof EntityDamageByChildEntityEvent;
    }

    public function getLeftShield(Player $player): int{
        return $this->config[$player->getName()] ?? 0;
    }

    public function setLeftShield(Player $player, int $amount): void{
        $this->config[$player->getName()] = $amount;
    }

    private function isExistsPlayer(Player $player): bool{
        return isset($this->config[$player->getName()]);
    }
}