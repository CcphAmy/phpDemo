<?php
	$newDB =new Mysql("localhost","root","","get_baidu_pa");//构造函数
    class Mysql{
        private $host;//服务器地址
        private $root;//用户名
        private $password;//密码
        private $database;//数据库名
         
		function __construct($host,$root,$password,$database){//构造函数
		    $this->host = $host;
		    $this->root = $root;
		    $this->password = $password;
		    $this->database = $database;
		    $this->connect();//function connect
		}

		// connect and close
		function connect(){
		    $this->conn = mysql_connect($this->host,$this->root,$this->password) or die("DB Connnection Error !".mysql_error());
		    mysql_select_db($this->database,$this->conn);
		    mysql_query("set names utf8");
		}
		         
		function dbClose(){
		    mysql_close($this->conn);
		}


		//封装Sql原语句
		function query($sql){
		    return mysql_query($sql);
		}
		        
		function myArray($result){
		    return mysql_fetch_array($result);
		}
		        
		function rows($result){
		    return mysql_num_rows($result);
		}

		//创建表(CREATE TABLE IF NOT EXISTS `from_323` )
		function createTable($newTableName,$tableText){
			// echo "CREATE TABLE IF NOT EXISTS `$newTableName` ($tableText) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			return $this->query("CREATE TABLE IF NOT EXISTS `$newTableName` ($tableText) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		}
		//删除表(DROP TABLE IF EXISTS `from_323`;)
		function dropTable($tableName)
		{
			// echo "DROP TABLE IF EXISTS `$tableName`;";
			return $this->query("DROP TABLE IF EXISTS `$tableName`;");
		}

		//查询
		function select($tableName,$condition){
		    return $this->query("SELECT * FROM $tableName $condition");
		}
		//插入
		function insert($tableName,$fields,$value){
			// echo "INSERT INTO $tableName $fields VALUES $value";
		    $this->query("INSERT INTO `$tableName` $fields VALUES $value");
		}
		//修改与更新
		function update($tableName,$change,$condition){
		    $this->query("UPDATE $tableName SET $change $condition");
		}
		//删除
		function delete($tableName,$condition){
		    $this->query("DELETE FROM $tableName $condition");
		}
    }
?>