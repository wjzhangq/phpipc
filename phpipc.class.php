<?php
class phpipc implements ArrayAccess, Countable
{
	protected $orgArr = array();
	protected $shm_id = 0;
	protected $offset = 1;
	protected $size = 0;
	
	const MAX = 100;
	
	
	
	//初始化
	function __construct($id=''){
		if (empty($id)){
			$id = __FILE__;
		}
		$shm_key = ftok($id, 't');
		$shm_size = self::MAX + $this->offset;

		$shm_id = shmop_open($shm_key, 'c', 0644, $shm_size);
		if ($shm_id === FALSE){
			throw new Exception('shmop open failure');
		}
		$this->shm_id = $shm_id;

		$raw_size = shmop_read($this->shm_id, 0, $this->offset);
		$this->size = ord($raw_size);
	}

	function inc($pos, $inc=1){

	}

	function accquire(){
		return true;
	}


	function release(){
		return false;
	}
	
	

	//implements arrayAccess
	function offsetExists($offset){
		return $offset > $this->size ? false : true;
	}

	function offsetGet($offset){
		if ($offset > $this->size){
			$ret = 0;
		} else {
			$raw_buff = shmop_read($this->shm_id, $this->offset + 4 * $offset, 4);
			$raw_ret = unpack('I', $raw_buff);
			$ret = current($raw_ret);
		}

		return $ret;
	}


	function offsetSet($offset, $value){
		$raw_buff = pack('I', $value);
		if ($offset > $this->size){
			if ($offset * 4 > self::MAX){
				throw new Exception('the big offset is '. (self::MAX / 4));
			}
			$this->size = $offset;
			$raw_size = chr($this->size);
			$ret = shmop_write($this->shm_id,$raw_size, 0);
			if ($ret === false){
				throw new Exception('change size falure');
			}
		}

		$ret = shmop_write($this->shm_id, $raw_buff, $this->offset + 4 * $offset);
		return $ret;
	}


	function offsetUnset($offset){
		if ($offset < 1){
			$ret = shmop_delete($this->shm_id);
		} else if ($offset < $size) {
			$this->size = $offset;
			$raw_size = chr($this->size);
			$ret = shmop_write($this->shm_id,$raw_size, 0);
			if ($ret === false){
				throw new Exception('change size falure');
			}
		} else {
			$ret = true;
		}
		
		
		return $ret;
	}

	//implements Countable
	function count(){
		return $this->size;
	}
	
}

$obj = new phpipc();
$obj[0] = $obj[0] + 1;
var_dump($obj[0]);


?>