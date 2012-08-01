<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Hotel_Payment extends Zyon_Model_Ftt
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

        if (isset($record['hp_hid']))
        {
            if (empty($record['hp_hid']) || !Zyon_Util::isUnsignedInt($record['hp_hid'])
                || strlen($record['hp_hid']) > 10)
            {
                return false;
            }
        }

        if (isset($record['hp_name']))
        {
            if (!is_string($record['hp_name'])
                || trim($record['hp_name']) == ''
                || mb_strlen($record['hp_name']) > 30)
            {
                return false;
            }
        }

        if (isset($record['hp_memo']))
        {
            if (!is_string($record['hp_memo'])
                || mb_strlen($record['hp_memo']) > 200)
            {
                return false;
            }
        }

        if (isset($record['hp_status']))
        {
            if (!Zyon_Util::isUnsignedInt($record['hp_status'])
                || strlen($record['hp_status']) > 3)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewPayment
     * 
     * @param int    $hid
     * @param int    $stat
     * @param string $name
     * @param string $memo
     * @return array
     */
    public function getNewPayment($hid, $stat, $name, $memo = null)
    {
        $ret = array(
            'hp_hid'    => $hid,
            'hp_name'   => $name,
            'hp_status' => $stat,
        );

        $memo === null OR $ret['hp_memo'] = $memo;

        return $ret;
    }

    /**
     * addPayment
     * 
     * @param array $map
     * @return string
     */
    public function addPayment($map)
    {
        if (!$this->verify($map))
        {
            return false;
        }

        if (empty($map['hp_hid']) || !isset($map['hp_name']))
        {
            return false;
        }

        if (!$this->chkCanCreatePayment($map['hp_hid'], $map['hp_name']))
        {
            return false;
        }

        if (!isset($map['hp_ctime']))
        {
            $map['hp_ctime'] = time();
        }

        if (!isset($map['hp_mtime']))
        {
            $map['hp_mtime'] = $map['hp_ctime'];
        }

        try
        {
            $this->dbase()->insert($this->tname('hotel_payment'), $map);
            $ret = $this->dbase()->lastInsertId();
            if ($ret && ($this->cache()->load($key = $this->hash('ids.hid' . $map['hp_hid']))))
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
     * getPayment
     * 
     * @param int $id 
     * @return array
     */
    public function getPayment($id)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_payment'))->where('hp_id = ?')->limit(1);
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
     * modPayment
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modPayment($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map) || isset($map['hp_name']))
        {
            return false;
        }

        if (!isset($map['hp_mtime']))
        {
            $map['hp_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('hotel_payment'), $map, 'hp_id = ' . $this->quote($id));
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
     * getPaymentAryByIds 
     * 
     * @param array $ids 
     * @return array
     */
    public function getPaymentAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_payment'))
                ->where('hp_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['hp_id']));
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
     * getPaymentIds
     * 
     * @param array $map
     * @return array
     */
    public function getPaymentIds($map)
    {
        if (empty($map) || !is_array($map))
        {
            return false;
        }

        if (count(array_intersect_key($map, array('hp_hid' => 1, 'hp_name' => 1)))
            !== count($map))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_payment'), $this->expr('GROUP_CONCAT(hp_id)'));
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
     * getPaymentIdsByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getPaymentIdsByHid($hid)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_payment'), $this->expr('GROUP_CONCAT(hp_id)'))
                ->where('hp_hid = ?');

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
     * getPaymentAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getPaymentAryByHid($hid)
    {
        return $this->getPaymentAryByIds($this->getPaymentIdsByHid($hid));
    }

    /**
     * getUsablePaymentAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getUsablePaymentAryByHid($hid)
    {
        if (!is_numeric($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('hotel_payment'))
                ->where('hp_status > 0')
                ->where('hp_hid = :hid');

            return $this->dbase()->fetchAll($sql, array('hid' => $hid));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getPaymentIdsByHidAndName
     * 
     * @param int    $hid
     * @param string $name
     * @return array
     */
    public function getPaymentIdsByHidAndName($hid, $name)
    {
        return $this->getPaymentIds(array('hp_hid' => $hid, 'hp_name' => $name));
    }

    /**
     * chkCanCreatePayment
     * 
     * @param mixed $hid
     * @param mixed $name
     * @return bool
     */
    public function chkCanCreatePayment($hid, $name = null)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel_payment'), array('hp_name'))->where('hp_hid = ?');
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
// End of file : Payment.php
