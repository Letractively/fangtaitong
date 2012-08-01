<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Hotel_Settlem extends Zyon_Model_Ftt
{
    /**
     * verify
     * 
     * @param mixed $record
     * @return bool
     */
    public function verify($record)
    {
        if (empty($record) || !is_array($record))
        {
            return false;
        }

        if (isset($record['hs_hid']))
        {
            if (empty($record['hs_hid']) || !Zyon_Util::isUnsignedInt($record['hs_hid'])
                || strlen($record['hs_hid']) > 10)
            {
                return false;
            }
        }

        if (isset($record['hs_status']))
        {
            if (!Zyon_Util::isUnsignedInt($record['hs_status'])
                || strlen($record['hs_status']) > 3)
            {
                return false;
            }
        }

        if (isset($record['hs_name']))
        {
            if (!is_string($record['hs_name'])
                || trim($record['hs_name']) == ''
                || mb_strlen($record['hs_name']) > 30)
            {
                return false;
            }
        }

        if (isset($record['hs_memo']))
        {
            if (!is_string($record['hs_memo'])
                || mb_strlen($record['hs_memo']) > 200)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewSettlem
     * 
     * @param int    $hid
     * @param int    $stat
     * @param string $name
     * @return array
     */
    public function getNewSettlem($hid, $stat, $name, $memo = null)
    {
        $ret =  array(
            'hs_hid'    => $hid,
            'hs_name'   => $name,
            'hs_status' => $stat,
        );

        $memo === null OR $ret['hs_memo'] = $memo;

        return $ret;
    }

    /**
     * addSettlem
     * 
     * @param array $map
     * @return string
     */
    public function addSettlem($map)
    {
        if (!$this->verify($map))
        {
            return false;
        }

        if (empty($map['hs_hid']) || !isset($map['hs_name']))
        {
            return false;
        }

        if (!$this->chkCanCreateSettlem($map['hs_hid'], $map['hs_name']))
        {
            return false;
        }

        if (!isset($map['hs_ctime']))
        {
            $map['hs_ctime'] = time();
        }

        if (!isset($map['hs_mtime']))
        {
            $map['hs_mtime'] = $map['hs_ctime'];
        }

        try
        {
            $this->dbase()->insert($this->tname('hotel_settlem'), $map);
            $ret = $this->dbase()->lastInsertId();
            if ($ret && ($this->cache()->load($key = $this->hash('ids.hid' . $map['hs_hid']))))
            {
                $this->cache()->remove($key);
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getSettlem
     * 
     * @param int $id 
     * @return array
     */
    public function getSettlem($id)
    {
        if (empty($id) || !is_numeric($id))
        {
            return false;
        }

        if ($ret = $this->cache()->load($key = $this->hash($id)))
        {
            return $ret;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_settlem'))->where('hs_id = ?')->limit(1);
            $ret = $this->dbase()->fetchRow($sql, $id);

            if ($ret)
            {
                $this->cache()->save($ret, $key);
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * modSettlem
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modSettlem($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map) || isset($map['hs_name']))
        {
            return false;
        }

        if (!isset($map['hs_mtime']))
        {
            $map['hs_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('hotel_settlem'), $map, 'hs_id = ' . $this->quote($id));
            if ($ret && $this->cache()->load($key = $this->hash($id)))
            {
                $this->cache()->remove($key);
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getSettlemAryByIds 
     * 
     * @param array $ids 
     * @return array
     */
    public function getSettlemAryByIds($ids)
    {
        if (!is_array($ids))
        {
            return false;
        }

        if (empty($ids))
        {
            return array();
        }

        $ids = array_values(array_unique($ids));
        foreach ($ids as $val)
        {
            if (!is_numeric($val))
            {
                return false;
            }
        }
        $ids = array_combine(array_map(array($this, 'hash'), $ids), $ids);

        if ($ret = $this->cache()->load(array_keys($ids)))
        {
            if (count($ret) === count($ids))
            {
                return array_values($ret);
            }
            else
            {
                $ids = array_diff_key($ids, $ret);
                $ret = array_values($ret);
            }
        }
        else
        {
            $ret = array();
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_settlem'))
                ->where('hs_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['hs_id']));
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getSettlemIds
     * 
     * @param array $map
     * @return array
     */
    public function getSettlemIds($map)
    {
        if (empty($map) || !is_array($map))
        {
            return false;
        }

        if (count(array_intersect_key($map, array('hs_hid' => 1, 'hs_name' => 1)))
            !== count($map))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_settlem'), $this->expr('GROUP_CONCAT(hs_id)'));
            foreach (array_keys($map) as $val)
            {
                $sql->where($val . '=:' . $val);
            }

            $ret = $this->dbase()->fetchOne($sql, $map);
            return empty($ret) ? array() : explode(',', $ret);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getSettlemIdsByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getSettlemIdsByHid($hid)
    {
        if (!is_numeric($hid))
        {
            return false;
        }

        if ($ret = $this->cache()->load($key = $this->hash('ids.hid' . $hid)))
        {
            return $ret;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_settlem'), $this->expr('GROUP_CONCAT(hs_id)'))
                ->where('hs_hid = ?');

            $ret = $this->dbase()->fetchOne($sql, $hid);
            $ret = empty($ret) ? array() : explode(',', $ret);

            $this->cache()->save($ret, $key);

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getSettlemAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getSettlemAryByHid($hid)
    {
        return $this->getSettlemAryByIds($this->getSettlemIdsByHid($hid));
    }

    /**
     * getUsableSettlemAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getUsableSettlemAryByHid($hid)
    {
        if (!is_numeric($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_settlem'))
                ->where('hs_status > 0')
                ->where('hs_hid = :hid');

            return $this->dbase()->fetchAll($sql, array('hid' => $hid));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getSettlemIdsByHidAndName
     * 
     * @param int    $hid
     * @param string $name
     * @return array
     */
    public function getSettlemIdsByHidAndName($hid, $name)
    {
        return $this->getSettlemIds(array('hs_hid' => $hid, 'hs_name' => $name));
    }

    /**
     * chkCanCreateSettlem
     * 
     * @param mixed $hid
     * @param mixed $name
     * @return bool
     */
    public function chkCanCreateSettlem($hid, $name = null)
    {
        if (!is_numeric($hid) || ($name !== null && !is_string($name)))
        {
            return false;
        }

        if ($name === null)
        {
            return true;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_settlem'), array('hs_name'))->where('hs_hid = ?');
            $ret = $this->dbase()->fetchCol($sql, $hid);
            return !in_array(trim($name), $ret, true);
        }
        catch (Exception $e)
        {
            $this->log($e);
        }

        return false;
    }
}
// End of file : Settlem.php
