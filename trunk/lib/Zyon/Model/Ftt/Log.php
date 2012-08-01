<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Log extends Zyon_Model_Ftt
{
    /**
     * _acts
     * 
     * @var array
     */
    protected $_acts = array();

    /**
     * getAct
     * 
     * @param string $name 
     * @return mixed
     */
    public function getAct($name)
    {
        return isset($this->_acts[$name]) ? $this->_acts[$name] : null;
    }

    /**
     * encode
     * 
     * @param array $map
     * @return array
     */
    public function encode($map)
    {
        if (isset($map["{$this->_pre}memo"]))
        {
            $map["{$this->_pre}memo"] == '' AND $map["{$this->_pre}memo"] = '';
        }

        if (isset($map["{$this->_pre}data"]))
        {
            $map["{$this->_pre}data"] = $map["{$this->_pre}data"] == ''
                ? ''
                : json_encode($map["{$this->_pre}data"]);
        }

        return $map;
    }

    /**
     * decode
     * 
     * @param array $ret
     * @return array
     */
    public function decode($ret)
    {
        if (!empty($ret) && is_array($ret))
        {
            $ret["{$this->_pre}data"] = $ret["{$this->_pre}data"] == ''
                ? array()
                : json_decode($ret["{$this->_pre}data"], true);
        }

        return $ret;
    }

    /**
     * getDatetime
     * 
     * @return string
     */
    public function getDatetime($timestamp = null)
    {
        return $timestamp === null ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * getClientIp
     * 
     * @return string
     */
    public function getClientIp()
    {
        $ip = null;
        if (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else if (isset($_SERVER['REMOTE_ADDR']))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (!Zyon_Util::isIP($ip))
        {
            $ip = '';
        }

        return $ip;
    }

    /**
     * getNewLog
     * 
     * @param string $act
     * @param int    $mid
     * @param int    $xid
     * @param string $xnm
     * @param int    $pid
     * @param string $pnm
     * @param string $memo
     * @param mixed  $data
     * @return array
     */
    public function getNewLog($act, $mid, $xid, $xnm, $pid, $pnm, $memo = null, $data = null)
    {
        $log = array(
            "{$this->_pre}ip"   => $this->getClientIp(),
            "{$this->_pre}act"  => $this->getAct($act),
            "{$this->_pre}mid"  => $mid,
            "{$this->_pre}xid"  => $xid,
            "{$this->_pre}xnm"  => $xnm,
            "{$this->_pre}pid"  => $pid,
            "{$this->_pre}pnm"  => $pnm,
            "{$this->_pre}memo" => $memo,
            "{$this->_pre}data" => $data,
        );

        $log["{$this->_pre}name"] = __($log["{$this->_pre}act"]);

        return $log;
    }

    /**
     * addLog
     * 
     * @param array $map
     * @return string
     */
    public function addLog($map)
    {
        if (empty($map) || !is_array($map)
            || !isset($map["{$this->_pre}act"]) || !is_string($map["{$this->_pre}act"])
            || !in_array($map["{$this->_pre}act"], $this->_acts, true)
        )
        {
            return false;
        }

        if (!isset($map["{$this->_pre}ip"]))
        {
            $map["{$this->_pre}ip"] = $this->getClientIp();
        }

        if (!isset($map["{$this->_pre}date"]))
        {
            $map["{$this->_pre}date"] = $this->getDatetime();
        }

        $map = $this->encode($map);

        try
        {
            $this->dbase()->insert($this->tname($this->_tbl), $map);
            return $this->dbase()->lastInsertId();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getLog
     * 
     * @param int $lid 
     * @return array
     */
    public function getLog($id)
    {
        if (empty($id) || !is_numeric($id))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname($this->_tbl))->where("{$this->_pre}id = ?")->limit(1);
            return $this->decode($this->dbase()->fetchRow($sql, $id));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }
}
// End of file : Log.php
