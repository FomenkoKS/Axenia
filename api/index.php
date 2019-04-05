<?
/*require_once("../configs/axenia/config.php");

$chat_id = intval($_GET['chat_id']);
$mysqli = new mysqli("localhost", MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
$output = array();
$q = '';
switch ($_GET['get']) {
    case 'karma':
        $q = 'SELECT user_id,level,last_updated,last_time_voted FROM Karma where chat_id = ?';
        break;
    case 'rewards':
        $q = 'SELECT r.user_id, rt.code, r.description FROM Rewards r,Reward_Type rt where r.type_id=rt.id and r.group_id = ?';
        break;
}
$stmt = $mysqli->prepare($q);
$stmt->bind_param('i', $chat_id);
$stmt->execute();
if ($result = $stmt->get_result()) {
    while ($row = $result->fetch_assoc()) {
        array_push($output, $row);
    }
    $result->close();
}
$mysqli->close();
print_r(json_encode($output, true));
*/

?>