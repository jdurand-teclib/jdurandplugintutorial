<?php

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

class SuperAsset extends CommonDBTM
{
    // right management, we'll change this later
    static $rightname = 'computer';
    const RIGHT_ONE = 128;

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

    public static function getTypes(): array
    {
        return [Computer::class];
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

    function rawSearchOptions()
    {
        $options = [];

        $options[] = [
            'id'   => 'common',
            'name' => __('Characteristics')
        ];

        $options[] = [
            'id'    => 1,
            'table' => self::getTable(),
            'field' => 'name',
            'name'  => __('Name'),
            'datatype' => 'itemlink'
        ];

        $options[] = [
            'id'    => 2,
            'table' => self::getTable(),
            'field' => 'id',
            'name'  => __('ID')
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
            ]
        ];

        return $options;
    }

    function defineTabs($options = [])
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

    public function prepareInputForUpdate($input)
    {
        if(self::checkInput($input)){
            return $input;
        }
        return false;
    }

    public function post_deleteItem()
    {
        self::deleteFromRelations();
    }

    public function post_purgeItem()
    {
        self::deleteFromRelations();
    }

    public function deleteFromRelations()
    {
            $id = $this->getID();
            global $DB;

            $DB->delete(SuperAsset_Item::getTable(), [
            'plugin_jdplugintutorial_superassets_id' => $id
        ]);
    }

    public function prepareInputForAdd($input)
    {
        if(self::checkInput($input)){
            return $input;
        }
        return false;
    }

    function checkInput($input): bool
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
        if(!preg_match($phonenumberformat, $input["phonenumber"])) {
            Session::addMessageAfterRedirect(sprintf(__s('The %s must be in french format (XX XX XX XX XX or +33 X XX XX XX XX'), 'phonenumber'), false, ERROR);

            return false;
        }
        return True;
    }

    public static function preItemForm(array $params)
    {
        $item = $params['item'];
        if($item::getType() === Computer::class){
            $options = $params['options'];

            $nbItems = SuperAsset_Item::countForItem($item);
            $out = '<tr><th>Linked assets: </th><td><a href="/front/computer.form.php?id=';
            $out .= $options["id"];
            $out .= '&forcetab=GlpiPlugin\Jdpluginutorial\SuperAsset_Item$1&forcetab=GlpiPlugin\Jdpluginutorial\SuperAsset_Item$1&forcetab=GlpiPlugin\Jdplugintutorial\SuperAsset_Item$1">';
            $out .= $nbItems;
            $out .= '</a></td></tr>';
            echo $out;
        }
    }

    function getRights($interface = 'central')
    {
        // if we need to keep standard rights
        $rights = parent::getRights();

        // define an additional right
        $rights[self::RIGHT_ONE] = __("My specific rights", "jdplugintutorial");

        return $rights;
    }

    function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        // add a single massive action
        $class        = __CLASS__;
        $action_key   = "computer_link";
        $action_label = "Link to a computer";
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        return $actions;
    }

    static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'computer_link':
                echo __("Select Computer");
                echo Dropdown::show(Computer::dropdown());

                break;

            case 'superasset_link':
                echo __('Select Super Asset');
                echo Dropdown::show(SuperAsset::dropdown());
                break;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {
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

    public static function linkToComputer(string $computerId, string $superAssetId): bool {
        global $DB;
        $DB->insert(SuperAsset_Item::getTable(), [
            "plugin_jdplugintutorial_superassets_id" => $superAssetId,
            "itemtype" => Computer::getType(),
            "items_id" => $computerId
        ]);
        return true;
    }
}