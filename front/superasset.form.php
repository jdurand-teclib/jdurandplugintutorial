<?php

use GlpiPlugin\Jdplugintutorial\SuperAsset;
use Html;

include ('../../../inc/includes.php');

$superasset = new SuperAsset();

if (isset($_POST["add"])) {
    $newID = $superasset->add($_POST);

    if ($_SESSION['glpibackcreated']) {
        Html::redirect(SuperAsset::getFormURL()."?id=".$newID);
    }
    Html::back();

} else if (isset($_POST["delete"])) {
    $superasset->delete($_POST);
    $superasset->redirectToList();

} else if (isset($_POST["restore"])) {
    $superasset->restore($_POST);
    $superasset->redirectToList();

} else if (isset($_POST["purge"])) {
    $superasset->delete($_POST, 1);
    $superasset->redirectToList();

} else if (isset($_POST["update"])) {
    $superasset->update($_POST);
    \Html::back();

} else {
    // fill id, if missing
    isset($_GET['id'])
        ? $ID = intval($_GET['id'])
        : $ID = 0;

    // display form
    Html::header(
       SuperAsset::getTypeName(),
       $_SERVER['PHP_SELF'],
       "plugins",
       SuperAsset::class,
       "SuperAsset"
    );
    $superasset->display(['id' => $ID]);
    Html::footer();
}