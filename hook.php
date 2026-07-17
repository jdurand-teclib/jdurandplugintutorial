<?php

use DBConnection;
use GlpiPlugin\Jdplugintutorial\SuperAsset;
use GlpiPlugin\Jdplugintutorial\SuperAsset_Item;
use GlpiPlugin\Jdplugintutorial\Profile;
use GlpiPlugin\Jdplugintutorial\NotificationTargetSuperAsset;
use NotificationTemplate;
use NotificationTemplateTranslation;
use Config;
use Notification;
use NotificationTarget;
use Notification_NotificationTemplate;
use CronTask;

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
                    `plugin_jdplugintutorial_superassets_id`        int unsigned NOT NULL DEFAULT '0',
                    `itemtype`      VARCHAR(255) DEFAULT NULL,
                    `items_id`      int unsigned NOT NULL DEFAULT '0',
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

    // ##### Display columns in SuperAssets list ##### //

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

   // ##### Notifications Setup ##### //

   $DB->insert(NotificationTemplate::getTable(), [
        'name' => 'Automatic Super Asset notification template',
        'itemtype' => SuperAsset::getType(),
        'css' => "body {background-color: purple;}",
    ]);

    $templateId = $DB->insertId();

    $DB->insert(NotificationTemplateTranslation::getTable(), [
        'notificationtemplates_id' => $templateId,
        'subject' => "##superasset.name##",
        'content_text' => '##superasset.phonenumber##',
        'content_html' => '&lt;p&gt;##superasset.name## : ##superasset.phonenumber##&lt;/p&gt;'
    ]);
    $notificationTarget = new NotificationTargetSuperAsset();
    foreach($notificationTarget->getEvents() as $key => $label) {
        $DB->insert(Notification::getTable(), [
            'name' => 'Automatic Super Asset notification',
            'itemtype' => SuperAsset::getType(),
            'event' => $key,
            'is_recursive' => 0,
            'is_active' => 1,
            'allow_response' => 0,
            'attach_documents' => -2
        ]);
        $notificationId = $DB->insertId();

        $DB->insert(NotificationTarget::getTable(), [
            'items_id' => 4,
            'type' => 2,
            'notifications_id' => $notificationId,
            'is_exclusion' => 0
        ]);

        $DB->insert(Notification_NotificationTemplate::getTable(), [
            'notifications_id' => $notificationId,
            'mode' => Notification_NotificationTemplate::MODE_MAIL,
            'notificationtemplates_id' => $templateId
        ]);
   }

    // ##### Cron setup ##### //
    CronTask::register(
        SuperAsset::class,
        'createautomaticasset',
        HOUR_TIMESTAMP,
        [
            'comment'   => '',
            'mode'      => CronTask::MODE_EXTERNAL
        ]
    );

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

   // ##### Clean Notifications setup ##### //

   $notificationIterator = $DB->request([
        'SELECT' => ['id'],
        'FROM'   => Notification::getTable(),
        'WHERE'  => [
            'itemtype' => SuperAsset::getType()
        ],
    ]);

    $templatesIterator = $DB->request([
        'SELECT' => ['id'],
        'FROM'   => NotificationTemplate::getTable(),
        'WHERE'  => [
            'itemtype' => SuperAsset::getType()
        ],
    ]);

    $notificationsIds = [];
    foreach($notificationIterator as $notification) {
        $notificationsIds[] = $notification['id'];
    }

    $templatesIds = [];
    foreach($templatesIterator as $template) {
        $templatesIds[] = $template['id'];
    }

    $DB->delete(Notification::getTable(), [
        'itemtype' => SuperAsset::getType()
    ]);
    $DB->delete(NotificationTemplate::getTable(), [
        'itemtype' => SuperAsset::getType()
    ]);
    if (count($notificationsIds) > 0) {
        $DB->delete(Notification_NotificationTemplate::getTable(), [
            'notifications_id' => $notificationsIds
        ]);
        $DB->delete(NotificationTarget::getTable(), [
            'notifications_id' => $notificationsIds
        ]);
    }
    if(count($templatesIds) > 0) {
        $DB->delete(NotificationTemplateTranslation::getTable(), [
            'notificationtemplates_id' => $templatesIds
        ]);
    }

   return true;
}

/**
 * Allows the research of linked SuperAsset items on core Assets
 * @param string $itemtype The type of a core Asset
 * @return list<array<string,array<string,array<string, array<string, string>|string>>|bool|int|string>> An array of searchoptions for core Assets
 */
function plugin_jdplugintutorial_getAddSearchOptionsNew(string $itemtype): array
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

/**
 * Fallback method called when a Computer is purged. Deletes all relations between this computer and any SuperAsset item
 * @param CommonDBTM $item Purged computer
 * @return void
 */
function jdplugintutorial_purge_computer_called(CommonDBTM $item): void
{
    global $DB;
    $computerId = $item->fields['id'];

    $DB->delete(SuperAsset_Item::getTable(), [
        'items_id' => $computerId,
        'itemtype' => Computer::getType()
    ]);
}

/**
 * Displays a lign in the header of the Computer asset form
 * @param array<string, mixed> $params A set of informations about the targeted Computer form
 * @return string The HTML content to display
 */
function jdplugintutorial_pre_item_form_computer(array $params): string
{
    $nbItems = SuperAsset_Item::countForItem($params[Computer::class]);
    return '<tr><a href="/front/computer.form.php?id=5&forcetab=PluginJdplugintutorialSuperAsset$1">'.$nbItems.'</a></tr>';
}

/**
 * Adds new massive actions related to the SuperAsset on core assets
 * @param string $type The type of a core Asset
 * @return string[]
 */
function plugin_jdplugintutorial_MassiveActions(string $type): array
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
