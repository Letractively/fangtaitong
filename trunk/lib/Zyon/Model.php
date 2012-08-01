<?php
/**
 * @version    $Id$
 */
class Zyon_Model
{
    /**
     * @var array
     */
    protected $_caches = array();

    /**
     * @var array
     */
    protected $_dbases = array();

    /**
     * @var array
     */
    protected $_tnames = array('prefix' => '', 'tnames' => array());

    /**
     * _dbn
     * 
     * @var mixed
     */
    protected $_dbn;

    /**
     * _tbl
     * Short table name.
     * 
     * @var string
     */
    protected $_tbl;

    /**
     * _pre
     * Prefix of table field.
     * 
     * @var string
     */
    protected $_pre;

    /**
     * __construct
     * 
     * @param string $tbl
     * @param string $dbn
     * @return void
     */
    public function __construct($tbl, $dbn)
    {
        $this->_tbl == '' AND $this->_tbl = (string)$tbl;
        $this->_dbn == '' AND $this->_dbn = (string)$dbn;

        if ($this->_pre == '' && $this->_tbl != '')
        {
            foreach (explode('_', strtr($this->_tbl, '.', '_')) as $val)
            {
                $this->_pre .= $val[0];
            }
            $this->_pre .= '_';
        }

        $this->_prepare();
    }

    /**
     * __clone
     * 
     * @return void
     */
    protected function __clone()
    {
    }

    /**
     * _prepare
     * 
     * @return void
     */
    protected function _prepare()
    {
    }

    /**
     * cache
     * 
     * @param string $name 
     * @param Zend_Cache_Core $cache 
     * @return Zend_Cache_Core
     */
    public function cache($name = 'memcachedb', Zend_Cache_Core $cache = null)
    {
        if ($cache === null)
        {
            if (!isset($this->_caches[$name]))
            {
                $this->_caches[$name] = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getCache($name);
            }

            return $this->_caches[$name];
        }

        $this->_caches[$name] = $cache;
    }

    /**
     * dbase
     * 
     * @param string $name 
     * @param Zend_Db_Adapter_Abstract $adapter 
     * @return Zend_Db_Adapter_Abstract
     */
    public function dbase($name = null, Zend_Db_Adapter_Abstract $dbase = null)
    {
        if ($dbase === null)
        {
            if ($name == '')
            {
                $name = $this->_dbn;
            }

            if (!isset($this->_dbases[$name]))
            {
                $this->_dbases[$name] = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getDbAdapter($name);
            }

            return $this->_dbases[$name];
        }

        $this->_dbases[$name] = $dbase;
    }

    /**
     * tname
     * 
     * @param string $name 
     * @param string $tname 
     * @return string
     */
    public function tname($name, $tname = null)
    {
        if ($tname === null)
        {
            if (isset($this->_tnames['tnames'][$name]))
            {
                return $this->_tnames['tnames'][$name];
            }

            return $this->_tnames['prefix'] . $name;
        }

        $this->_tnames['tnames'][$name] = $tname;
    }

    /**
     * quote
     * 
     * @param string $expr 
     * @return string
     */
    public function quote($expr)
    {
        return $this->dbase()->quote($expr);
    }

    /**
     * fetch
     * 
     * @param array $query
     * @return array
     */
    public function fetch($query)
    {
        if (empty($query) || !is_array($query) || count($query = array_merge(array(
            'where' => array(), 'order' => null, 'limit' => null, 'group' => null, 'having' => null, 'field' => null, 'right' => array(),
        ), $query)) !== 7)
        {
            return false;
        }

        extract($query);

        $where === null AND $where = array();
        $right === null AND $right = array();
        $table = $this->_tbl;

        if (empty($table) || !is_string($table) || (!is_array($where) && !($where instanceof Zend_Db_Expr)))
        {
            return false;
        }

        try
        {
            if ($where instanceof Zend_Db_Expr)
            {
                return $this->dbase()->fetchAll((string)$where);
            }

            $sql = $this->dbase()->select();

            if (empty($field))
            {
                $sql->from($this->tname($table));
            }
            else
            {
                $sql->from($this->tname($table), $field);
            }

            if (!empty($right))
            {
                foreach ($right as $table)
                {
                    if (!is_array($table))
                    {
                        return false;
                    }

                    if (isset($table[0]))
                    {
                        $table[0] = $this->tname($table[0]);
                        call_user_func_array(array($sql, 'joinLeft'), $table);
                    }
                }
            }

            foreach ($where as $val)
            {
                $sql->where($this->expr($val));
            }

            if (!empty($order))
            {
                $sql->order($order);
            }

            if (!empty($group))
            {
                $sql->group($group);
            }

            if (!empty($having))
            {
                $sql->having($having);
            }

            if (!empty($limit))
            {
                if (is_numeric($limit))
                {
                    $sql->limit($limit);
                }
                else if (is_array($limit))
                {
                    isset($limit[1]) ? $sql->limit($limit[0], $limit[1]) : $sql->limit($limit[0]);
                }
            }

            return $this->dbase()->fetchAll($sql);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * fetchIds
     * 
     * @param mixed $where
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function fetchIds($where = null, $order = null, $limit = null)
    {
        $ids = $this->fetch(array('where' => $where, 'order' => $order, 'limit' => $limit, 'field' => "{$this->_pre}id"));
        if (!empty($ids))
        {
            foreach ($ids as $key => $val)
            {
                $ids[$key] = $val["{$this->_pre}id"];
            }
        }

        return $ids;
    }

    /**
     * fetchAry
     * 
     * @param mixed $where
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function fetchAry($where = null, $order = null, $limit = null)
    {
        return $this->fetch(array('where' => $where, 'order' => $order, 'limit' => $limit));
    }

    /**
     * guid
     *
     * @param bool $expr
     * @return Zend_Db_Expr|string unique ID of each row, must be an integer string or Zend_Db_Expr
     */
    public function guid($expr = true)
    {
        $guid = 'CONCAT(1, RIGHT(UUID_SHORT(), 11))';
        return $expr ? $this->expr($guid) : $this->dbase()->fetchOne('SELECT ' . $guid);
    }

    /**
     * hash
     * 
     * @param string $expr 
     * @return string
     */
    public function hash($expr)
    {
        return md5($expr.strtolower("@{$this->_dbn}.{$this->_tbl}"));
    }

    /**
     * expr
     * 
     * @param string $expr 
     * @return Zend_Db_Expr
     */
    public function expr($expr)
    {
        return new Zend_Db_Expr($expr);
    }

    /**
     * log
     * 
     * @param mixed $info 
     * @return void
     */
    public function log($info)
    {
        $log = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getLog();

        if ($info instanceof Exception)
        {
            $log->err($info->getMessage(), $info);
        }
        else
        {
            $log->log((string)$info);
        }
    }
}
// End of file : Zyon_Model.php
