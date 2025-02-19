<?php
/**
 *  _____    ____    ____   __  __  __  ______
 * |  __ \  / __ \  / __ \ |  \/  |/_ ||____  |
 * | |__) || |  | || |  | || \  / | | |    / /
 * |  _  / | |  | || |  | || |\/| | | |   / /
 * | | \ \ | |__| || |__| || |  | | | |  / /
 * |_|  \_\ \____/  \____/ |_|  |_| |_| /_/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace room17\SkyBlock\command\presets;


use ReflectionException;
use room17\SkyBlock\command\IslandCommand;
use room17\SkyBlock\command\IslandCommandMap;
use room17\SkyBlock\session\BaseSession;
use room17\SkyBlock\session\Session;
use room17\SkyBlock\SkyBlock;
use room17\SkyBlock\utils\message\MessageContainer;

class TransferCommand extends IslandCommand {

    /** @var SkyBlock */
    private $plugin;

    /**
     * TransferCommand constructor.
     * @param IslandCommandMap $map
     */
    public function __construct(IslandCommandMap $map) {
        $this->plugin = $map->getPlugin();
    }

    /**
     * @return string
     */
    public function getName(): string {
        return "transfer";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return ["makeleader"];
    }

    /**
     * @return MessageContainer
     */
    public function getUsageMessageContainer(): MessageContainer {
        return new MessageContainer("TRANSFER_USAGE");
    }

    /**
     * @return MessageContainer
     */
    public function getDescriptionMessageContainer(): MessageContainer {
        return new MessageContainer("TRANSFER_DESCRIPTION");
    }

    /**
     * @param Session $session
     * @param array $args
     * @throws ReflectionException
     */
    public function onCommand(Session $session, array $args): void {
        if($this->checkFounder($session)) {
            return;
        } elseif(!isset($args[0])) {
            $session->sendTranslatedMessage(new MessageContainer("TRANSFER_USAGE"));
            return;
        }
        $player = $this->plugin->getServer()->getPlayer($args[0]);
        if($player == null) {
            $session->sendTranslatedMessage(new MessageContainer("NOT_ONLINE_PLAYER", [
                "name" => $args[0]
            ]));
            return;
        }
        $playerSession = $this->plugin->getSessionManager()->getSession($player);
        if($this->checkClone($session, $playerSession)) {
            return;
        } elseif($playerSession->getIsland() !== $session->getIsland()) {
            $session->sendTranslatedMessage(new MessageContainer("MUST_BE_PART_OF_YOUR_ISLAND", [
                "name" => $playerSession->getName()
            ]));
            return;
        }
        $session->setRank(BaseSession::RANK_DEFAULT);
        $playerSession->setRank(BaseSession::RANK_FOUNDER);
        $session->sendTranslatedMessage(new MessageContainer("RANK_TRANSFERRED", [
            "name" => $playerSession->getName()
        ]));
        $playerSession->sendTranslatedMessage(new MessageContainer("GOT_RANK_TRANSFERRED", [
            "name" => $session->getName()
        ]));
    }

}