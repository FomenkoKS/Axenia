<?php
if (!isset($site)) {
    require_once("SiteService.php");
    $site = new SiteService();
}
?>
<div class="col-xs-12 center">
    <?
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $site->getUserID($_GET['username']);
    echo $site->createCharBarView($user_id, "user");
    echo $site->createRewardView($user_id);
    ?>
</div>
