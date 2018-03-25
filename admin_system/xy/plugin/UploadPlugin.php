<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 文件上传插件类					    				     	          |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-27       |
   +----------------------------------------------------------------------+
*/

final class UploadPlugin{

	public $uploadBack	= false;//是否返回

	public function upload() {
		$dir	= $this->mkDir();
		foreach($_FILES AS $k => $file){
			$this->up($file, $dir);
		}
	}
	
	public function mkDir(){
		$attachDir	= "attachment";
		if (!is_dir($attachDir)) {
			mkdir($attachDir, 0755, true);
		}
		$yearDir	= $attachDir."/".date("Y");
		if (!is_dir($yearDir)) {
			mkdir($yearDir, 0755, true);
		}
		$monthDir	= $yearDir."/".date("m");
		if (!is_dir($monthDir)) {
			mkdir($monthDir, 0755, true);
		}
		$dayDir		= $monthDir."/".date("d");
		if (!is_dir($dayDir)) {
			mkdir($dayDir, 0755, true);
		}
		
		return $dayDir;
	}
	
	public function up($file, $dir){
		$fileArr	= explode(".", $file['name'][0]);
		$ext		= $fileArr[count($fileArr)-1];
		$newName	= $dir."/".date("YmdHis").rand(10000, 99999).".".$ext;
		if(move_uploaded_file($file['tmp_name'][0], $newName)){
			$arr	= array(
				"deleteType"	=> "DELETE",
				"deleteUrl"		=> rawurlencode($newName),
				"name"			=> $file['name'][0],
				"size"			=> $file['size'][0],
				"type"			=> $file['type'][0],
				"path"			=> __APP__."/".$newName,
				"url"			=> rawurlencode(__APP__."/".$newName),
			);
			
			$retArr	= array("files" => array($arr));
		}else{
			$retArr	= array();
		}
		if(!$this->$uploadBack){
			exit(json_encode($retArr));
		}else{
			return $retArr;
		}
	}
}
