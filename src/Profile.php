<?php

/**
 * -------------------------------------------------------------------------
 * jdplugintutorial plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2026 by the jdplugintutorial plugin team.
 * @license   MIT https://opensource.org/licenses/mit-license.php
 * @link      https://github.com/pluginsGLPI/jdplugintutorial
 * -------------------------------------------------------------------------
 */

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
        $withtemplate = 0,
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
                'field'    => 'jdplugintutorial::superasset',
            ],
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
