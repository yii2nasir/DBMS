<?php
//ADD THIS IN ADVANCE/COMMON/COMPNETS/Database.php


namespace common\components;

use Yii;
use yii\base\Component;

class Database extends Component {

    protected $db;
    protected $active_group = 'local';
    protected $link;
    function __construct(){
       // echo 'sory';exit;
        $this->db['local'] = array(
            'dsn'   => '',
            'hostname' => 'localhost',//change host name
            'username' => 'root',		//change
            'password' => '',			//change
            'database' => 'yii2advanced',//change
            'dbprefix' => '',
        );
        $this->db['serverskm'] = array(
            'dsn'   => '',
            'hostname' => '109.235.64.249',
            'username' => 'conserv_skm',
            'password' => 'P@ssw0rd',
            'database' => 'conserv_skm',
            'dbdriver' => 'mysqli',
            'dbprefix' => '',
        ); 
    
    $this->link = mysqli_connect($this->db[$this->active_group]['hostname'], $this->db[$this->active_group]['username'], $this->db[$this->active_group]['password'],$this->db[$this->active_group]['database']);
    /*  if (!$link) {
    die('Could not connect: ' . mysqli_error());
    }
    echo 'Connected successfully';
    mysqli_close($link);*/
    }
    
	function query($query){
        $result=mysqli_query($this->link,$query);
        if($result){
            return $result;
        }else{
            return 0;
        }
    }
    
    function result($result){ 
        $res=array();
        while ($row = $result->fetch_assoc()) {
            $res[]=$row;
        }
        return $res;
    }
	
function con(){
        if (!$this->link) {
            die('Could not connect: ' . mysqli_error());
            }
            echo 'Connected successfully';
    }
function install(){
   
    $q[]="CREATE TABLE IF NOT EXISTS `blacklist` (`id` int(11) NOT NULL AUTO_INCREMENT,`email` varchar(70) NOT NULL,
        PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1";
    $q[]="create VIEW IF NOT EXISTS `fresh_list` AS SELECT `contact`.`id` AS `id`, `contact`.`name` AS `name`, `contact`.`email` AS `email`, `contact`.`phone` AS `phone`, `contact`.`country` AS `country` FROM `contact` WHERE ( ( NOT( `contact`.`email` IN( SELECT `log`.`email` FROM `log` ) ) ) AND( NOT( `contact`.`email` IN( SELECT `blacklist`.`email` FROM `blacklist` ) ) ) )";
    foreach($q as $v){
        $this->query($v);
    }
}
function find_insert($table_name,$key,$val){
		$query="INSERT INTO `$table_name` (`name`) 
SELECT 'id' 
FROM $table_name
WHERE NOT EXISTS (SELECT id FROM `$table_name` WHERE `$key`='$val') 
LIMIT 1;
select id from $table_name where `name`='$val'";
}
function insert($table_name,$data){
	/*$data=array(
    'id'=>'',
    'name'=>'saroj',
    'dep_id'=>'2'
INSERT INTO `teacher` (`id`, `name`, `dep_id`) VALUES (NULL, 'tks', '2');
);*/
$keys='';
$values='';
foreach($data as $k=>$v){
	$keys.='`'.$k.'`,';
	$values.="'".$v."',";
}
$keys=rtrim($keys,',');
$values=rtrim($values,',');
	$query= "INSERT INTO `{$table_name}` ({$keys}) VALUES ({$values})";
	//echo $query;
	$this->query($query);
}
    function __destruct(){
        mysqli_close($this->link);
        //echo "mysql_close";
    }
}

