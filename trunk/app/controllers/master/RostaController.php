<?php
/**
 * @version    $Id$
 */
class Master_RostaController extends MasterController
{
    /**
     * 时间轴
     */
    public function indexAction()
    {
        $rosta = array('time' => time(), 'line' => array(), 'room' => array(), 'data' => array());

        $date = $this->input('date');
        $lgth = $this->input('lgth');
        $skip = $this->input('skip');
        $rids = $this->input('rids');

        $time = strtotime(Zyon_Util::isDate($date) ? $date : ($date = date('Y-m-d', $rosta['time']-86400)));

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

        // 订单查询默认条件
        $where = array(
            'o_hid = ' . $this->model('order')->quote($this->_master['u_hid']),
            'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
            // 'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
        );

        // 指定房间编号集合
        if ($rids)
        {
            $rids = explode(',', $rids);
            if (empty($rids))
            {
                $rooms = array();
            }
            else
            {
                $rooms = Zyon_Array::keyto($this->model('room')->getRoomAryByIds($rids, $this->_master['u_hid']), 'r_id');
            }
        }
        else
        {
            $rooms = Zyon_Array::keyto($this->model('room')->getRoomAryByHid($this->_master['u_hid']), 'r_id');
        }

        if (!empty($rooms))
        {
            foreach ($rooms as $key => &$room)
            {
                if ($room['r_attr'] & ROOM_ATTR_YCFT)
                {
                    unset($rooms[$key]);
                }
            }
            unset($room);

            if (!empty($rooms))
            {
                // 增加查询房间限定
                $where[] = sprintf('o_rid IN (%s)', implode(',', array_keys($rooms)));
            }
        }

        // 取得上（下）一个有订单时间临界点
        if (!empty($rooms) && $skip)
        {
            if ($lgth < 0)
            {
                $orders = $this->model('order')->fetch(array(
                    'where' => array_merge($where, array('o_etime < ' . $this->model('order')->quote($time))),
                    'order' => 'o_etime DESC',
                    'field' => 'o_edatm',
                    'limit' => 1,
                ));

                if ($orders)
                {
                    $time = (int)$orders[0]['o_edatm'];
                }
            }
            else
            {
                $orders = $this->model('order')->fetch(array(
                    'where' => array_merge($where, array('o_btime > ' . $this->model('order')->quote($time))),
                    'order' => 'o_btime ASC',
                    'field' => 'o_bdatm',
                    'limit' => 1,
                ));

                if ($orders)
                {
                    $time = (int)$orders[0]['o_bdatm'];
                }
            }
        }

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

        if (!empty($rooms))
        {
            $obnum = $this->model('order')->calNumAryByRidAryAndSta(
                array_keys($rooms), ORDER_STATUS_YD, $rosta['time']-86400*3
            );

            foreach ($rooms as &$room)
            {
                $rosta['data'][$room['r_id']] = array();
                $rosta['room'][$room['r_id']] = array(
                    'rid' => $room['r_id'],
                    'sta' => $room['r_status'],
                    'rnm' => $this->view->escape($room['r_name']),
                    'ext' => array(
                        'rtp' => $this->view->escape($room['r_type']), 
                        'rlo' => $this->view->escape($room['r_layout']), 
                        'rze' => $this->view->escape($room['r_zone']),
                        'rvw' => $this->model('room')->getViewListByCode($room['r_view']),
                    ),
                    // 房间开张时间
                    'ovt' => (int)$room['r_otime'],
                    // 房间停用时间
                    'lvt' => (int)$room['r_btime'],
                    'rvt' => (int)$room['r_etime'],
                    // 今日预订数量
                    'obn' => (int)(isset($obnum[$room['r_id']]) ? $obnum[$room['r_id']] : 0),
                );
            }
            unset($room);

            $orders = Zyon_Array::keyto($this->model('order')->fetchAry(
                array_merge($where, array(
                    'o_btime < ' . $this->model('order')->quote($etime),
                    'o_etime > ' . $this->model('order')->quote($btime),
                )),
                'o_id DESC'
            ), 'o_id');

            if (!empty($orders))
            {
                $others = array();
                $owhere = array();
                foreach ($orders as &$order)
                {
                    $rosta['data'][$order['o_rid']][$order['o_id']] = array(
                        'key' => $order['o_mtime'],
                        'oid' => $order['o_id'],
                        'bid' => $order['o_bid'],
                        // 订单属性、当前状态
                        'ats' => (int)$order['o_attr'],
                        'sta' => (int)$order['o_status'],
                        // 订单入住、离店时间
                        'lvt' => $order['o_btime'],
                        'rvt' => $order['o_etime'],
                        // 可用操作
                        'act' => $this->model('order')->getUsableActions($order, $this->_hostel, false),
                        // 旅客信息
                        'gst' => array(
                            'bkg' => array(array('gnm' => $this->view->escape($order['o_gbker_name']), 'tel' => $this->view->escape($order['o_gbker_phone']))),
                            'lvg' => array(array('gnm' => $this->view->escape($order['o_glver_name']), 'tel' => $this->view->escape($order['o_glver_phone']))),
                        ),
                    );

                    if ($order['o_memo'] == '')
                    {
                        $rosta['data'][$order['o_rid']][$order['o_id']]['tip'] = '';
                    }

                    // 检测订单与房态的冲突
                    if ($order['o_btime'] <= $rooms[$order['o_rid']]['r_etime']
                        && $order['o_edatm'] > $rooms[$order['o_rid']]['r_btime'])
                    {
                        unset($rosta['data'][$order['o_rid']][$order['o_id']]['act'][ORDER_ACTION_BLDD]);
                        unset($rosta['data'][$order['o_rid']][$order['o_id']]['act'][ORDER_ACTION_BLRZ]);
                    }

                    // 拆分占位和不占位订单
                    if ($order['o_status'] == ORDER_STATUS_YD)
                    {
                        $owhere = empty($owhere) ? array(min($order['o_btime'], $btime), max($order['o_etime'], $etime))
                            : array(min($order['o_btime'], $owhere[0]), max($order['o_etime'], $owhere[1]));
                    }
                    else
                    {
                        $others[$order['o_id']] = $order;
                        unset($orders[$order['o_id']]);
                    }
                }
                unset($order);

                // 获取扩展时间段的订单
                if (!empty($owhere) && ($owhere[0] < $btime || $owhere[1] > $etime))
                {
                    $others += Zyon_Array::keyto($this->model('order')->fetch(array(
                        'where' => array_merge($where, array(
                            sprintf('o_id NOT IN (%s)', implode(',', array_merge(array_keys($orders), array_keys($others)))),
                            'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                            'o_btime < ' . $this->model('order')->quote($owhere[1]),
                            'o_etime > ' . $this->model('order')->quote($owhere[0])
                        )),
                        'field' => array('o_id', 'o_rid', 'o_btime', 'o_etime'),
                    )), 'o_id');
                }

                // 检测不占位的订单冲突
                foreach ($orders as &$order)
                {
                    foreach ($others as &$other)
                    {
                        if ($other['o_rid'] === $order['o_rid']
                            && $other['o_btime'] < $order['o_etime'] && $other['o_etime'] > $order['o_btime'])
                        {
                            unset($rosta['data'][$order['o_rid']][$order['o_id']]['act'][ORDER_ACTION_BLDD]);
                            unset($rosta['data'][$order['o_rid']][$order['o_id']]['act'][ORDER_ACTION_BLRZ]);
                            break;
                        }
                    }
                    unset($other);
                }

                unset($order, $orders, $others);
            }
        }

        if ($this->input('ct') === 'json')
        {
            $this->flash(1, array('context' => $rosta));
        }

        $this->view->hotel = $this->_hostel;
        $this->view->rosta = $rosta;
    }
}
// End of file : RostaController.php
