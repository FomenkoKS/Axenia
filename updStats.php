<?php
require_once("SiteService.php");
$site = new SiteService();
$type = $site->updateStats($site->getStats());
?>
