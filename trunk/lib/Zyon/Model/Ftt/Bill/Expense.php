<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Bill_Expense extends Zyon_Model_Ftt
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

        if (isset($record['be_hid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['be_hid'])
                || empty($record['be_hid'])
                || strlen($record['be_hid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['be_bid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['be_bid'])
                || empty($record['be_bid'])
                || strlen($record['be_bid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['be_uid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['be_uid'])
                || empty($record['be_uid'])
                || strlen($record['be_uid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['be_sum']))
        {
            if (!Zyon_Util::isInt($record['be_sum'])
                || strlen($record['be_sum']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['be_time']))
        {
            if (!Zyon_Util::isUnsignedInt($record['be_time'])
                || strlen($record['be_time']) > 11
            )
            {
                return false;
            }
        }

        if (isset($record['be_user']))
        {
            if (!is_string($record['be_user'])
                || mb_strlen($record['be_user']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['be_memo']))
        {
            if (!is_string($record['be_memo'])
                || mb_strlen($record['be_memo']) > 200
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewExpense
     * 
     * @param int $bid
     * @param int $hid
     * @param int $uid
     * @param int $sum
     * @param string $user
     * @param string $memo
     * @return array
     */
    public function getNewExpense($bid, $hid, $uid, $sum, $user, $memo = null)
    {
        $ret = array(
            'be_bid'   => $bid,
            'be_hid'   => $hid,
            'be_uid'   => $uid,
            'be_sum'   => $sum,
            'be_user'  => $user,
        );

        $memo === null OR $ret['be_memo'] = $memo;

        return $ret;
    }

    /**
     * addExpense
     * 
     * @param array $map
     * @return string
     */
    public function addExpense($map)
    {
        if (!$this->verify($map)
            || empty($map['be_hid'])
            || empty($map['be_bid'])
            || !isset($map['be_uid'])
            || !isset($map['be_sum'])
        )
        {
            return false;
        }

        if (!isset($map['be_time']))
        {
            $map['be_time'] = time();
        }

        try
        {
            $this->dbase()->insert($this->tname('bill_expense'), $map);
            if ($ret = $this->dbase()->lastInsertId())
            {
                if ($this->cache()->load($key = $this->hash('ids.bid' . $map['be_bid'])))
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
     * getExpenseAryByIds
     * 
     * @param array $ids 
     * @return array
     */
    public function getExpenseAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('bill_expense'))
                ->where('be_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['be_id']));
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
     * getExpenseIdsByBid
     * 
     * @param int $bid 
     * @return array
     */
    public function getExpenseIdsByBid($bid)
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
            $sql = $this->dbase()->select()->from($this->tname('bill_expense'), $this->expr('GROUP_CONCAT(be_id)'))
                ->where('be_bid = ?');
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
     * getExpenseAryByBid
     * 
     * @param int $bid
     * @return array
     */
    public function getExpenseAryByBid($bid)
    {
        return $this->getExpenseAryByIds($this->getExpenseIdsByBid($bid));
    }
}
// End of file : Expense.php
