<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Bill extends Zyon_Model_Ftt
{
    /**
     * _action
     * 
     * @var array
     */
    protected static $_action = array();

    /**
     * _battrs
     * 
     * @var array
     */
    protected static $_battrs = array();

    /**
     * _status
     * 
     * @var array
     */
    protected static $_status = array();

    /**
     * _rtstat
     * 
     * @var array
     */
    protected static $_rtstat = array();

    /**
     * _handle
     * 
     * @var array
     */
    protected static $_handle = array(
        BILL_STATUS_KF => array(BILL_ACTION_QTFY, BILL_ACTION_SKTK, BILL_ACTION_XGBZ, BILL_ACTION_JSFS, BILL_ACTION_GQSJ, BILL_ACTION_GBZD),
        BILL_STATUS_GB => array(BILL_ACTION_KFZD),
    );

    /**
     * _prepare
     * 
     * @return void
     */
    protected function _prepare()
    {
        if (empty(static::$_action))
        {
            static::$_action[BILL_ACTION_GBZD] = array(
                'name' => getBillActionNameByCode(BILL_ACTION_GBZD),
                'code' => BILL_ACTION_GBZD,
            );

            static::$_action[BILL_ACTION_KFZD] = array(
                'name' => getBillActionNameByCode(BILL_ACTION_KFZD),
                'code' => BILL_ACTION_KFZD,
            );

            static::$_action[BILL_ACTION_QTFY] = array(
                'name' => getBillActionNameByCode(BILL_ACTION_QTFY),
                'code' => BILL_ACTION_QTFY,
            );

            static::$_action[BILL_ACTION_SKTK] = array(
                'name' => getBillActionNameByCode(BILL_ACTION_SKTK),
                'code' => BILL_ACTION_SKTK,
            );

            static::$_action[BILL_ACTION_XGBZ] = array(
                'name' => getBillActionNameByCode(BILL_ACTION_XGBZ),
                'code' => BILL_ACTION_XGBZ,
            );

            static::$_action[BILL_ACTION_JSFS] = array(
                'name' => getBillActionNameByCode(BILL_ACTION_JSFS),
                'code' => BILL_ACTION_JSFS,
            );

            static::$_action[BILL_ACTION_GQSJ] = array(
                'name' => getBillActionNameByCode(BILL_ACTION_GQSJ),
                'code' => BILL_ACTION_GQSJ,
            );
        }

        if (empty(static::$_battrs))
        {
            // static::$_battrs[getBillAttrNameByCode(BILL_ATTR_GQTX)] = BILL_ATTR_GQTX;
        }

        if (empty(static::$_status))
        {
            static::$_status[getBillStatusNameByCode(BILL_STATUS_KF)] = BILL_STATUS_KF;
            static::$_status[getBillStatusNameByCode(BILL_STATUS_GB)] = BILL_STATUS_GB;
        }

        if (empty(static::$_rtstat))
        {
            static::$_rtstat[getBillRealTimeStatusNameByCode(BILL_REALTIME_STATUS_YGQ)] = BILL_REALTIME_STATUS_YGQ;
        }
    }

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

        if (isset($record['b_hid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['b_hid'])
                || empty($record['b_hid'])
                || strlen($record['b_hid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['b_sid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['b_sid'])
                || empty($record['b_sid'])
                || strlen($record['b_sid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['b_attr']) && !($record['b_attr'] instanceof Zend_Db_Expr))
        {
            if (!Zyon_Util::isInt($record['b_attr'])
                || strlen((string)$record['b_attr']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['b_name']) && !($record['b_name'] instanceof Zend_Db_Expr))
        {
            if (!is_string($record['b_name'])
                || mb_strlen($record['b_name']) > 100
            )
            {
                return false;
            }
        }

        if (isset($record['b_cost']) && !($record['b_cost'] instanceof Zend_Db_Expr))
        {
            if (!Zyon_Util::isInt($record['b_cost'])
                || strlen((string)$record['b_cost']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['b_paid']) && !($record['b_paid'] instanceof Zend_Db_Expr))
        {
            if (!Zyon_Util::isInt($record['b_paid'])
                || strlen((string)$record['b_paid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['b_memo']))
        {
            if (!is_string($record['b_memo']))
            {
                return false;
            }
        }

        if (isset($record['b_ltime']))
        {
            if (!Zyon_Util::isUnsignedInt($record['b_ltime'])
                || strlen($record['b_ltime']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['b_status']))
        {
            if (!Zyon_Util::isUnsignedInt($record['b_status'])
                || strlen($record['b_status']) > 3
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewBill
     * 
     * @param int    $hid
     * @param int    $sid
     * @param string $snm
     * @param int    $cost
     * @param int    $paid
     * @param string $name
     * @param int    $life
     * @return array
     */
    public function getNewBill($hid, $sid, $snm, $cost, $paid, $name, $life = null)
    {
        $ret = array(
            'b_hid'  => $hid,
            'b_sid'  => $sid,
            'b_snm'  => $snm,
            'b_cost' => $cost,
            'b_paid' => $paid,
            'b_name' => $name,
        );

        if ($life !== null)
        {
            $ret['b_attr']  = BILL_ATTR_GQTX;
            $ret['b_ltime'] = $life;
        }

        return $ret;
    }

    /**
     * addBill
     * 
     * @param array $map
     * @return string
     */
    public function addBill($map)
    {
        if (!$this->verify($map)
            || empty($map['b_hid'])
            || empty($map['b_sid'])
            || !isset($map['b_cost'])
            || !isset($map['b_paid'])
        )
        {
            return false;
        }

        if (!isset($map['b_ctime']))
        {
            $map['b_ctime'] = time();
        }

        if (!isset($map['b_mtime']))
        {
            $map['b_mtime'] = $map['b_ctime'];
        }

        try
        {
            $this->dbase()->insert($this->tname('bill'), $map);
            return $this->dbase()->lastInsertId();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getBill
     * 
     * @param int $id 
     * @return array
     */
    public function getBill($id)
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
            $sql = $this->dbase()->select()->from($this->tname('bill'))->where('b_id = ?')->limit(1);
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
     * modBill
     * 
     * @param int   $id
     * @param array $map
     * @return int
     */
    public function modBill($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map))
        {
            return false;
        }

        if (isset($map['b_hid']))
        {
            return false;
        }

        if (!isset($map['b_mtime']))
        {
            $map['b_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('bill'), $map, 'b_id = ' . $this->quote($id));
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
     * getBillAryByIds
     * 
     * @param array $ids 
     * @return array
     */
    public function getBillAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('bill'))
                ->where('b_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['b_id']));
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
     * getUsableActions
     * 
     * @param array $bill
     * @return array
     */
    public function getUsableActions($bill)
    {
        $code = $bill['b_status'];
        if (empty(static::$_handle[$code]))
        {
            return array();
        }

        $actions = array();
        foreach (static::$_handle[$code] as $idx)
        {
            isset(static::$_action[$idx]) AND $actions[$idx] = static::$_action[$idx];
        }

        if (isset($actions[BILL_ACTION_GBZD]))
        {
            if ($bill['b_cost'] !== $bill['b_paid'])
            {
                unset($actions[BILL_ACTION_GBZD]);
            }
            else
            {
                $oids = Model::factory('order', 'ftt')->fetch(array(
                    'where' => array(
                        'o_bid = ' . (int)$bill['b_id'],
                        'o_status <> ' . (int)ORDER_STATUS_YJS,
                        'o_status <> ' . (int)ORDER_STATUS_YQX,
                    ),
                    'field' => 'o_id'
                ));

                if (!is_array($oids) || !empty($oids))
                {
                    unset($actions[BILL_ACTION_GBZD]);
                }
            }
        }

        return $actions;
    }

    /**
     * getStatus
     * 
     * @return array
     */
    public function getStatus()
    {
        return static::$_status;
    }

    /**
     * getRtstas
     * 
     * @return array
     */
    public function getRtstas()
    {
        return static::$_rtstat;
    }

    /**
     * getStateNameByCode
     * 
     * @param string $code
     * @return string
     */
    public function getStateNameByCode($code)
    {
        return array_search($code, static::$_status, true);
    }

    /**
     * getStateCodeByName
     * 
     * @param string $name
     * @return string
     */
    public function getStateCodeByName($name)
    {
        return isset(static::$_status[$name]) ? static::$_status[$name] : null;
    }

    /**
     * getRealTimeStateConds
     * 
     * @param array $hotel
     * @param array $stats
     * @return array
     */
    public function getRealTimeStateConds($hotel, $stats = null)
    {
        if (!is_array($hotel) || ($stats !== null && !is_array($stats)))
        {
            return false;
        }

        $ctime = time();
        $conds = array();
        $stats === null AND $stats = $this->getRtstas();
        foreach ($stats as $state)
        {
            switch ($state)
            {
            case BILL_REALTIME_STATUS_YGQ:
                $conds[BILL_REALTIME_STATUS_YGQ] = sprintf(
                    '(b_status = %d AND (b_attr & %d) > 0 AND b_ltime < %d)',
                    BILL_STATUS_KF,
                    BILL_ATTR_GQTX,
                    $ctime
                );
                break;

            default :
                break;
            }
        }

        return $conds;
    }

    /**
     * getRealTimeStateCodes
     * 
     * @param array $bill
     * @param array $hotel
     * @return array
     */
    public function getRealTimeStateCodes($bill, $hotel)
    {
        $ret = array();
        if (is_array($hotel) && !empty($hotel['h_id']) && is_array($bill) && !empty($bill['b_hid'])
            && $hotel['h_id'] === $bill['b_hid'])
        {
            $now = time();

            switch ($bill['b_status'])
            {
            case BILL_STATUS_KF:
                if (($bill['b_attr'] & (int)BILL_ATTR_GQTX) > 0 && $now > $bill['b_ltime'])
                {
                    $ret[] = BILL_REALTIME_STATUS_YGQ;
                }

                break;

            default :
                break;
            }
        }

        return $ret;
    }

    /**
     * getRealTimeStateNames
     * 
     * @param array $bill
     * @param array $hotel
     * @return array
     */
    public function getRealTimeStateNames($bill, $hotel)
    {
        $names = array();
        $codes = $this->getRealTimeStateCodes($bill, $hotel);
        if ($codes)
        {
            foreach ($codes as $code)
            {
                $names[] = array_search($code, static::$_rtstat, true);
            }
        }

        return $names;
    }

    /**
     * getRealTimeStateBillAryByHid
     * 
     * @param int $hid
     * @param int $btime
     * @param int $etime
     * @return array
     */
    public function getRealTimeStateBillAryByHid($hid, $btime, $etime)
    {
        if (!Zyon_Util::isUnsignedInt($hid)
            || !is_numeric($btime) || !is_numeric($etime)
        )
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('bill'))
                ->where('b_hid = :hid')
                ->where('b_status <> ' . BILL_STATUS_GB)
                ->where('b_ctime < :etime')
                ->where('b_ctime > :btime')
                ->order('b_id ASC');

            return $this->dbase()->fetchAll($sql, array(
                'hid'   => $hid,
                'btime' => $btime,
                'etime' => $etime,
            ));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }
}
// End of file : Stat.php
