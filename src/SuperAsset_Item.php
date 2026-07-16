<?php

namespace GlpiPlugin\Jdplugintutorial;

use CommonDBRelation;
use CommonDBTM;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use CommonGLPI;
use Session;
use Entity;

class SuperAsset_Item extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1 = SuperAsset::class;
    public static $items_id_1    = 'plugin_jdplugintutorial_superassets_id';
    public static $take_entity_1 = false;

    public static $itemtype_2    = "itemtype";
    public static $items_id_2    = 'items_id';
    public static $take_entity_2 = true;

    static function getTypeName($nb=0)
    {
        return _n('Super-asset Items', 'Super-assets Items', $nb);
    }

    /**
     * Tabs title
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if($item->getType() == SuperAsset::class){
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = self::countForMainItem($item);
            }
            return self::createTabEntry(_n('Associated item', 'Associated items', Session::getPluralNumber()), $nb, $item::getType(), 'ti ti-package');
        }
        elseif(in_array($item->getType(), SuperAsset::getTypes())){
            if ($_SESSION['glpishow_count_on_tabs']) {
                $count = self::countForItem($item);
                return self::createTabEntry(text: SuperAsset::getTypeName(Session::getPluralNumber()), nb: $count, icon: SuperAsset::getIcon());
            }
            return self::createTabEntry(text: SuperAsset::getTypeName(Session::getPluralNumber()), icon: SuperAsset::getIcon());
        }
        return '';
    }

    /**
     * Display tabs content
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if($item->getType() == SuperAsset::class){
            self::showForSuperasset($item, $withtemplate);
        }
        elseif(in_array($item->getType(), SuperAsset::getTypes())){
            self::showForItem($item, $withtemplate);
        }
        return true;
    }

    /**
     * Specific function for display only items of Superasset
     */
    public static function showForSuperasset(CommonGLPI $superasset, $withtemplate = 0): void
    {
        $instID = $superasset->getID();

        TemplateRenderer::getInstance()->display('components/form/link_existing_or_new.html.twig', [
                'rand' => mt_rand(),
                'link_itemtype' => self::class,
                'source_itemtype' => $superasset::class,
                'source_items_id' => $instID,
                'link_types' => SuperAsset::getTypes(),
                'generic_target' => true,
                'dropdown_options' => [
                    'entity'      => $superasset->getEntityID(),
                    'entity_sons' => $superasset->isRecursive(),
                ],
                'form_label' => '',
                'add_button_label' => _x('button', 'Associate'),
            ]);

        $columns = [
            'name' => __('Name'),
        ];

        $types_iterator = self::getDistinctTypes($instID, ['itemtype' => SuperAsset::getTypes()]);
        $entries = [];
        foreach ($types_iterator as $type_row) {
            $itemtype = $type_row['itemtype'];
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }
            $itemtype_name = $itemtype::getTypeName(1);

            if ($item::canView()) {
                $iterator = self::getTypeItems($instID, $itemtype);

                if (count($iterator)) {
                    foreach ($iterator as $data) {
                        if (!$item->getFromDB($data["id"])) {
                            continue;
                        }

                        $entry = [
                            'itemtype' => static::class,
                            'id' => $data['linkid'],
                            'row_class' => $item->isDeleted() ? 'table-danger' : '',
                            'type' => $itemtype_name,
                            'name' => $item->getLink(),
                            'serial' => $data['serial'] ?? '-',
                            'otherserial' => $data['otherserial'] ?? '-',
                        ];

                        if (Session::isMultiEntitiesMode()) {
                            $entry['entity'] = $item->isEntityAssign() ? Dropdown::getDropdownName("glpi_entities", $data['entity']) : '-';
                        }
                        $entries[] = $entry;
                    }
                }
            }
        }

        TemplateRenderer::getInstance()->display('components/datatable.html.twig', [
            'is_tab' => true,
            'nofilter' => true,
            'nosort' => true,
            'columns' => $columns,
            'formatters' => [
                'name' => 'raw_html',
            ],
            'entries' => $entries,
            'total_number' => count($entries),
            'filtered_number' => count($entries),
            'showmassiveactions' => true,
            'massiveactionparams' => [
                'num_displayed' => count($entries),
                'container'     => 'mass' . static::class . mt_rand(),
            ],
        ]);
    }

    public static function showForItem(CommonDBTM $item, $withtemplate = 0): void {
        $ID = $item->getField('id');

        $superasset = new SuperAsset();
        //$canedit      = $item->canAddItem('SuperAsset');
        $iterator = self::getListForItem($item);

        $superassets = [];
        $used         = [];

        foreach ($iterator as $data) {
            $superassets[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        //if ($canedit && $withtemplate < 2) {
        if ($withtemplate < 2) {
            TemplateRenderer::getInstance()->display('components/form/link_existing_or_new.html.twig', [
                'rand' => mt_rand(),
                'link_itemtype' => self::class,
                'generic_source' => true,
                'source_itemtype' => $item::class,
                'source_items_id' => $ID,
                'target_itemtype' => SuperAsset::class,
                'dropdown_options' => [
                    'entity'      => $item->getEntityID(),
                    'entity_sons' => $item->isRecursive(),
                    'used'        => $used,
                ],
                'add_button_label' => _x('button', 'Associate'),
                'form_label' => '',
            ]);
        }

        $used = [];
        $entries = [];

        foreach ($superassets as $data) {
            $superassetID = $data["id"];
            $link = htmlescape(NOT_AVAILABLE);

            if ($superasset->getFromDB($superassetID)) {
                $link = $superasset->getLink();
            }
            $used[$superassetID] = $superassetID;

            $entry = [
                'itemtype' => static::class,
                'id' => $data['linkid'],
                'row_class' => $data['is_deleted'] ? 'table-danger' : '',
                'name' => $link,
                'phonenumber' => $data['phonenumber'],
                'created_at' => $data['created_at']
            ];
            if (Session::isMultiEntitiesMode()) {
                $entry['entity'] = Dropdown::getDropdownName("glpi_entities", $data['entities_id']);
            }

            $entries[] = $entry;
        }

        $columns = [
            'name' => __('Name'),
        ];
        if (Session::isMultiEntitiesMode()) {
            $columns['entity'] = Entity::getTypeName(1);
        }
        $columns['name'] = _n('Name', 'Names', 1);
        $columns['phonenumber'] = __('Phone number');

        TemplateRenderer::getInstance()->display('components/datatable.html.twig', [
            'is_tab' => true,
            'nofilter' => true,
            'nosort' => true,
            'columns' => $columns,
            'formatters' => [
                'name' => 'raw_html',
                'date_creation' => 'date',
                'date_expiration' => 'raw_html',
            ],
            'entries' => $entries,
            'total_number' => count($entries),
            'filtered_number' => count($entries),
            //'showmassiveactions' => $canedit && $withtemplate < 2,
            'showmassiveactions' => $withtemplate < 2,
            'massiveactionparams' => [
                'num_displayed' => count($entries),
                'container'     => 'mass' . static::class . mt_rand(),
            ],
        ]);
    }
}