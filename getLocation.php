<?php
include "./header.php";
include "./MyPDO.php";

$m = new MyPDO();

$pre_sql = "select * from gpslist";
$val = array();
$res = $m->my_query($pre_sql, $val);
if ($res) {
//    print_r($res);
//    $arr = array();
//    $i=0;
//    foreach($res as $row){
//        print_r($row);
//        $insert_string='"uuid":'.'"'.$row["uuid"].'",';
//        $row["item"] = substr_replace($row["item"], $insert_string, 1, 0);
//        $insert_string='"createTime":'.'"'.$row["createTime"].'",';
//        $row["item"] = substr_replace($row["item"], $insert_string, 1, 0);
//        $arr[$i] = json_decode($row["item"]);
//        $i++;
//    }
    $op = json_encode($res, JSON_UNESCAPED_UNICODE);
    echo $op;
} else {
    echo json_encode([
        'message' => 'error',
        'result' => '查询失败'
    ]);
}
