<?php

/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | xy框架服务基础类									      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-26       |
   +----------------------------------------------------------------------+
*/

class XyService
{

    /**
     * 数据库实例
     */
    public $db;

    /**
     * 表名称
     */
    public $tableName;

    /**
     * 服务初始化，如果有任何动作需要于服务初始化时执行，则重写该方法即可。
     */
    public function init()
    {

    }

    /**
     * 构造函数
     *
     * @param $table    string       表名称
     */
    public function __construct($table)
    {
        $this->init();

        //由于数据库表不一致的原因，必须新建数据库对象
        $this->db               = db();
        $this->tableName        = $table;

        $this->db->tableName    = $table;
    }

    /**
     * 使用数据库查询语句
     *
     * @param $sql string 要执行的语句
     * @return array
     */
    public function doSql($sql)
    {
        return $this->db->exec($sql);
    }

    /**
     * 使用数据库查询语句
     *
     * @param $sql string 要执行的语句
     * @return array
     */
    public function doSqlArr($sql)
    {
        $rows = $this->doSql($sql);

        $result = array();

        while ($row = mysql_fetch_array($rows, MYSQL_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * 根据ID获取记录
     *
     * @param $value string 主键的值
     * @param $key   string 主键的键名
     * @return array
     */
    public function getById($value, $key = 'id')
    {
        return $this->db->findById($value, $key);
    }

    /**
     * 获取记录数
     *
     * @param $where array|string 可为数组或字符串
     * @return int
     */
    public function getNum($where)
    {
        return $this->db->num($where);
    }

    /**
     * 根据条件返回单个字段
     *
     * @param $where array|string 可为数组或字符串
     * @param $field string       字段名
     * @return array
     */

    public function getField($where, $field)
    {
        return $this->db->findField($where, $field);
    }

    /**
     * 根据条件返回单条记录
     *
     * @param $where  array|string 可为数组或字符串
     * @param $field  string       字段名 默认为全部
     * @param $order  string       排序方式
     * @return array
     */
    public function get($where = '', $field = '*', $order = '')
    {
        return $this->db->find($where, $field, $order);
    }

    /**
     * 根据条件返回多条记录
     *
     * @param $where array|string 可为数组或字符串
     * @param $field string 字段
     * @param $order string 排序
     * @param $limit string 个数
     * @return array
     */
    public function getAll($where = '', $field = '*', $order = '', $limit = '')
    {
        return $this->db->findAll($where, $field, $order, $limit);
    }

    /**
     * 插入数据
     *
     * @param $data array 键为字段名，值为字段值 array("field"=>"value")
     * @return int|boolean
     */
    public function insert($data)
    {
        return $this->db->insert($data);
    }


    /**
     * 插入数据
     *
     * @param $data array 键为字段名，值为字段值 array("field"=>"value")
     * @return int|boolean
     */
    public function I($data)
    {
        return $this->insert($data);
    }

    /**
     * 更新字段
     *
     * @param $where array|string 可为数组或字符串        array("name"=>"xiaoy")
     * @param $data  string       二维数组形式的要更新的值  array("field"=>"value")
     * @return int|boolean
     */
    public function update($data, $where)
    {
        return $this->db->update($data, $where);
    }

    /**
     * 更新字段
     *
     * @param $where array|string 可为数组或字符串        array("name"=>"xiaoy")
     * @param $data  string       二维数组形式的要更新的值  array("field"=>"value")
     * @return int|boolean
     */
    public function U($data, $where)
    {
        return $this->update($data, $where);
    }

    /**
     * 根据条件删除数据
     *
     * @param $where array|string 可为数组或字符串
     * @return int|boolean
     */
    public function delete($where)
    {
        return $this->db->delete($where);
    }

    public function D($where)
    {
        return $this->delete($where);
    }

    /**
     *获取字段默认值
     */
    public function enumToArr($columnNameValue)
    {
        return $this->db->enum($columnNameValue);
    }

    /**
     * 根据条件取得分页后的结果集
     *
     * @param $where array|string       条件语句 可为数组或字符串
     * @param $field string             字段名 默认为全部
     * @param $order string             排序方式
     * @return array
     */
    public function getPage($where = '', $field = '*', $order = '')
    {
        return $this->db->find($where, $field, $order);
    }

    /**
     * 析构
     */
    public function __destruct()
    {

    }
}

