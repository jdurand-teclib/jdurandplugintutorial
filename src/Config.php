<?php

namespace GlpiPlugin\Jdplugintutorial;

use CommonGLPI;
use Session;
use Glpi\Application\View\TemplateRenderer;
use Config as Glpi_Config;

class Config extends Glpi_Config
{
    public static function getTypeName($nb = 0): string
    {
        return __('Super Asset configurations', 'jdplugintutorial');
    }

    /**
     * Returns a set of configurations for the current context
     * @return array set of configurations.
     */
    public static function getConfig(): array
    {
        return Glpi_Config::getConfigurationValues('plugin:jdplugintutorial');
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        switch ($item->getType()) {
            case Glpi_Config::class:
                return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    public static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0,
    ): bool {
        switch ($item->getType()) {
            case Glpi_Config::class:
                self::showForConfig($item, $withtemplate);
        }

        return true;
    }

    public static function showForConfig(
        CommonGLPI $config,
        int $withtemplate = 0,
    ): void {

        if (self::canView()) {

            $current_config = self::getConfig();
            $canedit        = Session::haveRight(self::$rightname, UPDATE);

            TemplateRenderer::getInstance()->display('@jdplugintutorial/config.html.twig', [
                'current_config' => $current_config,
                'can_edit'       => $canedit,
            ]);
        }
    }
}
