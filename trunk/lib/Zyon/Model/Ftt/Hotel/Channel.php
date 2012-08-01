<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Hotel_Channel extends Zyon_Model_Ftt
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

        if (isset($record['hc_hid']))
        {
            if (empty($record['hc_hid']) || !Zyon_Util::isUnsignedInt($record['hc_hid'])
                || strlen($record['hc_hid']) > 10)
            {
                return false;
            }
        }

        if (isset($record['hc_status']))
        {
            if (!Zyon_Util::isUnsignedInt($record['hc_status'])
                || strlen($record['hc_status']) > 3)
            {
                return false;
            }
        }

        if (isset($record['hc_name']))
        {
            if (!is_string($record['hc_name'])
                || trim($record['hc_name']) == ''
                || mb_strlen($record['hc_name']) > 30)
            {
                return false;
            }
        }

        if (isset($record['hc_memo']))
        {
            if (!is_string($record['hc_memo'])
                || mb_strlen($record['hc_memo']) > 200)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewChannel
     * 
     * @param int    $hid
     * @param int    $stat
     * @param string $name
     * @return array
     */
    public function getNewChannel($hid, $stat, $name, $memo = null)
    {
        $ret =  array(
            'hc_hid'    => $hid,
            'hc_name'   => $name,
            'hc_status' => $stat,
        );

        $memo === null OR $ret['hc_memo'] = $memo;

        return $ret;
    }

    /**
     * addChannel
     * 
     * @param array $map
     * @return string
     */
    public function addChannel($map)
    {
        if (!$this->verify($map))
        {
            return false;
        }

        if (empty($map['hc_hid']) || !isset($map['hc_name']))
        {
            return false;
        }

        if (!$this->chkCanCreateChannel($map['hc_hid'], $map['hc_name']))
        {
            return false;
        }

        if (!isset($map['hc_ctime']))
        {
            $map['hc_ctime'] = time();
        }

        if (!isset($map['hc_mtime']))
        {
            $map['hc_mtime'] = $map['hc_ctime'];
        }

        try
        {
            $this->dbase()->insert($this->tname('hotel_channel'), $map);
            $ret = $this->dbase()->lastInsertId();
            if ($ret && ($this->cache()->load($key = $this->hash('ids.hid' . $map['hc_hid']))))
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
     * getChannel
     * 
     * @param int $id 
     * @return array
     */
    public function getChannel($id)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_channel'))->where('hc_id = ?')->limit(1);
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
     * modChannel
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modChannel($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map) || isset($map['hc_name']))
        {
            return false;
        }

        if (!isset($map['hc_mtime']))
        {
            $map['hc_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('hotel_channel'), $map, 'hc_id = ' . $this->quote($id));
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
     * getChannelAryByIds 
     * 
     * @param array $ids 
     * @return array
     */
    public function getChannelAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_channel'))
                ->where('hc_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['hc_id']));
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
     * getChannelIds
     * 
     * @param array $map
     * @return array
     */
    public function getChannelIds($map)
    {
        if (empty($map) || !is_array($map))
        {
            return false;
        }

        if (count(array_intersect_key($map, array('hc_hid' => 1, 'hc_name' => 1)))
            !== count($map))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_channel'), $this->expr('GROUP_CONCAT(hc_id)'));
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
     * getChannelIdsByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getChannelIdsByHid($hid)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_channel'), $this->expr('GROUP_CONCAT(hc_id)'))
                ->where('hc_hid = ?');

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
     * getChannelAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getChannelAryByHid($hid)
    {
        return $this->getChannelAryByIds($this->getChannelIdsByHid($hid));
    }

    /**
     * getUsableChannelAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getUsableChannelAryByHid($hid)
    {
        if (!is_numeric($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_channel'))
                ->where('hc_status > 0')
                ->where('hc_hid = :hid');

            return $this->dbase()->fetchAll($sql, array('hid' => $hid));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getChannelIdsByHidAndName
     * 
     * @param int    $hid
     * @param string $name
     * @return array
     */
    public function getChannelIdsByHidAndName($hid, $name)
    {
        return $this->getChannelIds(array('hc_hid' => $hid, 'hc_name' => $name));
    }

    /**
     * chkCanCreateChannel
     * 
     * @param mixed $hid
     * @param mixed $name
     * @return bool
     */
    public function chkCanCreateChannel($hid, $name = null)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_channel'), array('hc_name'))->where('hc_hid = ?');
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
// End of file : Channel.php
