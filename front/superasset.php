<?php

include('../../../inc/includes.php');

use GlpiPlugin\Jdplugintutorial\SuperAsset;
use Search;
use Html;

Html::header(
    SuperAsset::getTypeName(),
    $_SERVER['PHP_SELF'],
    "plugins",
    SuperAsset::class,
    "superasset",
);
Search::show(SuperAsset::class);
Html::footer();
