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
use Dropdown;
use Notepad;
use Log;
use Glpi\Application\View\TemplateRenderer;
use Session;
use Computer;
use GlpiPlugin\Jdplugintutorial\SuperAsset_Item;
use MassiveAction;
use NotificationEvent;
use DBmysql;

use function Safe\preg_match;

class SuperAsset extends CommonDBTM
{
    // right management, we'll change this later
    public static $rightname = 'jdplugintutorial::superasset';
    public const RIGHT_ONE = 128;

    // permits to automaticaly store logs for this itemtype
    // in glpi_logs table
    public $dohistory = true;

    /**
     *  Name of the itemtype
     */
    public static function getTypeName($nb = 0): string
    {
        return _n('Super-asset', 'Super-assets', $nb);
    }

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);
        // @jdplugintutorial is a shortcut to the **templates** directory of your plugin
        TemplateRenderer::getInstance()->display('@jdplugintutorial/superasset.form.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);

        return true;
    }

    public static function getTypes(): array
    {
        return [Computer::class];
    }

    /**
     * Define menu name
     */
    public static function getMenuName($nb = 0): string
    {
        // call class label
        return self::getTypeName($nb);
    }

    /**
     * Define additional links used in breacrumbs and sub-menu
     *
     * A default implementation is provided by CommonDBTM
     */
    public static function getMenuContent(): array
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
                        'add'    => $form,
                    ],
                ],
            ],
        ];

        return $menu;
    }

    public function rawSearchOptions(): array
    {
        $options = [];

        $options[] = [
            'id'   => 'common',
            'name' => __('Characteristics'),
        ];

        $options[] = [
            'id'    => 1,
            'table' => self::getTable(),
            'field' => 'name',
            'name'  => __('Name'),
            'datatype' => 'itemlink',
        ];

        $options[] = [
            'id'    => 2,
            'table' => self::getTable(),
            'field' => 'id',
            'name'  => __('ID'),
        ];

        $options[] = [
            'id'           => 3,
            'table'        => Superasset_Item::getTable(),
            'field'        => 'id',
            'name'         => __('Number of associated assets', 'jdplugintutorial'),
            'datatype'     => 'count',
            'forcegroupby' => true,
            'usehaving'    => true,
            'joinparams'   => [
                'jointype' => 'child',
            ],
        ];

        return $options;
    }

    public function defineTabs($options = []): array
    {
        $tabs = [];
        $this->addDefaultFormTab($tabs)
            ->addStandardTab(SuperAsset_Item::class, $tabs, $options)
            ->addStandardTab(Notepad::class, $tabs, $options)
            ->addStandardTab(Log::class, $tabs, $options);

        return $tabs;
    }

    public static function getIcon(): string
    {
        return 'ti ti-rocket';
    }

    public function prepareInputForUpdate($input): array|bool
    {
        if (self::checkInput($input)) {
            return $input;
        }
        return false;
    }

    public function post_deleteItem(): void
    {
        self::deleteFromRelations();
    }

    public function post_purgeItem(): void
    {
        self::deleteFromRelations();
    }

    public function post_addItem(): void
    {
        NotificationEvent::raiseEvent("my_event_key", $this);
    }

    public function deleteFromRelations(): void
    {
        $id = $this->getID();
        /** @global DBmysql $DB */
        global $DB;

        $DB->delete(SuperAsset_Item::getTable(), [
            'plugin_jdplugintutorial_superassets_id' => $id,
        ]);
    }

    public function prepareInputForAdd($input): array|bool
    {
        if (self::checkInput($input)) {
            return $input;
        }
        return false;
    }

    public function checkInput($input): bool
    {
        if ((array_key_exists('name', $input) && (string) $input['name'] === '') || !array_key_exists('name', $input)) {
            Session::addMessageAfterRedirect(sprintf(__s('The %s field is mandatory'), 'name'), false, ERROR);

            return false;
        }

        if ((array_key_exists('phonenumber', $input) && (string) $input['phonenumber'] === '') || !array_key_exists('phonenumber', $input)) {
            Session::addMessageAfterRedirect(sprintf(__s('The %s field is mandatory'), 'phonenumber'), false, ERROR);

            return false;
        }

        $phonenumberformat = "/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/";
        if (!preg_match($phonenumberformat, $input["phonenumber"])) {
            Session::addMessageAfterRedirect(sprintf(__s('The %s must be in french format (XX XX XX XX XX or +33 X XX XX XX XX'), 'phonenumber'), false, ERROR);

            return false;
        }
        return true;
    }

    public static function preItemForm(array $params): void
    {
        $item = $params['item'];
        if ($item::getType() === Computer::class) {
            $options = $params['options'];

            $nbItems = SuperAsset_Item::countForItem($item);
            $out = '<tr><th>Linked assets: </th><td><a href="/front/computer.form.php?id=';
            $out .= $options["id"];
            $out .= '&forcetab=GlpiPlugin\Jdplugintutorial\SuperAsset_Item$1">';
            $out .= $nbItems;
            $out .= '</a></td></tr>';
            echo $out;
        }
    }

    public function getRights($interface = 'central'): array
    {
        // if we need to keep standard rights
        $rights = parent::getRights();

        // define an additional right
        $rights[self::RIGHT_ONE] = __("My specific rights", "jdplugintutorial");

        return $rights;
    }

    public function getSpecificMassiveActions($checkitem = null): array
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        // add a single massive action
        $class        = __CLASS__;
        $action_key   = "computer_link";
        $action_label = "Link to a computer";
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        return $actions;
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma): bool
    {
        switch ($ma->getAction()) {
            case 'computer_link':
                echo __s("Select Computer");
                echo Dropdown::show(Computer::dropdown());

                break;

            case 'superasset_link':
                echo __s('Select Super Asset');
                echo Dropdown::show(SuperAsset::dropdown());
                break;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids,
    ): void {
        switch ($ma->getAction()) {
            case 'computer_link':
                $input = $ma->getInput();

                foreach ($ids as $id) {

                    if (
                        $item->getFromDB($id)
                        && SuperAsset::linkToComputer($input["computers_id"], $id)
                    ) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage(__("Something went wrong"));
                    }
                }
                return;

            case 'superasset_link':
                $input = $ma->getInput();

                foreach ($ids as $id) {

                    if (
                        $item->getFromDB($id)
                        && SuperAsset::linkToComputer($id, $input["plugin_jdplugintutorial_superassets_id"])
                    ) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage(__("Something went wrong"));
                    }
                }
                return;
        }

        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    public static function linkToComputer(string $computerId, string $superAssetId): bool
    {
        /** @global DBmysql $DB */
        global $DB;
        $DB->insert(SuperAsset_Item::getTable(), [
            "plugin_jdplugintutorial_superassets_id" => $superAssetId,
            "itemtype" => Computer::getType(),
            "items_id" => $computerId,
        ]);
        return true;
    }

    public static function cronInfo($name): array
    {

        switch ($name) {
            case 'createautomaticasset':
                return ['description' => __('Atuomaticaly create an asset', 'jdplugintutorial')];
        }
        return [];
    }

    public static function cronCreateAutomaticAsset($task = null): bool
    {
        /** @global DBmysql $DB */
        global $DB;
        $DB->insert(SuperAsset::getTable(), [
            'name' => 'Automatic SuperAsset creation',
            'phonenumber' => '06 57 48 59 68',
            'created_at' => date('Y-m-d'),
        ]);

        return true;
    }
}
