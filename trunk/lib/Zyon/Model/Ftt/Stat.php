<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Stat extends Zyon_Model_Ftt
{
    /**
     * getStatWait
     * 
     * @param int   $hid
     * @param mixed $len
     * @return int 
     */
    public function getStatWait($hid, $len = null)
    {
        $hotel = Model::factory('hotel', 'ftt')->getHotel($hid);
        return getSysLimit(
            $len === STAT_LGTH_D ? 'TASK_TODO_WAIT' : 'STAT_WAIT', isset($hotel['h_level']) ? $hotel['h_level'] : null
        );
    }

    /**
     * getStatDays
     * 
     * @param int $hid
     * @return array
     */
    public function getStatDays($hid)
    {
        $hotel = Model::factory('hotel', 'ftt')->getHotel($hid);
        return getSysLimit('STAT_DAYS', isset($hotel['h_level']) ? $hotel['h_level'] : null);
    }

    /**
     * getStatLgth
     * 
     * @param int $hid
     * @return array
     */
    public function getStatLgth($hid)
    {
        $lgth = $this->getStatDays($hid);
        if (!isset($lgth['len']) || !is_array($lgth = $lgth['len']))
        {
            return array();
        }

        $maps = getStatLgths();
        $lgth = array_combine($lgth, str_replace(array_keys($maps), $maps, $lgth));

        return $lgth;
    }

    /**
     * getStatTimeLine
     * 
     * @param int   $hid
     * @param mixed $len
     * @param mixed $day
     * @return array
     */
    public function getStatTimeLine($hid, $len, $day)
    {
        if (!is_numeric($hid) || !Zyon_Util::isDate($day))
        {
            return false;
        }

        $days = $this->getStatDays($hid);
        if (!in_array($len, $days['len'], true))
        {
            return false;
        }

        $dtm = strtotime($day);
        $now = strtotime(date('Y-m-d'));
        $min = $now-$days['min']*86400;
        $max = $now+($days['max']+1)*86400;

        if ($dtm < $min || $dtm > $max)
        {
            return false;
        }

        $ret = array();
        switch ($len)
        {
        case STAT_LGTH_D:
            $ret = array($dtm, $dtm+86399);

            break;
        case STAT_LGTH_W:
            $ret[0] = $dtm - ((date('w', $dtm) ?: 7) - 1)*86400;
            $ret[1] = $ret[0] + 86400*6+86399;

            break;
        case STAT_LGTH_M:
            $ret[0] = strtotime(date('Y-m-01', $dtm));
            $ret[1] = strtotime(date('Y-m-', $dtm) . date('t', $dtm))+86399;

            break;
        case STAT_LGTH_S:
            $mon = date('n', $dtm);
            foreach (array(3 => 31, 6 => 30, 9 => 30, 12 => 31) as $key => $val)
            {
                if ($mon <= $key)
                {
                    $ret[0] = strtotime(date('Y-' . ($key-2) . '-01', $dtm));
                    $ret[1] = strtotime(date('Y-' . $key . '-' . $val, $dtm))+86399;

                    break;
                }
            }

            break;
        case STAT_LGTH_Y:
            $ret[0] = strtotime(date('Y-01-01', $dtm));
            $ret[1] = strtotime(date('Y-12-31', $dtm))+86399;

            break;
        default :
            break;
        }

        if (!$ret || $ret[0] > $ret[1] || $ret[0] < $min || $ret[1] > $max)
        {
            return false;
        }

        return $ret;
    }

    /**
     * calDateStat
     * 
     * @param mixed $day
     * @param mixed $hid
     * @param mixed $cls
     * @return array
     */
    public function calDateStat($day, $hid, $cls = null)
    {
        if (!$this->verify(array('s_date' => $day, 's_hid' => $hid)))
        {
            return false;
        }

        $ret = array();

        /**
         * 计算旅店入住率
         */
        if ($cls === null || $cls === HSTAT_CLASS_HOTEL_RZL)
        {
            $ret[HSTAT_CLASS_HOTEL_RZL] = array(
                'class' => HSTAT_CLASS_HOTEL_RZL,
                'datas' => array(),
            );

            $roomn = Model::factory('hotel', 'ftt')->calRoomNumByDay($day, $hid);
            if (!empty($roomn[$hid]))
            {
                $liven = Model::factory('hotel', 'ftt')->calLiveNumByDay($day, $hid);
                $ret[HSTAT_CLASS_HOTEL_RZL]['datas'][] = array(
                    'value' => round((isset($liven[$hid]) ? $liven[$hid] : 0)*100 / $roomn[$hid], 2),
                );
            }
        }

        /**
         * 计算旅店不同房型的房间入住率
         */
        if ($cls === null || $cls === HSTAT_CLASS_RTYPE_RZL)
        {
            $ret[HSTAT_CLASS_RTYPE_RZL] = array(
                'class' => HSTAT_CLASS_RTYPE_RZL,
                'datas' => array(),
            );

            /**
             * 依据房型，计算每日入住率
             */
            $rooms = Model::factory('room', 'ftt')->getRoomIdsGroupWithTypeByDayAndHid($day, $hid);
            if (!empty($rooms))
            {
                foreach ($rooms as $type => $rids)
                {
                    $ret[HSTAT_CLASS_RTYPE_RZL]['datas'][] = array(
                        'value' => round(Model::factory('room', 'ftt')->calLiveNumByDateAndRids($day, $rids)*100/count(explode(',', $rids)), 2),
                        'label' => $type,
                    );
                }
            }
        }

        return $ret;
    }

    /**
     * calZongFangLiang
     * 计算某一时段的总房量
     * 
     * @param int  $hid
     * @param date $bdate
     * @param date $edate
     * @return int
     */
    public function calZongFangLiang($hid, $bdate, $edate)
    {
        if (!Zyon_Util::isUnsignedInt($hid) || !Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            return false;
        }

        $btime = strtotime($bdate);
        $etime = strtotime($edate) + 86400;

        if ($etime <= $btime)
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from(
                $this->tname('room'),
                $this->expr('SUM(DATEDIFF(FROM_UNIXTIME(:etime), FROM_UNIXTIME(GREATEST(r_otime, :btime))))')
            )->where('r_hid = :hid')->where('r_otime < :etime');

            return (int)$this->dbase()->fetchOne($sql, array(
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

    /**
     * calTingYongFangLiang
     * 计算某一时段的停用房量
     * 
     * @param int  $hid
     * @param date $bdate
     * @param date $edate
     * @return int
     */
    public function calTingYongFangLiang($hid, $bdate, $edate)
    {
        if (!Zyon_Util::isUnsignedInt($hid) || !Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            return false;
        }

        $btime = strtotime($bdate);
        $etime = strtotime($edate) + 86400;

        if ($etime <= $btime)
        {
            return false;
        }

        try
        {
            $ret = 0;

            $sql = $this->dbase()->select()->from(
                $this->tname('room'),
                array('r_id', 'r_otime', 'r_btime', 'r_etime')
            )
            ->where('r_hid = :hid')
            ->where('r_btime > 0')
            ->where('r_btime < :etime');

            $ary = $this->dbase()->fetchAll($sql, array(
                'hid'   => $hid,
                'etime' => $etime,
            ));

            if (!empty($ary))
            {
                foreach ($ary as &$val)
                {
                    $val['r_btime'] = strtotime(date('Y-m-d', max($val['r_otime'], $val['r_btime'], $btime)));
                    $val['r_etime'] = min(strtotime(date('Y-m-d', $val['r_etime']))+86400, $etime);

                    $ret += ($val['r_etime'] - $val['r_btime'])/86400;
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
     * calRuZhuLiang
     * 计算某一时段的入住量，以渠道分组
     * 
     * @param int  $hid
     * @param date $bdate
     * @param date $edate
     * @return array
     */
    public function calRuZhuLiang($hid, $bdate, $edate)
    {
        if (!Zyon_Util::isUnsignedInt($hid) || !Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            return false;
        }

        $btime = strtotime($bdate) + 86399;
        $etime = strtotime($edate) + 86400;

        if ($etime <= $btime)
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('order'),
                'o_cid, SUM(DATEDIFF(FROM_UNIXTIME(LEAST(o_etime, :etime)), FROM_UNIXTIME(GREATEST(o_btime, :btime))))'
            )
            ->where('o_hid = :hid')
            ->where('NOT (o_btime >= :etime OR o_etime <= :btime)')
            ->where(sprintf('o_status IN (%s, %s)',
                $this->quote(ORDER_STATUS_ZZ),
                $this->quote(ORDER_STATUS_YJS)
            ))
            ->group('o_cid');

            return $this->dbase()->fetchPairs($sql, array(
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

    /**
     * calYiWanChengDingDan
     * 计算某一时段的已完成订单，以渠道分组
     * 
     * @param int  $hid
     * @param date $bdate
     * @param date $edate
     * @return array
     */
    public function calYiWanChengDingDan($hid, $bdate, $edate)
    {
        if (!Zyon_Util::isUnsignedInt($hid) || !Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            return false;
        }

        $btime = strtotime($bdate);
        $etime = strtotime($edate) + 86399;

        if ($etime <= $btime)
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('order'),
                'o_cid, count(o_id)'
            )
            ->where('o_hid = :hid')
            ->where('NOT (o_btime >= :etime OR o_etime <= :btime)')
            ->where(sprintf('o_status = %s', $this->quote(ORDER_STATUS_YJS)))
            ->group('o_cid');

            return $this->dbase()->fetchPairs($sql, array(
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

    /**
     * calZaiZhuDingDan
     * 计算某一时段的在住订单，以渠道分组
     * 
     * @param int  $hid
     * @param date $bdate
     * @param date $edate
     * @return array
     */
    public function calZaiZhuDingDan($hid, $bdate, $edate)
    {
        if (!Zyon_Util::isUnsignedInt($hid) || !Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            return false;
        }

        $btime = strtotime($bdate);
        $etime = strtotime($edate) + 86399;

        if ($etime <= $btime)
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('order'),
                'o_cid, count(o_id)'
            )
            ->where('o_hid = :hid')
            ->where('NOT (o_btime >= :etime OR o_etime <= :btime)')
            ->where(sprintf('o_status = %s', $this->quote(ORDER_STATUS_ZZ)))
            ->group('o_cid');

            return $this->dbase()->fetchPairs($sql, array(
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

    /**
     * calYuDingLiang
     * 计算某一时段的预订量，以渠道分组
     * 
     * @param int  $hid
     * @param date $bdate
     * @param date $edate
     * @return array
     */
    public function calYuDingLiang($hid, $bdate, $edate)
    {
        if (!Zyon_Util::isUnsignedInt($hid) || !Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            return false;
        }

        $btime = strtotime($bdate) + 86399;
        $etime = strtotime($edate) + 86400;

        if ($etime <= $btime)
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('order'),
                'o_cid, SUM(DATEDIFF(FROM_UNIXTIME(LEAST(o_etime, :etime)), FROM_UNIXTIME(GREATEST(o_btime, :btime))))'
            )
            ->where('o_hid = :hid')
            ->where('NOT (o_btime >= :etime OR o_etime <= :btime)')
            ->where(sprintf('o_status IN (%s, %s)',
                $this->quote(ORDER_STATUS_YD),
                $this->quote(ORDER_STATUS_BL)
            ))
            ->group('o_cid');

            return $this->dbase()->fetchPairs($sql, array(
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
