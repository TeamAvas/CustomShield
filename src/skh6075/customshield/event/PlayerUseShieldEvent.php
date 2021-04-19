<?php

namespace skh6075\customshield\event;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\Player;

class PlayerUseShieldEvent extends Event implements Cancellable{

    private Player $player;

    private Entity $attacker;

    private int $amount;

    public function __construct(Player $player, Entity $attacker, int $amount) {
        $this->player = $player;
        $this->attacker = $attacker;
        $this->amount = $amount;
    }

    final public function getPlayer(): Player{
        return $this->player;
    }

    final public function getAttacker(): Entity{
        return $this->attacker;
    }

    final public function getAmount(): int{
        return $this->amount;
    }

    /**
     * @description It supports the method you need to override the event.
     *
     * @param int $amount
     */
    public function setAmount(int $amount): void{
        $this->amount = $amount;
    }
}