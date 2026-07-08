<?php

namespace GlpiPlugin\Jdplugintutorial;

use CommonDBTM;
use Notepad;
use Log;
use Glpi\Application\View\TemplateRenderer;
use SuperAsset_Item;

class SuperAsset extends CommonDBTM
{
    // right management, we'll change this later
    static $rightname = 'computer';

    // permits to automaticaly store logs for this itemtype
    // in glpi_logs table
    public $dohistory = true;

    /**
     *  Name of the itemtype
     */
    static function getTypeName($nb=0)
    {
        return _n('Super-asset', 'Super-assets', $nb);
    }

    function showForm($ID, $options=[])
    {
        $this->initForm($ID, $options);
        // @jdplugintutorial is a shortcut to the **templates** directory of your plugin
        TemplateRenderer::getInstance()->display('@jdplugintutorial/superasset.form.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);

        return true;
    }

    /**
     * Define menu name
     */
    public static function getMenuName($nb = 0)
    {
        // call class label
        return self::getTypeName($nb);
    }

    /**
     * Define additional links used in breacrumbs and sub-menu
     *
     * A default implementation is provided by CommonDBTM
     */
    public static function getMenuContent()
    {
        $title  = self::getMenuName(Session::getPluralNumber());
        $search = self::getSearchURL(false);
        $form   = self::getFormURL(false);

        // define base menu
        $menu = [
            'title' => __("JD Plugin Tutorial", 'jdplugintutorial'),
            'page'  => $search,

            // define sub-options
            // we may have multiple pages under the "Plugin > My type" menu
            'options' => [
                'superasset' => [
                    'title' => $title,
                    'page'  => $search,

                    //define standard icons in sub-menu
                    'links' => [
                        'search' => $search,
                        'add'    => $form
                    ]
                ]
            ]
        ];

        return $menu;
    }

    function defineTabs($options = [])
    {
        $tabs = [];
        $this->addDefaultFormTab($tabs)
            ->addStandardTab(Superasset_Item::class, $tabs, $options)
            ->addStandardTab(Notepad::class, $tabs, $options)
            ->addStandardTab(Log::class, $tabs, $options);

        return $tabs;
    }
}