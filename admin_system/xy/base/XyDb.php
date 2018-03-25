<?php

/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 数据库连接类	      	 	  	  				                          |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-26       |
   +----------------------------------------------------------------------+
*/

/**
 * 数据库连接类
 */
class Db
{

    /**
     * 主键名称
     *
     * @var int
     */
    private $pk;

    /**
     * 数据库实例
     */
    public $connection;

    /**
     * 数据表名称
     *
     * @var string
     */
    public $tableName;

    /**
     * 缓存路径
     *
     * @var string
     */
    protected $cachePath = './cache/';

    /**
     * 缓存扩展名
     *
     * @var string
     */
    protected $cacheFileExt = "php";

    /**
     * 缓存文件名
     *
     * @var string
     */
    protected $cacheFileName;

    /**
     * 缓存更新时间秒数
     *
     * @var int
     */
    protected $cacheLimitTime = 60;

    /**
     * 数据返回类型, 1代表数组, 2代表对象
     *
     * @var string
     */
    protected $returnType = 1;

    /**
     * 存储预插入语句
     *
     * @var array
     */
    public $preInsertSql = array();

    /**
     * 构造函数-连接数据库
     */
    public function __construct()
    {
        $dbConfig = $GLOBALS['X_G']["db"];

        if (!$this->connection = mysql_connect($dbConfig['host'] . ":" . $dbConfig['port'], $dbConfig['username'], $dbConfig['password'])) {
            $mess = '数据库连接错误:';
            //如果为调试模式则输出异常信息
            if ($GLOBALS['X_G']['debug']) {
                $mess .= mysql_error();
            }

            require(X_PATH . '/exception/DbException.php');
            throw new DbException($mess);
        }

        if (!mysql_select_db($dbConfig['database'], $this->connection)) {
            require(X_PATH . '/exception/DbException.php');
            throw new DbException('数据库不存在，请确认库名称是否正确:');
        }
        $this->cachePath = isset($dbConfig['cachePath']) ? $dbConfig['cachePath'] : $this->cachePath;
        $this->cacheLimitTime = isset($dbConfig['cacheTime']) ? $dbConfig['cacheTime'] : $this->cacheLimitTime;
        $this->exec("set names utf8");
    }

    /**
     * 执行语句
     *
     * @param string $sql SQL语句
     *
     * @return int 返回相关ID值
     *
     * @throws  DbException
     */
    public function exec($sql)
    {
        $GLOBALS['X_G']['backSql'][] = $sql;

        if ($result = mysql_query($sql, $this->connection)) {
            return $result;
        } else {
            $mess = '数据库语句执行异常:';
            //如果为调试模式则输出SQL语句
            if ($GLOBALS['X_G']['debug']) {
                $mess .= $sql;
            }
            require(X_PATH . '/exception/DbException.php');
            throw new DbException($mess);
        }
    }

    /**
     * 根据主键取得一条记录
     *
     * @param int|string $value 主键值
     * @param string     $key   主键名，默认为id，可以设为其它值，也可支持非主键，建议非主键采用根据字段取值方法
     *
     * @return array 数据库记录
     *
     */
    public function findById($value, $key = 'id')
    {
        if ($value == '') {
            return false;
        }

        $sql = "select * from " . $this->tableName . " where `" . $key . "` = '" . $value . "' limit 1";

        $result = $this->exec($sql);

        return mysql_fetch_array($result, MYSQL_ASSOC);
    }

    /**
     * 根据条件返回单个字段
     *
     * @param array|string $where 条件语句 可为数组或字符串
     * @param string       $field 字段名
     *
     * @return array 结果集
     */

    public function findField($where, $field)
    {
        $tmpSql = $this->getWhere($where);

        $sql = "SELECT " . $field . " FROM " . $this->tableName . " " . $tmpSql;
        $result = $this->exec($sql);
        $list = mysql_fetch_array($result, MYSQL_ASSOC);

        return $list[$field];
    }

    /**
     * 根据条件返回单条记录
     *
     * @param array|string $where 条件语句 可为数组或字符串
     * @param string       $field 条件语句 字段
     * @param string       $order 排序条件 field1 asc
     *
     * @return array 结果集
     */
    public function find($where = '', $field = '*', $order = '')
    {
        //为了安全 不允许不带条件的直接查询
        if (empty($where)) {
            return false;
        };

        $list = $this->findAll($where, $field, $order, 1);

        return $list[0];
    }

    /**
     * 根据条件返回多条记录
     *
     * @param array|string $where 条件语句 可为数组或字符串
     * @param string       $field 条件语句 字段
     * @param string       $order 排序条件 field1 asc
     * @param string       $limit 条数限制 0,10
     *
     * @return array 结果集
     *
     */
    public function findAll($where = '', $field = '*', $order = '', $limit = '')
    {
        $tmpSql = $this->getWhere($where);

        $sql    = "SELECT " . $field . " FROM " . $this->tableName . " " . $tmpSql;

        $sql    .= !empty($order) ? " ORDER BY " . $order : "";

        $sql    .= !empty($limit) ? " LIMIT " . $limit : "";

        $list   = '';

        if($GLOBALS['X_G']['cache'] === TRUE){
            $this->setCacheFileName($sql);
            $list	= $this->readCache();
        }

        //如果读取失败, 或者没有开启缓存
        if (is_null($list)){
            $result	= $this->exec($sql);

            while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
                $list[]	= $row;
            }

            //如果开启了缓存, 那么就写入
            if ($GLOBALS['X_G']['cache'] === TRUE){
                $this->writeCache($list);
            }
        }

        $result = $this->exec($sql);

        $list   = '';

        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $list[] = $row;
        }

        return $list;
    }

    /**
     * 根据条件返回记录条数
     *
     * @param array|string $where 条件语句 可为数组或字符串
     *
     * @return array 结果集
     */
    public function num($where = '')
    {
        $tmpSql = $this->getWhere($where);

        $sql = "SELECT count(1) FROM " . $this->tableName . " " . $tmpSql;
        $result = $this->exec($sql);
        $row = mysql_fetch_row($result);
        return $row[0];
    }

    /**
     * 插入数据
     *
     * @param array $data 数组，键为字段名，值为字段值 array("field"=>"value")
     *
     * @return int 自增值，如果id为非自增字段，则返回0
     */
    public function insert($data)
    {
        if (empty($data)) {
            return false;
        }

        $fields = '';
        $values = '';

        foreach ($data as $key => $value) {
            $fields .= empty($fields) ? "`" . $key . "`" : ",`" . $key . "`";

            if(!is_null($value)){
                $value = "'" . addslashes($value) . "'";
            }else{
                $value = 'NULL';
            }

            $values .= empty($values) ? $value : "," . $value;
        }

        $sql = "INSERT INTO " . $this->tableName . " (" . $fields . ") VALUES (" . $values . ")";

        $this->exec($sql);

        return $this->getInsertid();
    }

    public function preInsert($data)
    {
        if (empty($data)) {
            return false;
        }

        $fields = '';
        $values = '';

        foreach ($data as $key => $value) {
            $fields .= empty($fields) ? "`" . $key . "`" : ",`" . $key . "`";

            if(!is_null($value)){
                $value = "'" . addslashes($value) . "'";
            }else{
                $value = 'NULL';
            }

            $values .= empty($values) ? $value : "," . $value;
        }

        if($this->preInsertSql[$this->tableName] == ''){
            $this->preInsertSql[$this->tableName] = "INSERT INTO " . $this->tableName . " (" . $fields . ") VALUES (" . $values . ")";
        }else{
            $this->preInsertSql[$this->tableName] .= ",(" . $values . ")";
        }
    }

    public function preInsertPost(){
        foreach($this->preInsertSql as $value){
            $this->exec($value);
        }

        //清空预插入缓存
        $this->preInsertSql = array();
    }

    /**
     * 更新字段
     *
     * @param array $data  二维数组形式的要更新的值 array("field"=>"value")
     * @param array $where 二维数组形式的条件语句   array("name"=>"xiaoy")
     *
     * @return int 更新的条数，没更新则返回0
     */
    public function update($data, $where)
    {
        if (empty($data)) {
            return false;
        }

        $tmpSql = $this->getWhere($where);

        $valArr = '';

        foreach ($data as $key => $value) {
            $valArr[] = "`" . $key . "` = '" . addslashes($value) . "'";
        }

        if ($tmpSql == '') {
            return false;
        }

        $sql = "UPDATE " . $this->tableName . " SET " . implode(", ", $valArr) . " " . $tmpSql;

        return $this->exec($sql);
    }

    /**
     * 根据条件删除数据
     *
     * @param array|string $where 条件语句 可为数组或字符串
     *
     * @return int 删除的条数，没删除则返回0
     */
    public function delete($where)
    {
        $tmpSql = $this->getWhere($where);

        $sql = "DELETE FROM " . $this->tableName . " " . $tmpSql;
        if (!preg_match('/WHERE/', $sql)) {
            exit('删除语句不包含where条件，不允许删除');
        }
        return $this->exec($sql);
    }

    /**
     * 获取某个字段默认值
     *
     * @param string $columnNameValue 字段名
     *
     * @return array 字段默认值
     */
    public function enum($columnNameValue)
    {
        $tmpSql = "WHERE FIELD ='$columnNameValue'";
        $sql = "SHOW COLUMNS FROM " . $this->tableName . " " . $tmpSql;
        $result = $this->exec($sql);
        $row = mysql_fetch_assoc($result);
        $str = $row["Type"];
        $str = substr($str, 5, strlen($str) - 6);
        $a = explode(",", $str);

        $returnArr = '';

        for ($i = 0; $i < count($a); $i++) {
            $this->removeQuote($a[$i]); //从字符串中去除单引号
            $returnArr[$i] = $a[$i];
        }
        return $returnArr;
    }

    /**
     * 从字符串中去除单引号
     *
     * @param string &$str 要转换的字符串的实参
     *
     * @return string 转换后的字符串
     */
    public function removeQuote(&$str)
    {
        if (preg_match("/^\'/", $str)) {
            $str = substr($str, 1, strlen($str) - 1);
        }
        if (preg_match("/\'$/", $str)) {
            $str = substr($str, 0, strlen($str) - 1);
        }
        return $str;
    }

    /**
     * 根据当前动态文件生成缓存文件名
     *
     * @param string $fileName 文件名
     *
     * @return string 缓存文件名
     */
    public function setCacheFileName($fileName)
    {
        $this->cacheFileName = $this->cachePath . strtoupper(md5($fileName)) . "." . $this->cacheFileExt;
    }

    /**
     * 获取缓存文件名
     *
     * @return string 缓存文件名
     */
    public function getCacheFileName()
    {
        return $this->cacheFileName;
    }

    /**
     * 读取缓存
     *
     * @return string mixed   如果读取成功返回缓存内容, 否则返回NULL
     */
    protected function readCache()
    {
        $file = $this->getCacheFileName();
        if (file_exists($file)) {
            //缓存过期
            if ((filemtime($file) + $this->cacheLimitTime) < time()) {
                @unlink($file);
                return null;
            }
            if (1 === $this->returnType) {
                $row = include $file;
            } else {
                $data = file_get_contents($file);
                $row = unserialize($data);
            }
            return $row;
        }
        return null;
    }

    /**
     * 写入缓存
     *
     * @param  string $data 缓存内容
     *
     * @return boolean 是否成功
     */
    public function writeCache($data)
    {
        $file = $this->getCacheFileName();
        if ($this->makeDir(dirname($file))) {
            if (1 === $this->returnType) {
                $data = '<?php return ' . var_export($data, true) . ';?>';
            } else {
                $data = serialize($data);
            }
        }
        return file_put_contents($file, $data);
    }

    /**
     * 清除缓存文件
     *
     * @param string $fileName 指定文件名(含函数)或者all（全部）
     *
     * @return boolean 返回：清除成功返回true，反之返回false
     */
    public function clearCache($fileName = "all")
    {
        if ($fileName != "all") {
            if (file_exists($fileName)) {
                return @unlink($fileName);
            } else {
                return false;
            }
        }
        if (is_dir($this->cachePath)) {
            if ($dir = @opendir($this->cachePath)) {
                while ($file = @readdir($dir)) {
                    $check = is_dir($file);
                    if (!$check) {
                        @unlink($this->cachePath . $file);
                    }
                }
                @closedir($dir);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 连续建目录
     *
     * @param $dir  string      目录字符串
     * @param $mode int|string  权限数字
     *
     * @return boolean  顺利创建或者全部已建返回true，其它方式返回false
     */
    public function makeDir($dir, $mode = "0777")
    {
        if (!$dir) {
            return 0;
        }
        $dir = str_replace("\\", "/", $dir);

        $mDir = "";
        foreach (explode("/", $dir) as $val) {
            $mDir .= $val . "/";
            if ($val == ".." || $val == "." || trim($val) == "") {
                continue;
            }

            if (!file_exists($mDir)) {
                if (mkdir($mDir, $mode)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 获取影响行数
     *
     * @return int 影响的行数
     */
    public function getAffect()
    {
        return mysql_affected_rows($this->connection);
    }

    /**
     * 返回当前插入记录的主键ID
     *
     * @return int 主键ID
     */
    public function getInsertId()
    {
        return mysql_insert_id($this->connection);
    }

    /**
     * 返回where语句
     *
     * @param array|string $where 条件语句 可为数组或字符串
     *
     * @return string 查询语句
     */
    public function getWhere($where)
    {
        $tmpSql = '';

        if (!empty($where)) {
            if (is_numeric($where)) {
                $pk = $this->getPk();
                $tmpSql = " WHERE `" . $pk . "` = '" . $where . "'";
            } elseif (is_array($where)) {
                $condition = '';

                foreach ($where as $key => $value) {
                    $condition[] = "`" . $key . "` = '" . $value . "'";
                }
                if (count($condition) > 0) {
                    $tmpSql = " WHERE " . implode(" AND ", $condition);
                }
            } else {
                if (null != $where || '' != $where) {
                    $tmpSql = " WHERE " . $where;
                }
            }
        } else {
            $tmpSql = "";
        }

        return $tmpSql;
    }

    /**
     * 获取数据表主键
     *
     * @return int 表的主键
     */
    public function getPk()
    {
        if (!empty($this->pk[$this->tableName])) {
            return $this->pk[$this->tableName];
        } else {
            $sql    = "SELECT * FROM " . $this->tableName . " LIMIT 1";
            $result = mysql_query($sql, $this->connection);
            $nums   = mysql_num_fields($result);

            $name   = '';

            for ($i = 0; $i < $nums; $i++) {
                $flags = mysql_field_flags($result, $i);
                $name = mysql_field_name($result, $i);
                if (strstr($flags, 'primary_key')) {
                    break;
                }
            }

            $this->pk[$this->tableName] = $name ? $name : "id";
            return $this->pk[$this->tableName];
        }
    }

    /**
     * 析构
     */
    public function __destruct()
    {

    }
}

