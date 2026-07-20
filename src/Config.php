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

use CommonGLPI;
use Session;
use Glpi\Application\View\TemplateRenderer;
use Config as Glpi_Config;

class Config extends Glpi_Config
{
    public static function getTypeName($nb = 0): string
    {
        return __('Super Asset configurations', 'jdplugintutorial');
    }

    /**
     * Returns a set of configurations for the current context
     * @return array set of configurations.
     */
    public static function getConfig(): array
    {
        return Glpi_Config::getConfigurationValues('plugin:jdplugintutorial');
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item->getType() === Glpi_Config::class) {
            return self::createTabEntry(self::getTypeName());
        }

        return '';
    }

    public static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0,
    ): bool {
        if ($item->getType() === Glpi_Config::class) {
            self::showForConfig($item, $withtemplate);
        }

        return true;
    }

    public static function showForConfig(
        CommonGLPI $config,
        int $withtemplate = 0,
    ): void {

        if (self::canView()) {

            $current_config = self::getConfig();
            $canedit        = Session::haveRight(self::$rightname, UPDATE);

            TemplateRenderer::getInstance()->display('@jdplugintutorial/config.html.twig', [
                'current_config' => $current_config,
                'can_edit'       => $canedit,
            ]);
        }
    }
}
