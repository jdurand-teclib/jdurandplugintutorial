<?php

namespace GlpiPlugin\Jdplugintutorial;

use CommonDBTM;
use Glpi\Application\View\TemplateRenderer;
use CommonGlpi;

class SuperAsset_Item extends CommonDBTM
{
    /**
     * Tabs title
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case Superasset::class:
                $nb = countElementsInTable(self::getTable(),
                    [
                        'plugin_jdplugintutorial_superassets_id' => $item->getID()
                    ]
                );
                return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return '';
    }

    /**
     * Display tabs content
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case Superasset::class:
                return self::showForSuperAsset($item, $withtemplate);
        }

        return true;
    }

    /**
     * Specific function for display only items of Superasset
     */
    static function showForSuperAsset(SuperAsset $superasset, $withtemplate = 0)
    {
        TemplateRenderer::getInstance()->display('@jdplugintutorial/superasset_item.html.twig', [
            'superasset' => $superasset,
        ]);
    }
}