<?php
namespace GlpiPlugin\Jdplugintutorial;

use CommonDBTM;
use CommonGLPI;
use Profile as Glpi_Profile;
use GlpiPlugin\Jdplugintutorial\SuperAsset;
use Glpi\Application\View\TemplateRenderer;

class Profile extends CommonDBTM
{
    public static $rightname = 'profile';

    public static function getTypeName($nb = 0): string
    {
        return __("Super Assets", 'jdplugintutorial');
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if (
            $item instanceof Glpi_Profile
            && $item->getField('id')
        ) {
            return self::createTabEntry(text: self::getTypeName(), icon: SuperAsset::getIcon());
        }
        return '';
    }

    public static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ): bool {
        if (
            $item instanceof Glpi_Profile
            && $item->getField('id')
        ) {
            self::showForProfile($item->getID());
        }

        return true;
    }

    /**
     * Return the list of rights managed by this plugin
     *
     * @param bool $all wether we return all rights or not
     *
     * @return array<int, array<string, string>>
     **/
    public static function getAllRights(bool $all = false): array
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

    /**
     * Displays the form to manage the rights for a given user
     *
     * @param int $profiles_id the user we want to manage rights for
     **/
    public static function showForProfile(int $profiles_id = 0): void
    {
        $profile = new Glpi_Profile();
        $profile->getFromDB($profiles_id);

        TemplateRenderer::getInstance()->display('@jdplugintutorial/profile.html.twig', [
            'can_edit' => self::canUpdate(),
            'profile'  => $profile,
            'rights'   => self::getAllRights(),
        ]);
    }
}
