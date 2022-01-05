<?php
# 基于PDO的二次封装

# 命名空间
// namespace core;

# 引入系统类：基于PDO的实现，需要引入第三个类
// use \PDO,\PDOStatement,\PDOException;

class MyPDO{
	private $pdo;			# 保存PDO类对象
	private $fetch_mode;	# 查询数据的模式：默认为关联数组
	public $error; 			# 记录的错误信息
	
	# 构造方法
	# 默认采用PDO异常和获取关联数组设定
	public function __construct($datebase_info = array(),$drivers = array()){
		$type = isset($database_info['type'])?$database_info['type']:'mysql';
		$host = isset($database_info['host'])?$database_info['host']:'dev.webpro.ltd';
		$port = isset($database_info['port'])?$database_info['port']:'3306';
		$user = isset($database_info['user'])?$database_info['user']:'taotaogps';
		$pass = isset($database_info['pass'])?$database_info['pass']:'taotaogps';
		$dbname = isset($database_info['dbname'])?$database_info['dbname']:'taotaogps';
		$charset = isset($database_info['charset'])?$database_info['charset']:'utf8';
		# fetchmode不能在初始化的时候实现，需要在得到PDOStatement类对象时设置
		$this->fetch_mode = isset($drivers[PDO::ATTR_DEFAULT_FETCH_MODE])?($drivers[PDO::ATTR_DEFAULT_FETCH_MODE]):(PDO::FETCH_ASSOC);
		
		#控制属性(增加异常处理模式)
		if(!isset($drivers[PDO::ATTR_ERRMODE])){
			$drivers[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		}		
		
		try{
            $this->pdo = @new PDO($type.':host='.$host.';port='.$port.';dbname='.$dbname.';charset='.$charset,$user,$pass,$drivers);
		}catch(PDOException $e){
            # 调用异常处理方法
			$this->my_exception($e);
		}
	}
	private function my_exception(PDOException $e){
		$this->error['file'] = $e->getFile();
		$this->error['line'] = $e->getLine();
		$this->error['error'] = $e->getMessage();
		# 返回false，让外部处理
		return false;
	}
	
	# 写操作
	public function my_exec($sql){
		try{
			return $this->pdo->exec($sql);
		}catch(PDOException $e){
			return $this->my_exception($e);
		}
	}
	
	# 获取自增长ID
	public function my_last_insert_id(){
		try{
			$id = $this->pdo->lastInsertId();
			
			# 主动抛出异常
			if(!$id) throw new PDOException('自增长ID不存在！');
			return $id;
		}catch(PDOException $e){
			return $this->my_exception($e);
		}
	}
	
	# 读方法:按条件进行单行或多行数据返回
	public function my_query($sql,$only = true){
		try{
			$stmt = $this->pdo->query($sql);
			# 设置查询模式
			$stmt->setFetchMode($this->fetch_mode);
		}catch(PDOException $e){
			return $this->my_exception($e);
		}
		
		# 数据解析
		if($only){
			return $stmt->fetch();
		}else{
			return $stmt->fetchAll();
		}
		
    }
	public function prepare_print($pre_sql,$val = array()){
		try{
			$stmt = $this->pdo->prepare($pre_sql);
			if(!$stmt) die('预处理指令执行失败!');
			$res = $stmt->execute($val);
			while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
				//生成器
				yield $row;
			}
		}catch(PDOException $e){
			//无法return
		}
	}
	public function prepare_noprint($pre_sql,$val = array()){
		try{
			$stmt = $this->pdo->prepare($pre_sql);
			if(!$stmt) die('预处理指令执行失败!');
			$res = $stmt->execute($val);
			if($res){
                $count = $stmt->rowCount();
				return $count;
			}else{
				return false;
			}
		}catch(PDOException $e){
		    echo $e;
			return $this->my_exception($e);
		}
	}

	public function prepare_optimism_lock($sql,$pre_sql,$val1 = array(),$val2 = array(),$version){
		try{
			//语句1：取出版本号
			$stmt1 = $this->pdo->prepare($sql);
			if(!$stmt1) die('预处理指令1执行失败!');
			$res1 = $stmt1->execute($val1);
			if($res1 > 0){

			}else{$row1=$stmt1->fetch(PDO::FETCH_ASSOC);
                $val2[$version]=$row1[$version];
                //语句2：更新语句，无输出
                $stmt2 = $this->pdo->prepare($pre_sql);
                if(!$stmt2) die('预处理指令2执行失败!');
                $res2 = $stmt2->execute($val2);
                if($res2 == 0){
                    prepare_optimism_lock($sql,$pre_sql,$val1,$val2,$version);
                }else{
                    return true;
                }
				return false;
			}
		}catch(PDOException $e){
			return $this->my_exception($e);
		}
	}
	
}




?>