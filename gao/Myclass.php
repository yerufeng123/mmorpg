<?php
namespace gao1;
class Myclass{
	private $name;
	private $age;


	function __construct($name,$age){
		$this->name=$name;
		$this->age=$age;
	}
	
	function __get($property){
		$method='get'.$property;
		if(method_exists($this,$method)){
			return $this->$method();
		}
	}



	function getName(){
		return $this->name;
	}

	static function getAge(){
		return 'nihao';
	}
}