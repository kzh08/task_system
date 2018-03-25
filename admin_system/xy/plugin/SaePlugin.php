<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | sae的插件类，用于它提供的各种服务     				     	              |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-27       |
   +----------------------------------------------------------------------+
*/
final class SaePlugin {
    
    /**
     * 取得初始化的kvdb
    */
	public function getKvdb(){
		$kvdb = $GLOBALS['X_G']['plugin']['sae_kvdb'];
       
        if($kvdb){
        	return $kvdb;
        }else{
            $kv = new SaeKV();
            $ret = $kv->init();
            //保存起来，以便下次调用
            $GLOBALS['X_G']['plugin']['sae_kvdb'] = $kv;
            return $kv;
        }
	}
    
    /**
     * 获取access_token
    */
    public function getAccessToken(){
        //取得客户信息
        $cp 	= $this->cp;
        
        //取得kvdb支持
        $kvdb 	= $this->getKvdb();
        //设置在kvdb中保存的名称
        $key	= 'cp_access_token_'.$cp['id'];
        
        $ret = $kvdb->get($key);
        if($ret){
        	$result = $ret;
            
            //判断是否过期
            if($ret && time() - $ret['time'] > 7000){
                $kvdb->set($key, $result);	//已设置，因而去更新
           		$result = getToken($cp);
                return $result;
            }else{
                return $result;
            }
        }else{
            $result = plugin('sae')->getToken($cp);
            $kvdb->add($key, $result);	//未设置，因而直接添加
            return $result;
        }
    }
    
    /**
     * 访问接口地址获取值
     * param $url string	接口地址
    */
    public function getUrl($url){
        
		$fetch = $GLOBALS['X_G']['plugin']['sae_fetchurl'];
       
        if($fetch){
            
        }else{
       		 $fetch 	= new SaeFetchurl();
            //保存起来，以便下次调用
            $GLOBALS['X_G']['plugin']['sae_fetchurl'] = $fetch;
        }
        
        $content 	= $fetch->fetch($url);
        
        $result     = json_decode($content,true);
        
        return $result;
    }
    
    /**
     * 根据CP通过网址获取access_token
     * param $cp array	 CP信息数组
    */
    private function getToken($cp){
        //设置获取的URL
        echo $url    = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$cp['appid'].'&secret='.$cp['appsecret'];
        
        $f 			= new SaeFetchurl();
        $content 	= $f->fetch($url);
        
        $ret = json_decode($content);
        if($ret->errorcode){
        	$result = '';
            LOG::e('无法取得access_token，获取地址为:'.$url.'。来源[SaePlugin][getToken]。');
        }else{
        	$result = $ret->access_token;
        }
        
        return $result;
    }
}
?>