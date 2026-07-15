<?php

use DBConnection;
use GlpiPlugin\Jdplugintutorial\SuperAsset;
use GlpiPlugin\Jdplugintutorial\SuperAsset_Item;
use GlpiPlugin\Jdplugintutorial\Profile;
use Config;

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

/**
 * Plugin install process
 */
function plugin_jdplugintutorial_install(): bool {
    global $DB;

    $default_charset   = DBConnection::getDefaultCharset();
    $default_collation = DBConnection::getDefaultCollation();

    // instantiate migration with version
    $migration = new Migration(PLUGIN_JDPLUGINTUTORIAL_VERSION);

    // create table only if it does not exist yet!
    $table = SuperAsset::getTable();
    if (!$DB->tableExists($table)) {
        //table creation query
        $query = "CREATE TABLE `$table` (
                  `id`         int unsigned NOT NULL AUTO_INCREMENT,
                  `is_deleted` TINYINT NOT NULL DEFAULT '0',
                  `name`      VARCHAR(255) NOT NULL,
                  `phonenumber` VARCHAR(255) NOT NULL,
                  `created_at` DATE NOT NULL,
                  PRIMARY KEY  (`id`)
                 ) ENGINE=InnoDB
                 DEFAULT CHARSET={$default_charset}
                 COLLATE={$default_collation}";
        $DB->doQuery($query);
    }

    $items_table = SuperAsset_Item::getTable();
    if(!$DB->tableExists($items_table)){
        //table creation query
        $query2 = "CREATE TABLE `$items_table` (
                    `id`        int unsigned NOT NULL AUTO_INCREMENT,
                    `plugin_jdplugintutorial_superassets_id`        int(11) NOT NULL DEFAULT '0',
                    `itemtype`      VARCHAR(255) DEFAULT NULL,
                    `items_id`      int(11) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `plugin_jdplugintutorial_superassets_id` (`plugin_jdplugintutorial_superassets_id`),
                    KEY `items_id` (`items_id`),
                    KEY `itemtype` (`itemtype`,`items_id`)
                ) ENGINE=InnoDB
                DEFAULT CHARSET={$default_charset}
                COLLATE={$default_collation}";
        $DB->doQuery($query2);
    }

    if ($DB->tableExists($table)) {
        // missing field
        $migration->addField(
            $table,
            'fieldname',
            'string'
        );

        // missing index
        $migration->addKey(
            $table,
            'fieldname'
        );
    }

    if ($DB->tableExists($items_table)) {
        // missing field
        $migration->addField(
            $items_table,
            'fieldname',
            'string'
        );

        // missing index
        $migration->addKey(
            $items_table,
            'fieldname'
        );
    }

    $DB->insert("glpi_displaypreferences", [
        'itemtype'  => SuperAsset::getType(),
        'num'       => 3,
        'rank'      => 1,
        'users_id'  => 0,
    ]);

    //execute the whole migration
    $migration->executeMigration();

    // Default values for the plugin's configuration
    Config::setConfigurationValues('plugin:jdplugintutorial', [
        'jdplugintutorial_computer_tab' => '1',
        'jdplugintutorial_computer_form' => '1',
    ]);

    // add rights to current profile
   foreach (Profile::getAllRights() as $right) {
      ProfileRight::addProfileRights([$right['field']]);
   }

   Config::setConfigurationValues('core', ['notifications_mailing' => 0]);

    return true;
}

/**
 * Plugin uninstall process
 */
function plugin_jdplugintutorial_uninstall(): bool
{
    global $DB;

    $tables = [
        SuperAsset::getTable(),
        SuperAsset_Item::getTable(),
    ];

    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $DB->doQuery(
                "DROP TABLE `$table`"
            );
        }
    }

    // Delete display preferences for columns
    $DB->delete("glpi_displaypreferences", [
        'itemtype' => SuperAsset::getType()
    ]);

    // Delete default config
    $config = new Config();
    $config->deleteByCriteria(['context' => 'plugin:jdplugintutorial']);

    // delete rights for current profile
   foreach (Profile::getAllRights() as $right) {
      ProfileRight::deleteProfileRights([$right['field']]);
   }

   Config::deleteConfigurationValues('core', ['notifications_mailing']);

   return true;
}

function plugin_jdplugintutorial_getAddSearchOptionsNew($itemtype)
{
    $sopt = [];

    if ($itemtype == Computer::class) {
        $sopt[] = [
            'id'           => 12345,
            'table'        => Superasset::getTable(),
            'field'        => 'name',
            'name'         => __('Associated Superassets', 'myplugin'),
            'datatype'     => 'itemlink',
            'forcegroupby' => true,
            'usehaving'    => true,
            'joinparams'   => [
                'beforejoin' => [
                    'table'      => Superasset_Item::getTable(),
                    'joinparams' => [
                        'jointype' => 'itemtype_item',
                    ]
                ]
            ]
        ];
    }

    return $sopt;
}

function jdplugintutorial_purge_computer_called(CommonDBTM $item)
{
    global $DB;
    $computerId = $item->fields['id'];

    $DB->delete(SuperAsset_Item::getTable(), [
        'items_id' => $computerId,
        'itemtype' => Computer::getType()
    ]);
}

function jdplugintutorial_pre_item_form_computer(array $params): string
{
    $nbItems = SuperAsset_Item::countForItem($params[Computer::class]);
    return '<tr><a href="/front/computer.form.php?id=5&forcetab=PluginJdplugintutorialSuperAsset$1">'.$nbItems.'</a></tr>';
}

function plugin_jdplugintutorial_MassiveActions($type)
{
   $actions = [];
   switch ($type) {
      case Computer::class:
         $class = SuperAsset::class;
         $key   = 'superasset_link';
         $label = __("Link SuperAsset");
         $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $key] = $label;

         break;
   }
   return $actions;
}
