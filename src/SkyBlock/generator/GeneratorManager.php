<?php

namespace SkyBlock\generator;

use pocketmine\level\generator\GeneratorManager as GManager;
use SkyBlock\generator\generators\BasicIsland;
use SkyBlock\generator\generators\OPIsland;
use SkyBlock\SkyBlock;

class GeneratorManager {

    /** @var SkyBlock */
    private $plugin;

    /** @var string[] */
    private $generators = [
        "Basic" => BasicIsland::class,
        "OP" => OPIsland::class
    ];
    
    /**
     * GeneratorManager constructor.
     * @param SkyBlock $plugin
     */
    public function __construct(SkyBlock $plugin) {
        $this->plugin = $plugin;
        foreach($this->generators as $name => $class) {
            GManager::addGenerator($class, $name);
        }
    }
    
    /**
     * @return string[]
     */
    public function getGenerators(): array {
        return $this->generators;
    }

    /**
     * Return if a generator exists
     *
     * @param string $name
     * @return bool
     */
    public function isGenerator(string $name) {
        return isset($this->generators[$name]);
    }
    
    /**
     * @param string $name
     * @param string $class
     */
    public function registerGenerator(string $name, string $class): void {
        GManager::addGenerator($class, $name);
        if(isset($this->generators[$name])) {
            $this->plugin->getLogger()->debug("Overwriting generator: $name");
        }
        $this->generators[$name] = $class;
    }

}