<?php
if (!isset($logic)) {
    require_once("logic.php");
    $logic = new FemaleLogic();
}
?>
<div class="col-md-12 center">
    <?
    echo $logic->MakeBarChart($_GET['group_id'], "group");
    ?>
</div>
