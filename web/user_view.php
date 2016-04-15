<?php
if (!isset($logic)) {
    require_once("logic.php");
    $logic = new FemaleLogic();
}
?>
<div class="col-xs-12 center">
    <?
        $user_id= isset($_GET['user_id'])?$_GET['user_id']:GetUserID($_GET['username']);
        echo $logic->MakeBarChart($user_id,"user");
        echo $logic->GetAwards($user_id);
    ?>
</div>
