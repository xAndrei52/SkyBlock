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
use room17\SkyBlock\session\Session;
use room17\SkyBlock\session\SessionLocator;
use room17\SkyBlock\SkyBlock;
use room17\SkyBlock\utils\Invitation;
use room17\SkyBlock\utils\message\MessageContainer;

class InviteCommand extends IslandCommand {

    /** @var SkyBlock */
    private $plugin;

    /**
     * InviteCommand constructor.
     * @param IslandCommandMap $map
     */
    public function __construct(IslandCommandMap $map) {
        $this->plugin = $map->getPlugin();
    }

    /**
     * @return string
     */
    public function getName(): string {
        return "invite";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return ["inv"];
    }

    /**
     * @return MessageContainer
     */
    public function getUsageMessageContainer(): MessageContainer {
        return new MessageContainer("INVITE_USAGE");
    }

    /**
     * @return MessageContainer
     */
    public function getDescriptionMessageContainer(): MessageContainer {
        return new MessageContainer("INVITE_DESCRIPTION");
    }

    /**
     * @param Session $session
     * @param array $args
     * @throws ReflectionException
     */
    public function onCommand(Session $session, array $args): void {
        if($this->checkOfficer($session)) {
            return;
        } elseif(!isset($args[0])) {
            $session->sendTranslatedMessage(new MessageContainer("INVITE_USAGE"));
            return;
        } elseif(count($session->getIsland()->getMembers()) >= $session->getIsland()->getSlots()) {
            $island = $session->getIsland();
            $next = $island->getNextCategory();
            if($next != null) {
                $session->sendTranslatedMessage(new MessageContainer("ISLAND_IS_FULL_BUT_YOU_CAN_UPGRADE", [
                    "next" => $next
                ]));
            } else {
                $session->sendTranslatedMessage(new MessageContainer("ISLAND_IS_FULL"));
            }
            return;
        }
        $player = $this->plugin->getServer()->getPlayer($args[0]);
        if($player == null) {
            $session->sendTranslatedMessage(new MessageContainer("NOT_ONLINE_PLAYER", [
                "name" => $args[0]
            ]));
            return;
        }
        $playerSession = SessionLocator::getSession($player);
        if($this->checkClone($session, $playerSession)) {
            return;
        } elseif($playerSession->hasIsland()) {
            $session->sendTranslatedMessage(new MessageContainer("CANNOT_INVITE_BECAUSE_HAS_ISLAND", [
                "name" => $player->getName()
            ]));
            return;
        }
        Invitation::send($session, $playerSession);
        $playerSession->sendTranslatedMessage(new MessageContainer("YOU_WERE_INVITED_TO_AN_ISLAND", [
            "name" => $session->getName()
        ]));
        $session->sendTranslatedMessage(new MessageContainer("SUCCESSFULLY_INVITED", [
            "name" => $player->getName()
        ]));
    }

}