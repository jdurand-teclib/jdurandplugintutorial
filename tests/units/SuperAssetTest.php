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

namespace GlpiPlugin\Jdplugintutorial\Tests;

use Computer;
use GlpiPlugin\Jdplugintutorial\SuperAsset;
use GlpiPlugin\Jdplugintutorial\SuperAsset_Item;
use PHPUnit\Framework\TestCase;

final class SuperAssetTest extends TestCase
{
    private function createComputerAsset(): int
    {
        $computer = new Computer();
        return $computer->add([
            "name" => "Testing Computer",
        ]);
    }

    private function createSuperAssetAsset(): int|bool
    {
        $superAsset = new SuperAsset();
        return $superAsset->add([
            "name" => "Matt Smith",
            "phonenumber" => "06 41 23 65 58",
            "created_at" => date("Y-m-d"),
        ]);
    }

    public function testSuperAssetCreationSuccessfull(): void
    {
        $superAsset = new SuperAsset();
        $superAssetId = $superAsset->add([
            "name" => "Peter Capaldi",
            "phonenumber" => "06 54 58 57 59",
            "created_at" => date("Y-m-d"),
        ]);

        $this->assertNotFalse($superAssetId);
    }

    public function testSuperAssetCreationFailureMissingName(): void
    {
        $superAsset = new SuperAsset();
        $superAssetId = $superAsset->add([
            "name" => "",
            "phonenumber" => "06 54 58 57 59",
            "created_at" => date("Y-m-d"),
        ]);

        $this->assertFalse($superAssetId);
    }

    public function testSuperAssetCreationFailurePhoneNumberWrongFormat(): void
    {
        $superAsset = new SuperAsset();
        $superAssetId = $superAsset->add([
            "name" => "David Tennant",
            "phonenumber" => "06 54 58 57 5",
            "created_at" => date("Y-m-d"),
        ]);

        $this->assertFalse($superAssetId);
    }

    public function testLinkBetweenComputerAndSuperAssetSuccessfull(): void
    {
        $superAssetItem = new SuperAsset_Item();
        $computerId = $this->createComputerAsset();
        $superAssetId = $this->createSuperAssetAsset();
        $superAssetItemId = $superAssetItem->add([
            "plugin_jdplugintutorial_superassets_id" => $superAssetId,
            "itemtype" => Computer::getType(),
            "items_id" => $computerId,
        ]);

        $this->assertNotFalse($superAssetItemId);
    }
}
