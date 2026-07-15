<?php
namespace GlpiPlugin\Jdplugintutorial;

use CommonDBTM;
use CommonGLPI;
use Html;
use Profile as Glpi_Profile;
use GlpiPlugin\Jdplugintutorial\SuperAsset;
use Glpi\Application\View\TemplateRenderer;

class Profile extends CommonDBTM
{
    public static $rightname = 'profile';

    static function getTypeName($nb = 0)
    {
        return __("JD Plugin Tutorial", 'jdplugintutorial');
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (
            $item instanceof Glpi_Profile
            && $item->getField('id')
        ) {
            return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {
        if (
            $item instanceof Glpi_Profile
            && $item->getField('id')
        ) {
            return self::showForProfile($item->getID());
        }

        return true;
    }

    static function getAllRights($all = false)
    {
        $rights = [
            [
                'itemtype' => SuperAsset::class,
                'label'    => SuperAsset::getTypeName(),
                'field'    => 'jdplugintutorial::superasset'
            ]
        ];

        return $rights;
    }


    static function showForProfile($profiles_id = 0)
    {
        $profile = new Glpi_Profile();
        $profile->getFromDB($profiles_id);

        TemplateRenderer::getInstance()->display('@jdplugintutorial/profile.html.twig', [
            'can_edit' => self::canUpdate(),
            'profile'  => $profile,
            'rights'   => self::getAllRights()
        ]);
    }
}
