<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Bill_Journal extends Zyon_Model_Ftt
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

        if (isset($record['bj_hid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['bj_hid'])
                || empty($record['bj_hid'])
                || strlen($record['bj_hid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['bj_bid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['bj_bid'])
                || empty($record['bj_bid'])
                || strlen($record['bj_bid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['bj_uid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['bj_uid'])
                || empty($record['bj_uid'])
                || strlen($record['bj_uid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['bj_sum']))
        {
            if (!Zyon_Util::isInt($record['bj_sum'])
                || strlen($record['bj_sum']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['bj_time']))
        {
            if (!Zyon_Util::isUnsignedInt($record['bj_time'])
                || strlen($record['bj_time']) > 11
            )
            {
                return false;
            }
        }

        if (isset($record['bj_user']))
        {
            if (!is_string($record['bj_user'])
                || mb_strlen($record['bj_user']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['bj_memo']))
        {
            if (!is_string($record['bj_memo'])
                || mb_strlen($record['bj_memo']) > 200
            )
            {
                return false;
            }
        }

        if (isset($record['bj_pid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['bj_pid'])
                || strlen($record['bj_pid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['bj_pynm']))
        {
            if (!is_string($record['bj_pynm'])
                || mb_strlen($record['bj_pynm']) > 30
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewJournal
     * 
     * @param int $bid
     * @param int $hid
     * @param int $uid
     * @param int $pid
     * @param int $sum
     * @param int $time
     * @param string $user
     * @param string $pynm
     * @param string $memo
     * @return array
     */
    public function getNewJournal($bid, $hid, $uid, $pid, $sum, $time, $user, $pynm, $memo = null)
    {
        $ret = array(
            'bj_hid'   => $hid,
            'bj_bid'   => $bid,
            'bj_uid'   => $uid,
            'bj_pid'   => $pid,
            'bj_sum'   => $sum,
            'bj_time'  => $time,
            'bj_user'  => $user,
            'bj_pynm'  => $pynm,
        );

        $memo === null OR $ret['bj_memo'] = $memo;

        return $ret;
    }

    /**
     * addJournal
     * 
     * @param array $map
     * @return string
     */
    public function addJournal($map)
    {
        if (!$this->verify($map)
            || empty($map['bj_hid'])
            || empty($map['bj_bid'])
            || !isset($map['bj_uid'])
            || !isset($map['bj_sum'])
            || empty($map['bj_pid'])
        )
        {
            return false;
        }

        if (!isset($map['bj_time']))
        {
            $map['bj_time'] = time();
        }

        try
        {
            $this->dbase()->insert($this->tname('bill_journal'), $map);
            if ($ret = $this->dbase()->lastInsertId())
            {
                if ($this->cache()->load($key = $this->hash('ids.bid' . $map['bj_bid'])))
                {
                    $this->cache()->remove($key);
                }
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
     * getJournalAryByIds
     * 
     * @param array $ids 
     * @return array
     */
    public function getJournalAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('bill_journal'))
                ->where('bj_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['bj_id']));
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
     * getJournalIdsByBid
     * 
     * @param int $bid 
     * @return array
     */
    public function getJournalIdsByBid($bid)
    {
        if (empty($bid) || !is_numeric($bid))
        {
            return false;
        }

        if ($ret = $this->cache()->load($key = $this->hash('ids.bid' . $bid)))
        {
            return $ret;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('bill_journal'), $this->expr('GROUP_CONCAT(bj_id)'))
                ->where('bj_bid = ?');
            $ret = $this->dbase()->fetchOne($sql, $bid);
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
     * getJournalAryByBid
     * 
     * @param int $bid
     * @return array
     */
    public function getJournalAryByBid($bid)
    {
        return $this->getJournalAryByIds($this->getJournalIdsByBid($bid));
    }

    /**
     * getJournalAryByHidAndDay
     * 
     * @param int    $hid
     * @param string $day
     * @return array
     */
    public function getJournalAryByHidAndDay($hid, $day)
    {
        if (!is_numeric($hid) || !Zyon_Util::isDate($day))
        {
            return false;
        }

        $dtm = strtotime($day);
        return $this->getJournalAryByIds($this->fetchIds(array(
            'bj_hid = ' . $this->quote($hid),
            'bj_time >= ' . $dtm,
            'bj_time < '  . ($dtm+86400),
        )));
    }
}
// End of file : Journal.php
