<div class="d-flex w-100 h-100 mx-auto flex-column">
<? include('header.php'); 
$chat_id=$_GET['chat_id'];
?>


<main role="main" class="mb-auto bg-light p-5 text-dark">
    <div class='row'>
        <div class="col">
            <h1><?php echo $site->getGroupName($chat_id); ?></h1>
        </div>
    </div>
    <div id="carouselIndicators" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php 
                $users=array_chunk($site->getTopUsers($chat_id),10);
                if(count($users)>1)
                    foreach($users as $n=>$u){
                        echo "<li data-target=\"#carouselIndicators\" data-slide-to=\"$n\"";
                        if($n==0)echo "class=\"active\"";
                        echo ">".($n+1)."</li>";
                    }
            ?>
        </ol>
        <div class="carousel-inner">
            
            <?php 
                foreach($users as $n=>$u){
                    echo "<div class=\"carousel-item";
                    if($n==0) echo " active";
                    echo "\">";
                    echo "
                        <table class=\"table table-hover \">
                            <thead>
                                <tr>
                                <th scope=\"col\">#</th>
                                <th scope=\"col\">Name</th>
                                <th scope=\"col\">Karma</th>
                                </tr>
                            </thead>
                        <tbody>
                        ";
                    foreach($u as $i=>$v){
                        echo "<tr><th scope=\"row\">".($n*10+$i+1)."</th>";
                        echo "<td>".$site->beutifyName($v[0],$v[1],$v[2])."</td>";
                        echo "<td>$v[3]</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                }
            ?>
        </div>
    </div>
</main>


<? include('footer.php'); ?>
</div>