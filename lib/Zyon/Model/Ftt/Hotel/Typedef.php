<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Hotel_Typedef extends Zyon_Model_Ftt
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

        if (isset($record['ht_hid']))
        {
            if (empty($record['ht_hid']) || !Zyon_Util::isUnsignedInt($record['ht_hid'])
                || strlen($record['ht_hid']) > 10)
            {
                return false;
            }
        }

        if (isset($record['ht_status']))
        {
            if (!Zyon_Util::isUnsignedInt($record['ht_status'])
                || strlen($record['ht_status']) > 3)
            {
                return false;
            }
        }

        if (isset($record['ht_name']))
        {
            if (!is_string($record['ht_name'])
                || trim($record['ht_name']) == ''
                || mb_strlen($record['ht_name']) > 30)
            {
                return false;
            }
        }

        if (isset($record['ht_memo']))
        {
            if (!is_string($record['ht_memo'])
                || mb_strlen($record['ht_memo']) > 200)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewTypedef
     * 
     * @param int    $hid
     * @param int    $stat
     * @param string $name
     * @return array
     */
    public function getNewTypedef($hid, $stat, $name, $memo = null)
    {
        $ret =  array(
            'ht_hid'    => $hid,
            'ht_name'   => $name,
            'ht_status' => $stat,
        );

        $memo === null OR $ret['ht_memo'] = $memo;

        return $ret;
    }

    /**
     * addTypedef
     * 
     * @param array $map
     * @return string
     */
    public function addTypedef($map)
    {
        if (!$this->verify($map))
        {
            return false;
        }

        if (empty($map['ht_hid']) || !isset($map['ht_name']))
        {
            return false;
        }

        if (!$this->chkCanCreateTypedef($map['ht_hid'], $map['ht_name']))
        {
            return false;
        }

        if (!isset($map['ht_ctime']))
        {
            $map['ht_ctime'] = time();
        }

        if (!isset($map['ht_mtime']))
        {
            $map['ht_mtime'] = $map['ht_ctime'];
        }

        try
        {
            $this->dbase()->insert($this->tname('hotel_typedef'), $map);
            $ret = $this->dbase()->lastInsertId();
            if ($ret && ($this->cache()->load($key = $this->hash('ids.hid' . $map['ht_hid']))))
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
     * getTypedef
     * 
     * @param int $id 
     * @return array
     */
    public function getTypedef($id)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_typedef'))->where('ht_id = ?')->limit(1);
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
     * modTypedef
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modTypedef($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map) || isset($map['ht_name']))
        {
            return false;
        }

        if (!isset($map['ht_mtime']))
        {
            $map['ht_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('hotel_typedef'), $map, 'ht_id = ' . $this->quote($id));
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
     * getTypedefAryByIds 
     * 
     * @param array $ids 
     * @return array
     */
    public function getTypedefAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_typedef'))
                ->where('ht_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['ht_id']));
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
     * getTypedefIds
     * 
     * @param array $map
     * @return array
     */
    public function getTypedefIds($map)
    {
        if (empty($map) || !is_array($map))
        {
            return false;
        }

        if (count(array_intersect_key($map, array('ht_hid' => 1, 'ht_name' => 1)))
            !== count($map))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_typedef'), $this->expr('GROUP_CONCAT(ht_id)'));
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
     * getTypedefIdsByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getTypedefIdsByHid($hid)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_typedef'), $this->expr('GROUP_CONCAT(ht_id)'))
                ->where('ht_hid = ?');

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
     * getTypedefAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getTypedefAryByHid($hid)
    {
        return $this->getTypedefAryByIds($this->getTypedefIdsByHid($hid));
    }

    /**
     * getUsableTypedefAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getUsableTypedefAryByHid($hid)
    {
        if (!is_numeric($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_typedef'))
                ->where('ht_status > 0')
                ->where('ht_hid = :hid');

            return $this->dbase()->fetchAll($sql, array('hid' => $hid));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getTypedefIdsByHidAndName
     * 
     * @param int    $hid
     * @param string $name
     * @return array
     */
    public function getTypedefIdsByHidAndName($hid, $name)
    {
        return $this->getTypedefIds(array('ht_hid' => $hid, 'ht_name' => $name));
    }

    /**
     * chkCanCreateTypedef
     * 
     * @param mixed $hid
     * @param mixed $name
     * @return bool
     */
    public function chkCanCreateTypedef($hid, $name = null)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_typedef'), array('ht_name'))->where('ht_hid = ?');
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
// End of file : Typedef.php
