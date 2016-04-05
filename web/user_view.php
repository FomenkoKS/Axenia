<?php
if (!isset($logic)) {
    require_once("logic.php");
    $logic = new FemaleLogic();
}
?>
<div class="col-md-12 center">
    <?
        echo isset($_GET['user_id'])?$logic->MakeBarChart($_GET['user_id'],"user"):$logic->MakeBarChart(GetUserID($_GET['username']),"user");
    ?>
</div>
