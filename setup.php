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

use GlpiPlugin\Jdplugintutorial\SuperAsset;
use GlpiPlugin\Jdplugintutorial\SuperAsset_Item;
use GlpiPlugin\Jdplugintutorial\Config;
use GlpiPlugin\Jdplugintutorial\Profile;
use Computer;
use Glpi\Plugin\Hooks;
use Profile as Glpi_Profile;
use Config as Glpi_Config;

/** @phpstan-ignore theCodingMachineSafe.function (safe to assume this isn't already defined) */
define('PLUGIN_JDPLUGINTUTORIAL_VERSION', '0.0.2');

// Minimal GLPI version, inclusive
/** @phpstan-ignore theCodingMachineSafe.function (safe to assume this isn't already defined) */
define("PLUGIN_JDPLUGINTUTORIAL_MIN_GLPI_VERSION", "11.0.0");

// Maximum GLPI version, exclusive
/** @phpstan-ignore theCodingMachineSafe.function (safe to assume this isn't already defined) */
define("PLUGIN_JDPLUGINTUTORIAL_MAX_GLPI_VERSION", "11.0.99");

/**
 * Init hooks of the plugin.
 * REQUIRED
 */
function plugin_init_jdplugintutorial(): void
{

    /**
     * @global array<string,array<string,array<string,string>>> $PLUGIN_HOOKS
     */
    global $PLUGIN_HOOKS;

    // ##### Hooks ##### //

    //add menu hook

    $PLUGIN_HOOKS[Hooks::MENU_TOADD]['jdplugintutorial'] = [
        // insert into 'plugin menu'
        'plugins' => SuperAsset::class
    ];

    // callback a function (declared in hook.php)
    $PLUGIN_HOOKS[Hooks::ITEM_UPDATE]['jdplugintutorial'] = [
        'Computer' => 'jdplugintutorial_computer_updated'
    ];

    // callback a class method
    $PLUGIN_HOOKS[Hooks::ITEM_PURGE]['jdplugintutorial'] = [
        'Computer' => 'jdplugintutorial_purge_computer_called'
    ];

    // css & js
    $PLUGIN_HOOKS[Hooks::ADD_CSS]['jdplugintutorial'] = 'jdplugintutorial.css';
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['jdplugintutorial'] = 'js/common.js';

    // on ticket page (in edition)
    if (strpos($_SERVER['REQUEST_URI'], "ticket.form.php") !== false
        && isset($_GET['id'])) {
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['jdplugintutorial'] = 'js/ticket.js.php';
    }

    $PLUGIN_HOOKS[Hooks::PRE_ITEM_FORM]['jdplugintutorial'] = [SuperAsset::class, 'preItemForm'];

    $PLUGIN_HOOKS[Hooks::USE_MASSIVE_ACTION]['jdplugintutorial'] = true;

    // ##### Classes ##### //
    $relatedTypes = [Computer::class];

    Plugin::registerClass(SuperAsset_Item::class, [
        'addtabon' => $relatedTypes,
    ]);

    Plugin::registerClass(Config::class, [
        'addtabon' => Glpi_Config::class
    ]);

    Plugin::registerClass(Profile::class, [
        'addtabon' => Glpi_Profile::class
    ]);

    Plugin::registerClass(SuperAsset::class, [
        'notificationtemplates_types' => true,
    ]);
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array{
 *      name: string,
 *      version: string,
 *      author: string,
 *      license: string,
 *      homepage: string,
 *      requirements: array{
 *          glpi: array{
 *              min: string,
 *              max: string,
 *          }
 *      }
 * }
 */
function plugin_version_jdplugintutorial(): array
{
    return [
        'name'           => 'jdplugintutorial',
        'version'        => PLUGIN_JDPLUGINTUTORIAL_VERSION,
        'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
        'license'        => 'MIT',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_JDPLUGINTUTORIAL_MIN_GLPI_VERSION,
                'max' => PLUGIN_JDPLUGINTUTORIAL_MAX_GLPI_VERSION,
            ],
        ],
    ];
}

/**
 * Check pre-requisites before install
 * OPTIONAL
 */
function plugin_jdplugintutorial_check_prerequisites(): bool
{
    return true;
}

/**
 * Check configuration process
 * OPTIONAL
 *
 * @param bool $verbose Whether to display message on failure. Defaults to false.
 */
function plugin_jdplugintutorial_check_config(bool $verbose = false): bool
{
    // Your configuration check
    return true;

    // Example:
    // if ($verbose) {
    //    echo __('Installed / not configured', 'jdplugintutorial');
    // }
    // return false;
}
