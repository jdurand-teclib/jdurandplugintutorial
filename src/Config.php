<?php

namespace GlpiPlugin\Jdplugintutorial;

use CommonGLPI;
use Dropdown;
use Html;
use Session;
use Glpi\Application\View\TemplateRenderer;

class Config extends \Config
{

    static function getTypeName($nb = 0)
    {
        return __('JD Plugin Tutorial', 'jdplugintutorial');
    }

    static function getConfig()
    {
        return \Config::getConfigurationValues('plugin:jdplugintutorial');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case \Config::class:
                return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {
        switch ($item->getType()) {
            case \Config::class:
                return self::showForConfig($item, $withtemplate);
        }

        return true;
    }

    static function showForConfig(
        \Config $config,
        $withtemplate = 0
    ) {
        global $CFG_GLPI;

        if (!self::canView()) {
            return false;
        }

        $current_config = self::getConfig();
        $canedit        = Session::haveRight(self::$rightname, UPDATE);

        TemplateRenderer::getInstance()->display('@jdplugintutorial/config.html.twig', [
            'current_config' => $current_config,
            'can_edit'       => $canedit
        ]);
    }
}
