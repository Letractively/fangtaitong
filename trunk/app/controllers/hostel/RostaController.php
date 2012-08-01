<?php
/**
 * @version    $Id$
 */
class Hostel_RostaController extends HostelController
{
    /**
     * 旅店房态
     */
    public function indexAction()
    {
        $this->checkHotelStat($hotel = $this->loadUsableHotel($this->input('hid')));
        if ((!$this->_master || $this->_master['u_hid'] !== $hotel['h_id'])
            && !(SYSTEM_GROUPS_GSER & (int)$hotel['h_rosta_visible']))
        {
            $this->flash(0, '旅店尚未开放该功能');
        }

        $rosta = array('time' => time(), 'line' => array(), 'room' => array(), 'data' => array());

        $date = $this->input('date');
        $lgth = $this->input('lgth');

        $time = strtotime(Zyon_Util::isDate($date) ? $date : ($date = date('Y-m-d', $rosta['time'])));

        if (!Zyon_Util::isInt($lgth))
        {
            $lgth = 8;
        }
        $lgth = (int)$lgth;

        if (abs($lgth) > 30 || abs($lgth) < 2)
        {
            $this->flash(0, '入住时间和离店时间间隔最少 2天，最长 30天');
        }

        $lgth = $lgth > 0 ? $lgth - 1 : $lgth + 1;

        // 令 {$time} 为左值
        if ($lgth < 0)
        {
            $time = $time + $lgth*86400;
            $lgth = abs($lgth);
        }

        $btime = $time;
        $etime = $btime + $lgth*86400 + 86399;

        $rosta['line'][] = $btime;
        $rosta['line'][] = $etime;

        if ($btime > $rosta['time'] - 86400)
        {
            // 订单查询默认条件
            $where = array(
                'o_hid = ' . $this->model('order')->quote($hotel['h_id']),
                'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                // 'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
            );

            $rooms = Zyon_Array::keyto($this->model('room')->getRoomAryByHid($hotel['h_id']), 'r_id');
            if (!empty($rooms))
            {
                foreach ($rooms as $key => &$room)
                {
                    if ($room['r_attr'] & ROOM_ATTR_YCFT)
                    {
                        unset($rooms[$key]);
                        continue;
                    }

                    $rosta['data'][$room['r_id']] = array();
                    $rosta['rent'][$room['r_id']] = array();
                    $rosta['room'][$room['r_id']] = array(
                        'rid' => $room['r_id'],
                        'rnm' => $this->view->escape($room['r_name']),
                        'ext' => array(
                            'rtp' => $this->view->escape($room['r_type']), 
                            'rlo' => $this->view->escape($room['r_layout']), 
                            'rze' => $this->view->escape($room['r_zone']), 
                            'rvw' => $this->model('room')->getViewListByCode($room['r_view']),
                        )
                    );

                    if ($btime < $room['r_otime'])
                    {
                        $_r_btime = $btime;
                        $_r_otime = min($etime, $room['r_otime']);

                        while ($_r_btime < $_r_otime)
                        {
                            $rosta['data'][$room['r_id']][($_r_btime-$btime)/86400] = ROSTA_BL;
                            $_r_btime += 86400;
                        }
                    }

                    if ($room['r_btime'])
                    {
                        $_r_btime = max($btime, $room['r_btime']);
                        $_r_etime = min($etime, $room['r_etime']);

                        while ($_r_btime < $_r_etime)
                        {
                            $rosta['data'][$room['r_id']][($_r_btime-$btime)/86400] = ROSTA_BL;
                            $_r_btime += 86400;
                        }
                    }
                }
                unset($room);
            }

            if (!empty($rooms))
            {
                // 增加查询房间限定
                $where[] = sprintf('o_rid IN (%s)', implode(',', array_keys($rooms)));

                $orders = Zyon_Array::keyto($this->model('order')->getOrderAryByIds($this->model('order')->fetchIds(
                    array_merge($where, array(
                        'o_bdatm < ' . $this->model('order')->quote($etime),
                        'o_edatm > ' . $this->model('order')->quote($btime),
                    ))
                )), 'o_id');

                if (!empty($orders))
                {
                    foreach ($orders as &$order)
                    {
                        switch ($order['o_status'])
                        {
                        case ORDER_STATUS_YD:
                            $sta = ROSTA_YD;
                            break;

                        default :
                            $sta = ROSTA_BL;
                            break;
                        }

                        $_o_btime = max($btime, $order['o_bdatm']);
                        $_o_etime = min($etime, $order['o_edatm']);

                        while ($_o_btime < $_o_etime)
                        {
                            $key = ($_o_btime-$btime)/86400;
                            if (!isset($rosta['data'][$order['o_rid']][$key])
                                || $rosta['data'][$order['o_rid']][$key] !== ROSTA_BL)
                            {
                                $rosta['data'][$order['o_rid']][$key] = $sta;
                            }

                            $_o_btime += 86400;
                        }
                    }
                    unset($order);
                }

                $prices = $this->model('room.price')->getPriceDotAryByHid($hotel['h_id'], $btime, $etime);

                if (!empty($prices))
                {
                    foreach ($prices as $rid => &$lst)
                    {
                        if (isset($rooms[$rid]))
                        {
                            foreach ($lst as $dtm => $val)
                            {
                                $val = (int)$val;
                                if (($end = end($rosta['rent'][$rid])) !== false && $end === $val)
                                {
                                    unset($rosta['rent'][$rid][$key = key($rosta['rent'][$rid])]);

                                    $key = explode(',', $key);
                                    $key[1] = ($dtm-$btime)/86400;
                                    $rosta['rent'][$rid][implode(',', $key)] = $val;
                                }
                                else
                                {
                                    $rosta['rent'][$rid][($dtm-$btime)/86400] = $val;
                                }
                            }
                        }
                    }
                    unset($lst);
                }

                // 合并相同状态的单元格，键 P 改为 L,R
                foreach ($rosta['data'] as $key => &$val)
                {
                    $lst = array();
                    ksort($val, SORT_NUMERIC);

                    $idx = -1;
                    foreach ($val as $k => $v)
                    {
                        if ($k && isset($val[$k-1]) && $val[$k-1] === $v)
                        {
                            $lst[$idx][2] = $k;
                        }
                        else
                        {
                            ++$idx;
                            $lst[] = array($v, $k);
                        }
                    }

                    $rosta['data'][$key] = array();
                    foreach ($lst as $k => $v)
                    {
                        $rosta['data'][$key][isset($v[2]) ? ($v[1].','.$v[2]) : $v[1]] = $v[0];
                    }
                }
            }
        }

        if ($this->input('ct') === 'json')
        {
            $this->flash(1, array('context' => $rosta));
        }

        $this->view->hotel = $hotel;
        $this->view->rosta = $rosta;
    }
}
// End of file : RostaController.php
