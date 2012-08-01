<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Room_Price extends Zyon_Model_Ftt
{
    /**
     * verify
     * 
     * @param array $record
     * @return bool
     */
    public function verify($record)
    {
        if (empty($record) || !is_array($record))
        {
            return false;
        }

        if (isset($record['rp_hid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['rp_hid'])
                || empty($record['rp_hid'])
                || strlen($record['rp_hid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['rp_rid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['rp_rid'])
                || empty($record['rp_rid'])
                || strlen($record['rp_rid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['rp_uid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['rp_uid'])
                || empty($record['rp_uid'])
                || strlen($record['rp_uid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['rp_value']))
        {
            if (!Zyon_Util::isUnsignedInt($record['rp_value'])
                || strlen($record['rp_value']) > 7
            )
            {
                return false;
            }
        }

        if (isset($record['rp_btime']))
        {
            if (!Zyon_Util::isUnsignedInt($record['rp_btime'])
                || strlen($record['rp_btime']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['rp_etime']))
        {
            if (!Zyon_Util::isUnsignedInt($record['rp_etime'])
                || strlen($record['rp_etime']) > 10
                || (isset($record['rp_btime']) && $record['rp_btime'] >  $record['rp_etime'])
            )
            {
                return false;
            }
        }

        if (isset($record['rp_uname']))
        {
            if (!is_string($record['rp_uname'])
                || mb_strlen($record['rp_uname']) > 20
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewPrice
     * 
     * @param int $hid
     * @param int $rid
     * @param int $uid
     * @param string $uname
     * @param int $value
     * @param int $btime
     * @param int $etime
     * @return array
     */
    public function getNewPrice($hid, $rid, $uid, $uname, $value, $btime, $etime)
    {
        return array(
            'rp_hid'   => $hid,
            'rp_rid'   => $rid,
            'rp_uid'   => $uid,
            'rp_uname' => $uname,
            'rp_value' => $value,
            'rp_btime' => $btime,
            'rp_etime' => $etime,
        );
    }

    /**
     * addPrice
     * 
     * @param array $map
     * @return string
     */
    public function addPrice($map)
    {
        if (!$this->verify($map)
            || !isset($map['rp_hid'])
            || !isset($map['rp_rid'])
            || !isset($map['rp_uid'])
            || !isset($map['rp_uname'])
            || !isset($map['rp_value'])
            || !isset($map['rp_btime'])
            || !isset($map['rp_etime'])
        )
        {
            return false;
        }

        if (!isset($map['rp_ctime']))
        {
            $map['rp_ctime'] = time();
        }

        if (!isset($map['rp_mtime']))
        {
            $map['rp_mtime'] = $map['rp_ctime'];
        }

        try
        {
            $this->dbase()->insert($this->tname('room_price'), $map);
            return $this->dbase()->lastInsertId();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * addPriceAry
     * 
     * @param array $ary
     * @return int
     */
    public function addPriceAry($ary)
    {
        if (empty($ary) || !is_array($ary))
        {
            return false;
        }

        $now = time();
        $vas = array();
        foreach ($ary as &$map)
        {
            if (!$this->verify($map)
                || !isset($map['rp_hid'])
                || !isset($map['rp_rid'])
                || !isset($map['rp_uid'])
                || !isset($map['rp_uname'])
                || !isset($map['rp_value'])
                || !isset($map['rp_btime'])
                || !isset($map['rp_etime'])
            )
            {
                return false;
            }

            if (!isset($map['rp_ctime']))
            {
                $map['rp_ctime'] = $now;
            }

            if (!isset($map['rp_mtime']))
            {
                $map['rp_mtime'] = $map['rp_ctime'];
            }

            $vas[] = '('
                . $this->quote($map['rp_hid']) . ','
                . $this->quote($map['rp_rid']) . ','
                . $this->quote($map['rp_uid']) . ','
                . $this->quote($map['rp_uname']) . ','
                . $this->quote($map['rp_value']) . ','
                . $this->quote($map['rp_btime']) . ','
                . $this->quote($map['rp_etime']) . ','
                . $this->quote($map['rp_ctime']) . ','
                . $this->quote($map['rp_mtime']) . ')';
        }
        unset($map);
        $vas = implode(',', $vas);

        try
        {
            return $this->dbase()->query(sprintf(
                'INSERT INTO %s (rp_hid, rp_rid, rp_uid, rp_uname, rp_value, rp_btime, rp_etime, rp_ctime, rp_mtime) VALUES %s',
                $this->tname('room_price'),
                $vas
            ))->rowCount();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getPrice
     * 
     * @param int $id 
     * @return array
     */
    public function getPrice($id)
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
            $sql = $this->dbase()->select()->from($this->tname('room_price'))->where('rp_id = ?')->limit(1);
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
     * modPrice
     * 
     * @param int $id
     * @param array $map
     * @return int
     */
    public function modPrice($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map))
        {
            return false;
        }

        if (!isset($map['rp_mtime']))
        {
            $map['rp_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('room_price'), $map, 'rp_id = ' . $this->quote($id));
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
     * getPriceAryByIds
     * 
     * @param array $ids 
     * @return array
     */
    public function getPriceAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('room_price'))
                ->where('rp_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['rp_id']));
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
     * getBasicPriceByRids
     *
     * @param int $rid
     * @return int
     */
    public function getBasicPriceByRids($ids)
    {
        if (!is_array($ids))
        {
            return false;
        }

        if (empty($ids))
        {
            return array();
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('room'), array('r_id', 'r_price'))
                ->where('r_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            return $this->dbase()->fetchPairs($sql);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getBasicPriceByRid
     *
     * @param int $rid
     * @return int
     */
    public function getBasicPriceByRid($rid)
    {
        if (!is_numeric($rid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('room'), 'r_price')
                ->where('r_id = ?');
            return $this->dbase()->fetchOne($sql, $rid);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getBasicPriceByHid
     *
     * @param int $hid
     * @return array
     */
    public function getBasicPriceByHid($hid)
    {
        if (!is_numeric($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('room'), array('r_id', 'r_price'))
                ->where('r_hid = ?');
            return $this->dbase()->fetchPairs($sql, $hid);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getPriceAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getPriceAryByHid($hid)
    {
        return $this->getPriceAryByIds($this->fetchIds(array('rp_hid = ' . (int)$hid)));
    }

    /**
     * getPriceAryByRid
     * 
     * @param int $rid
     * @return array
     */
    public function getPriceAryByRid($rid)
    {
        return $this->getPriceAryByIds($this->fetchIds(array('rp_rid = ' . (int)$rid)));
    }

    /**
     * getPriceAryByRids
     * 
     * @param array $ids
     * @return array
     */
    public function getPriceAryByRids($ids)
    {
        return $this->getPriceAryByIds($this->fetchIds(array('rp_rid IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')')));
    }

    /**
     * getPriceAryByRidAndTimeLine
     * 
     * @param int $rid
     * @param int $btime
     * @param int $etime
     * @return array
     */
    public function getPriceAryByRidAndTimeLine($rid, $btime, $etime)
    {
        if (!$this->verify(array('rp_rid' => $rid, 'rp_btime' => $btime, 'rp_etime' => $etime))
            || $etime < $btime
            || $etime - $btime > 8640000 // Too large interval time
        )
        {
            return false;
        }

        return $this->getPriceAryByIds(
            $this->fetchIds(array(
                'rp_rid = ' . (int)$rid,
                sprintf('NOT (rp_etime < %d OR rp_btime > %d)', $btime, $etime),
            ))
        );
    }

    /**
     * getPriceAryByHidAndTimeLine
     * 
     * @param int $hid
     * @param int $btime
     * @param int $etime
     * @return array
     */
    public function getPriceAryByHidAndTimeLine($hid, $btime, $etime)
    {
        if (!$this->verify(array('rp_hid' => $hid, 'rp_btime' => $btime, 'rp_etime' => $etime))
            || $etime < $btime
            || $etime - $btime > 8640000 // Too large interval time
        )
        {
            return false;
        }

        return $this->getPriceAryByIds(
            $this->fetchIds(array(
                'rp_hid = ' . (int)$hid,
                sprintf('NOT (rp_etime < %d OR rp_btime > %d)', $btime, $etime),
            ))
        );
    }

    /**
     * getPriceDotAry
     *
     * @param int   $bas
     * @param int   $btm
     * @param int   $etm
     * @param array $rps
     * @return array
     */
    public function getPriceDotAry($bas, $btm, $etm, $rps)
    {
        if (!is_numeric($bas) || !is_numeric($btm) || !is_numeric($etm) || !is_array($rps))
        {
            return false;
        }

        $ret = array();
        $dot = strtotime(date('Y-m-d', $btm));
        while ($dot <= $etm)
        {
            $ret[$dot] = $bas;
            $dot += 86400;
        }

        if (!empty($rps))
        {
            ksort($rps, SORT_NUMERIC);
            foreach ($rps as &$val)
            {
                if ($dot = strtotime(date('Y-m-d', max($val['rp_btime'], $btm))))
                {
                    $max = min($val['rp_etime'], $etm);
                    while ($dot <= $max)
                    {
                        $ret[$dot] = $val['rp_value'];
                        $dot += 86400;
                    }
                }
            }
            unset($val);
        }

        return $ret;
    }

    /**
     * getPriceDotAryByRid
     * 
     * @param int $rid
     * @param int $btime
     * @param int $etime
     * @return array
     */
    public function getPriceDotAryByRid($rid, $btime, $etime)
    {
        if (!is_numeric($basic = $this->getBasicPriceByRid($rid)))
        {
            return false;
        }

        return $this->getPriceDotAry($basic, $btime, $etime,
            Zyon_Array::keyto($this->getPriceAryByRidAndTimeLine($rid, $btime, $etime), 'rp_id')
        );
    }

    /**
     * getPriceDotAryByHid
     * 
     * @param int $hid
     * @param int $btime
     * @param int $etime
     * @return array
     */
    public function getPriceDotAryByHid($hid, $btime, $etime)
    {
        if (!is_array($bas = $this->getBasicPriceByHid($hid)))
        {
            return false;
        }

        $rps = Zyon_Array::group($this->getPriceAryByHidAndTimeLine($hid, $btime, $etime), 'rp_rid');

        $ret = array();
        if (!empty($bas))
        {
            foreach (array_keys($bas) as $key)
            {
                $ret[$key] = $this->getPriceDotAry($bas[$key], $btime, $etime, isset($rps[$key]) ? $rps[$key] : array());
            }
        }

        return $ret;
    }
}
// End of file : Price.php
