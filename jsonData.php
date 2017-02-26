<?

if (isset($_POST['please'])) {
    require_once("SiteService.php");
    $site = new SiteService();
    switch ($_POST['please']) {
        case 'userlist':
            echo $site->getUserListJson($_POST['query']);
            break;
        case 'grouplist':
            echo $site->getGroupListJson($_POST['query']);
            break;
        case 'header':
            // не понятно используется ли
            echo $site->createHeaderView("user", $_POST);
            break;
    }
}
