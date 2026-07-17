<?php

namespace GlpiPlugin\Jdplugintutorial\Tests;

use Computer;
use GlpiPlugin\Jdplugintutorial\SuperAsset;
use GlpiPlugin\Jdplugintutorial\SuperAsset_Item;
use PHPUnit\Framework\TestCase;

final class SuperAssetTest extends TestCase{

    private function createComputerAsset(): int
    {
        $computer = new Computer();
        $computerId = $computer->add([
            "name" => "Testing Computer"
        ]);
        return $computerId;
    }

    private function createSuperAssetAsset(): int
    {
        $superAsset = new SuperAsset();
        $superAssetId = $superAsset->add([
            "name" => "Matt Smith",
            "phonenumer" => "06 41 23 65 58",
            "created_at" => date("Y-m-d"),
        ]);
    }

    public function testSuperAssetCreationSuccessfull(): void
    {
        $superAsset = new SuperAsset();
        $superAssetId = $superAsset->add([
            "name" => "Peter Capaldi",
            "phonenumer" => "06 54 58 57 59",
            "created_at" => date("Y-m-d"),
        ]);

        $this->assertNotFalse($superAssetId);
    }

    public function testSuperAssetCreationFailureMissingName(): void
    {
        $superAsset = new SuperAsset();
        $superAssetId = $superAsset->add([
            "name" => "",
            "phonenumer" => "06 54 58 57 59",
            "created_at" => date("Y-m-d"),
        ]);

        $this->assertFalse($superAssetId);
    }

    public function testSuperAssetCreationFailurePhoneNumberWrongFormat(): void
    {
        $superAsset = new SuperAsset();
        $superAssetId = $superAsset->add([
            "name" => "David Tennant",
            "phonenumer" => "06 54 58 57 5",
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