<?php
/**
 * @version    $Id$
 */
class Master_OrderController extends MasterController
{
    /**
     * 单次预订操作能创建的订单数上限
     */
    const ORDER_LIMIT = 9;

    /**
     * 单订单日期长度上限
     */
    const ORDER_LIMIT_DSIZE = 31;

    /**
     * 订单列表
     */
    public function indexAction()
    {
        $where = array(
            'o_hid = ' . $this->model('order')->quote($this->_master['u_hid']),
        );
        $guest = array();

        $pager = array();

        $pager['qnty'] = (int)$this->input('qnty', 'numeric');
        empty($pager['qnty']) AND $pager['qnty'] = 25;
        $pager['qnty'] = min($pager['qnty'], 30);

        $pager['page'] = (int)$this->input('page', 'numeric');
        empty($pager['page']) AND $pager['page'] = 1;

        $query = array();
        $query['qnty'] = $pager['qnty'];

        $query['name'] = $this->input('name');

        $query['type'] = $this->input('type');
        in_array($query['type'], array('uqid', 'omno', 'book', 'live', 'call', 'room', 'cnnm', 'type', 'osnm'), true) OR $query['type'] = $query['name'] = null;

        $query['cdate0'] = $this->input('cdate0');
        $query['cdate1'] = $this->input('cdate1');
        $query['bdate0'] = $this->input('bdate0');
        $query['bdate1'] = $this->input('bdate1');
        $query['edate0'] = $this->input('edate0');
        $query['edate1'] = $this->input('edate1');

        if ($query['cdate0'] != '')
        {
            if (!Zyon_Util::isDate($query['cdate0']))
            {
                $this->flash(0, '查询订单创建起始时间错误');
            }

            $ctime0 = strtotime($query['cdate0']);
            $where[] = 'o_ctime >= ' . $this->model('order')->quote($ctime0);
        }

        if ($query['cdate1'] != '')
        {
            if (!Zyon_Util::isDate($query['cdate1']))
            {
                $this->flash(0, '查询订单创建结束时间错误');
            }

            $ctime1 = strtotime($query['cdate1']) + 86399;
            $where[] = 'o_ctime <= ' . $this->model('order')->quote($ctime1);
        }

        if ($query['bdate0'] != '')
        {
            if (!Zyon_Util::isDate($query['bdate0']))
            {
                $this->flash(0, '查询预计入住起始时间错误');
            }

            $btime0 = strtotime($query['bdate0']);
            $where[] = 'o_btime >= ' . $this->model('order')->quote($btime0);
        }

        if ($query['bdate1'] != '')
        {
            if (!Zyon_Util::isDate($query['bdate1']))
            {
                $this->flash(0, '查询预计入住结束时间错误');
            }

            $btime1 = strtotime($query['bdate1']) + 86399;
            $where[] = 'o_btime <= ' . $this->model('order')->quote($btime1);
        }

        if ($query['edate0'] != '')
        {
            if (!Zyon_Util::isDate($query['edate0']))
            {
                $this->flash(0, '查询预计离店起始时间错误');
            }

            $etime0 = strtotime($query['edate0']);
            $where[] = 'o_etime >= ' . $this->model('order')->quote($etime0);
        }

        if ($query['edate1'] != '')
        {
            if (!Zyon_Util::isDate($query['edate1']))
            {
                $this->flash(0, '查询预计离店结束时间错误');
            }

            $etime1 = strtotime($query['edate1']) + 86399;
            $where[] = 'o_etime <= ' . $this->model('order')->quote($etime1);
        }

        $query['rtsta'] = $this->input('rtsta', 'array');
        $query['rtsta'] OR $query['rtsta'] = array();
        $query['rtsta'] = array_unique($query['rtsta']);

        if (!empty($query['rtsta']))
        {
            $rtstas = $this->model('order')->getRealTimeStateConds($this->_hostel, $query['rtsta']);
            if (!empty($rtstas))
            {
                $where[] = implode(' OR ', $rtstas);
            }
        }

        $query['state'] = $this->input('state', 'array');
        $query['state'] OR $query['state'] = array();
        foreach ($query['state'] as $state)
        {
            if (!$this->model('order')->getStateNameByCode($state))
            {
                $this->flash(0, '订单状态错误');
            }
        }
        $query['state'] = array_unique($query['state']);

        if (!empty($query['state']))
        {
            $where[] = 'o_status IN (' . implode(',', array_map(array($this->model('order'), 'quote'), $query['state'])) . ')';
        }

        if ($query['name'] != '' && $query['type'] != '')
        {
            if ($query['type'] == 'uqid')
            {
                $where[] = 'o_id = ' . $this->model('order')->quote($query['name']);
            }
            else if ($query['type'] == 'omno')
            {
                $where[] = 'o_mno = ' . $this->model('order')->quote($query['name']);
            }
            else if ($query['type'] == 'room')
            {
                $where[] = 'o_room = ' . $this->model('order')->quote($query['name']);
            }
            else if ($query['type'] == 'cnnm')
            {
                $where[] = 'o_cnm = ' . $this->model('order')->quote($query['name']);
            }
            else if ($query['type'] == 'type')
            {
                $where[] = 'o_tnm = ' . $this->model('order')->quote($query['name']);
            }
            else if ($query['type'] == 'osnm')
            {
                $where[] = 'o_snm = ' . $this->model('order')->quote($query['name']);
            }
            else
            {
                if ($query['type'] == 'call')
                {
                    $guest['call'] = $query['name'];
                }
                else if ($query['type'] == 'mail')
                {
                    $guest['mail'] = $query['name'];
                }
                else
                {
                    $guest['type'] = $query['type'] == 'live' ? HOTEL_GUEST_TYPE_LIVE : HOTEL_GUEST_TYPE_BOOK;
                    $guest['name'] = $query['name'];
                }
            }
        }

        $orders = Zyon_Array::keyto(
            $this->model('order')->getIndexOrderAry(
                $where,
                $guest,
                'o_id DESC',
                array($pager['qnty'], $pager['qnty']*($pager['page'] - 1))
            ),
            'o_id'
        );
        empty($orders) AND $orders = array();

        foreach ($orders as &$order)
        {
            $order['state'] = $this->model('order')->getStateNameByCode($order['o_status']);
            $order['rtsta'] = $this->model('order')->getRealTimeStateNames($order, $this->_hostel);
        }
        unset($order);

        $pager['list'] = count($orders);
        $pager['args'] = $query;

        $this->view->pager  = $pager;
        $this->view->params = $query;
        $this->view->orders = $orders;
        $this->view->states = $this->model('order')->getStatus();
        $this->view->rtstas = $this->model('order')->getRtstas();
    }

    /**
     * 读取价格
     */
    public function fetchValueAction()
    {
        $order = $this->loadUsableOrder($this->input('oid', 'numeric'));
        $this->flash(1, array('context' => array(
            'price' => json_decode($order['o_prices'], true),
            'brice' => json_decode($order['o_brices'], true),
        )));
    }

    /**
     * 读取备注
     */
    public function fetchMemoAction()
    {
        $order = $this->loadUsableOrder($this->input('oid', 'numeric'));
        $this->flash(1, array('context' => (string)$order['o_memo']));
    }

    /**
     * 创建订单
     */
    public function createAction()
    {
        if (!$this->getRequest()->isPost())
        {
            $this->flash(0, '错误来源的表单提交');
        }

        $order = $this->input('order', 'array');
        if (empty($order))
        {
            $this->flash(0, '没有选择预订的房间');
        }

        if (($count = count($order)) > static::ORDER_LIMIT)
        {
            $this->flash(0, __('单次预订不允许超过%d个房间', static::ORDER_LIMIT));
        }

        $ctime = time();
        $dtime = strtotime(date('Y-m-d', $ctime));

        $order = array_values($order);
        $saved = $value_rules_conds = $other_order_conds = array();
        foreach ($order as $idx => &$val)
        {
            if (!is_array($val)
                || !isset($val['room']) || !isset($val['date']) || !isset($val['lgth'])
                || !Zyon_Util::isUnsignedInt($val['room'])
                || !Zyon_Util::isDate($val['date']) || !Zyon_Util::isUnsignedInt($val['lgth'])
                || $val['room'] < 1 || $val['lgth'] < 1
            )
            {
                $this->flash(0, '参数错误');
            }

            if ($val['lgth'] < $this->_hostel['h_order_minlens'] || $val['lgth'] > $this->_hostel['h_order_maxlens'])
            {
                $this->flash(0, __('旅店仅支持创建天数介于 %d 到 %d 的订单',
                    $this->_hostel['h_order_minlens'],
                    $this->_hostel['h_order_maxlens']
                ));
            }

            $val['datm'] = strtotime($val['date']);
            $val['time'] = array(0, 86399);

            $value_rules_conds[] = sprintf('(rp_rid = %d AND rp_btime <= %d AND rp_etime >= %d)',
                $val['room'],
                $val['datm'] + ($val['lgth']-1)*86400,
                $val['datm']
            );

            $other_order_conds[] = sprintf('(o_rid = %d AND o_bdatm <= %d AND o_edatm >= %d)',
                $val['room'],
                $val['datm'] + $val['lgth']*86400,
                $val['datm']
            );

            $saved[$val['room']] = array();
        }
        unset($val);
        $order = Zyon_Array::group($order, 'room');

        $value_rules_conds = implode(' OR ', $value_rules_conds);
        $other_order_conds = implode(' OR ', $other_order_conds);

        $rooms = Zyon_Array::keyto($this->model('room')->getRoomAryByIds(array_keys($order)), 'r_id');
        if (empty($rooms) || count($rooms) !== count($order))
        {
            $this->flash(0, '找不到指定预订的房间');
        }

        // 以下处理每个房间的启用日、停用段以及订单最长时间限制
        foreach ($rooms as $rid => &$val)
        {
            if ($val['r_hid'] !== $this->_hostel['h_id'])
            {
                $this->flash(0, '找不到指定预订的房间');
            }

            foreach ($order[$rid] as &$v)
            {
                $btime = $v['datm'];
                $etime = $btime + $v['lgth']*86400-1;

                for ($_btime = max($btime, $dtime+86400*$this->_hostel['h_order_enddays']); $_btime < $etime; $_btime+=86400)
                {
                    $saved[$rid][$_btime] = 1;
                }

                if ($val['r_btime'])
                {
                    $_r_btime = max($btime, $val['r_btime']);
                    $_r_etime = min($etime, $val['r_etime']);

                    while ($_r_btime < $_r_etime)
                    {
                        $saved[$rid][$_r_btime] = 1;
                        $_r_btime += 86400;
                    }
                }

                if ($btime < $val['r_otime'])
                {
                    $_o_btime = $btime;
                    $_r_otime = min($etime, $val['r_otime']);

                    while ($_o_btime < $_r_otime)
                    {
                        $saved[$rid][$_o_btime] = 1;
                        $_o_btime += 86400;
                    }
                }
            }
            unset($v);
        }
        unset($val);

        // 以下处理每个订单的冲突
        $other = $this->model('order')->getOrderAryByIds(
            $this->model('order')->fetchIds(array(
                'o_hid = ' . $this->_hostel['h_id'],
                'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                $other_order_conds,
            ))
        );
        if (!is_array($other))
        {
            $this->flash(0, '无法获取系统订单的信息');
        }

        if (!empty($other))
        {
            foreach ($other as &$val)
            {
                $rid = $val['o_rid'];
                foreach ($order[$rid] as &$v)
                {
                    if ($val['o_edatm'] == $v['datm'])
                    {
                        $v['time'][0] = max($v['time'][0], $val['o_etime'] - $val['o_edatm']);
                    }

                    if ($val['o_bdatm'] == $v['datm'] + $v['lgth']*86400)
                    {
                        $v['time'][1] = min($v['time'][1], $val['o_btime'] - $val['o_bdatm']);
                    }

                    $_o_btime = max($v['datm'], $val['o_bdatm']);
                    $_o_etime = min($v['datm'] + $v['lgth']*86400, $val['o_edatm']);

                    while ($_o_btime < $_o_etime)
                    {
                        $saved[$rid][$_o_btime] = 1;
                        $_o_btime += 86400;
                    }
                }
                unset($v);
            }
            unset($val);
        }
        unset($other);

        // 以下处理每个订单的房费
        $basic = $this->model('room.price')->getBasicPriceByRids(array_keys($rooms));
        if (!$basic || count($basic) !== count($rooms))
        {
            $this->flash(0, '无法获取预订房间的房价');
        }

        $rules = Zyon_Array::group($this->model('room.price')->getPriceAryByIds(
            $this->model('room.price')->fetchIds(array(
                'rp_hid = ' . $this->_hostel['h_id'],
                $value_rules_conds,
            ))
        ), 'rp_rid');

        $price = array();
        foreach (array_keys($basic) as $rid)
        {
            $price[$rid] = array();

            foreach ($order[$rid] as &$val)
            {
                $price[$rid] += $this->model('room.price')->getPriceDotAry(
                    $basic[$rid],
                    $val['datm'], $val['datm'] + $val['lgth']*86400-1,
                    isset($rules[$rid]) ? $rules[$rid] : array()
                );
            }
            unset($val);
        }
        unset($basic, $rules);

        // 暂时将两个价格等同
        $brice = $price;

        // 以下处理订单的预订员列表
        $sales = $this->model('user')->getUsableUserAryByHid($this->_hostel['h_id']);
        if (is_array($sales) && count($sales) > 1)
        {
            $sales = Zyon_Array::keyto($sales, 'u_id');
            unset($sales[$this->_master['u_id']]);
            $sales = array_values($sales);

            array_unshift($sales, $this->_master);
        }

        $this->view->ctime = $ctime;
        $this->view->rooms = $rooms;
        $this->view->order = $order;
        $this->view->price = $price;
        $this->view->brice = $brice;
        $this->view->saved = $saved;
        $this->view->sales = $sales;

        $this->view->cnns = $this->model('hotel.channel')->getUsableChannelAryByHid($this->_hostel['h_id']);
        $this->view->type = $this->model('hotel.typedef')->getUsableTypedefAryByHid($this->_hostel['h_id']);
        $this->view->setm = $this->model('hotel.settlem')->getUsableSettlemAryByHid($this->_hostel['h_id']);
    }

    /**
     * 创建订单操作
     */
    public function doCreateAction()
    {
        if (!$this->getRequest()->isPost())
        {
            $this->flash(0, '错误来源的表单提交');
        }

        $stacode = $this->input('sta') ?: $this->_hostel['h_order_default_stacode'];
        if ($stacode !== ORDER_STATUS_YD && $stacode !== ORDER_STATUS_BL)
        {
            $this->flash(0, '指定的订单状态码错误');
        }

        $channel = $this->loadUsableChannel($this->input('cid'));
        if (!$channel['hc_status'])
        {
            $this->flash(0, '指定的预订渠道不可用');
        }

        $typedef = $this->loadUsableTypedef($this->input('tid'));
        if (!$typedef['ht_status'])
        {
            $this->flash(0, '指定的预订类型不可用');
        }

        $saleman = $this->loadUsableSaleman($this->input('uid'));
        if (!$saleman['u_status'])
        {
            $this->flash(0, '指定的销售人员不可用');
        }

        $gbker = $this->fetchOrderGuest($this->input('gbker', 'array'), HOTEL_GUEST_TYPE_BOOK);
        if (empty($gbker))
        {
            $this->flash(0, '没有指定下单的预订客人');
        }

        $order = $this->input('order', 'array');
        if (empty($order))
        {
            $this->flash(0, '没有选择需预订的房间');
        }

        $price = $this->input('price', 'array');
        if (empty($price))
        {
            $this->flash(0, '没有指定成交房费明细');
        }

        $brice = $this->input('brice', 'array');
        if (empty($brice))
        {
            $this->flash(0, '没有指定账单房费明细');
        }

        if (($count = count($order)) > static::ORDER_LIMIT)
        {
            $this->flash(0, __('单次预订不允许超过%d个房间', static::ORDER_LIMIT));
        }

        if ($count !== count($price) || $count !== count($brice))
        {
            $this->flash(0, '订单和房费明细不匹配');
        }

        $ctime = time();
        $dtime = strtotime(date('Y-m-d', $ctime));
        $ltime = null;

        $omber = $this->input('mno');
        $omber = empty($omber) ? null : $this->loadUsableMberByUqno($omber);
        if (isset($omber['m_status']) && !$omber['m_status'])
        {
            $this->flash(0, '会员已停用，不允许新的订单与其关联');
        }

        $obill = $this->input('bid');
        $obill = empty($obill) ? null : $this->loadUsableBill($obill);
        if (isset($obill['b_status']) && $obill['b_status'] !== BILL_STATUS_KF)
        {
            $this->flash(0, '账单已关闭，不允许新的订单与其关联');
        }

        if (empty($obill))
        {
            $settlem = $this->loadUsableSettlem($this->input('sid'));
            if (!$settlem['hs_status'])
            {
                $this->flash(0, '指定的结算方式不可用');
            }

            if ($this->input('lft'))
            {
                $ldate = $this->input('ldate');
                $lhour = $this->input('lhour', 'numeric');
                $lminu = $this->input('lminu', 'numeric');

                isset($lhour[1]) OR $lhour = '0' . $lhour;
                isset($lminu[1]) OR $lminu = '0' . $lminu;

                if (!Zyon_Util::isDate($ldate) || !Zyon_Util::isTime($lhour . ':' . $lminu))
                {
                    $this->flash(0, '账单过期时间格式错误');
                }

                $ltime = strtotime($ldate . ' ' . $lhour . ':' . $lminu);
                if ($ltime < $ctime-59)
                {
                    $this->flash(0, __('账单过期时间不能早于当前时间%s', date('Y-m-d H:i', $ctime)));
                }
            }
        }

        // 以下处理订单集合
        $saved = $value_rules_conds = $other_order_conds = array();
        foreach ($order as $idx => &$val)
        {
            if (!is_array($val)
                || !isset($val['room']) || !Zyon_Util::isUnsignedInt($val['room']) || $val['room'] < 1
                || !isset($val['date']) || !isset($val['hour']) || !isset($val['minu'])
                || !is_array($val['date']) || !isset($val['date'][0]) || !isset($val['date'][1])
                || !is_array($val['hour']) || !isset($val['hour'][0]) || !isset($val['hour'][1])
                || !is_array($val['minu']) || !isset($val['minu'][0]) || !isset($val['minu'][1])
                || !is_numeric($val['hour'][0]) || !is_numeric($val['hour'][1])
                || !is_numeric($val['minu'][0]) || !is_numeric($val['minu'][1])
                || !Zyon_Util::isDate($val['date'][0]) || !Zyon_Util::isDate($val['date'][1])
                || !Zyon_Util::isDateTime($val['date'][0] . ' ' . $val['hour'][0] . ':' . $val['minu'][0])
                || !Zyon_Util::isDateTime($val['date'][1] . ' ' . $val['hour'][1] . ':' . $val['minu'][1])
                || !isset($val['lver']) || !is_array($val['lver'])
                || !isset($val['memo']) || !is_string($val['memo'])

                || !isset($price[$idx]) || !is_array($price[$idx])
                || !isset($brice[$idx]) || !is_array($brice[$idx])
            )
            {
                $this->flash(0, '参数错误');
            }

            $val['attr'] = $this->model('order')->getAttrCodeByList(
                isset($val['attr']) && is_array($val['attr']) ? $val['attr'] : array()
            );

            $val['datm'][0] = strtotime($val['date'][0]);
            $val['datm'][1] = strtotime($val['date'][1]);
            if ($val['datm'][1] < $dtime)
            {
                $this->flash(0, '订单离店时间不能在今天之前');
            }

            $val['lgth'] = ($val['datm'][1] - $val['datm'][0])/86400;
            if ($val['lgth'] < $this->_hostel['h_order_minlens'] || $val['lgth'] > $this->_hostel['h_order_maxlens'])
            {
                $this->flash(0, __('旅店仅支持创建天数介于 %d 到 %d 的订单',
                    $this->_hostel['h_order_minlens'],
                    $this->_hostel['h_order_maxlens']
                ));
            }

            $val['lver'] = $this->fetchOrderGuest($val['lver'], HOTEL_GUEST_TYPE_LIVE);

            $val['pval'] = $this->fetchOrderPrice($price[$idx], $val['date'][0], $val['date'][1]);
            $val['bval'] = $this->fetchOrderPrice($brice[$idx], $val['date'][0], $val['date'][1]);

            $val['time'][0] = strtotime($val['date'][0] . ' ' . $val['hour'][0] . ':' . $val['minu'][0]);
            $val['time'][1] = strtotime($val['date'][1] . ' ' . $val['hour'][1] . ':' . $val['minu'][1]);

            $value_rules_conds[] = sprintf('(rp_rid = %d AND rp_btime <= %d AND rp_etime >= %d)',
                $val['room'],
                $val['datm'][0] + ($val['lgth']-1)*86400,
                $val['datm'][0]
            );

            $other_order_conds[] = sprintf('(o_rid = %d AND o_bdatm <= %d AND o_edatm >= %d)',
                $val['room'],
                $val['datm'][1],
                $val['datm'][0]
            );

            $saved[$val['room']] = array();
        }
        unset($val);
        $order = Zyon_Array::group($order, 'room');

        $value_rules_conds = implode(' OR ', $value_rules_conds);
        $other_order_conds = implode(' OR ', $other_order_conds);

        $rooms = Zyon_Array::keyto($this->model('room')->getRoomAryByIds(array_keys($order)), 'r_id');
        if (empty($rooms) || count($rooms) !== count($order))
        {
            $this->flash(0, '找不到指定预订的房间');
        }

        // 以下处理每个房间的启用日、停用段以及订单最长时间限制
        foreach ($rooms as $rid => &$val)
        {
            if ($val['r_hid'] !== $this->_hostel['h_id'])
            {
                $this->flash(0, '找不到指定预订的房间');
            }

            foreach ($order[$rid] as &$v)
            {
                $btime = $v['datm'][0];
                $etime = $v['datm'][1]-1;

                if ($btime < $val['r_otime'])
                {
                    $this->flash(0, __('房间%s在%s之前尚未启用', $val['r_name'], date('Y-m-d', $val['r_otime'])));
                }

                for ($_btime = max($btime, $dtime+86400*$this->_hostel['h_order_enddays']); $_btime < $etime; $_btime+=86400)
                {
                    $this->flash(0, __('%s的房间%s超出时间限制', date('Y-m-d', $_btime), $val['r_name']));
                }

                if ($val['r_btime'])
                {
                    $_r_btime = max($btime, $val['r_btime']);
                    $_r_etime = min($etime, $val['r_etime']);

                    while ($_r_btime < $_r_etime)
                    {
                        $this->flash(0, __('%s的房间%s已停用', date('Y-m-d', $_r_btime), $val['r_name']));
                    }
                }
            }
            unset($v);
        }
        unset($val);

        // 以下处理订单集合内的冲突
        if ($stacode === ORDER_STATUS_BL)
        {
            foreach ($order as &$val)
            {
                foreach ($val as $k => &$v)
                {
                    foreach ($val as $i => &$o)
                    {
                        if ($k !== $i && $k['time'][0] < $o['time'][1] && $k['time'][1] > $o['time'][0])
                        {
                            $this->flash(0, __('房间%s的订单时间冲突，不能同时创建', $rooms[$v['room']]['r_name']));
                        }
                    }
                    unset($o);
                }
                unset($v);
            }
            unset($val);
        }

        // 以下处理与系统订单的冲突
        $other = $this->model('order')->getOrderAryByIds(
            $this->model('order')->fetchIds(array(
                'o_hid = ' . $this->_hostel['h_id'],
                'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                $other_order_conds,
            ))
        );
        if (!is_array($other))
        {
            $this->flash(0, '无法获取系统订单的信息');
        }

        if (!empty($other))
        {
            foreach ($other as &$val)
            {
                $rid = $val['o_rid'];
                foreach ($order[$rid] as &$v)
                {
                    if ($val['o_edatm'] == $v['datm'][0] && $v['time'][0] < $val['o_etime'])
                    {
                        $this->flash(0, __('%s的房间%s已被其它订单占用',
                            date('Y-m-d H:i', $v['time'][0]), $rooms[$rid]['r_name'])
                        );
                    }

                    if ($val['o_bdatm'] == $v['datm'][1] && $v['time'][1] > $val['o_btime'])
                    {
                        $this->flash(0, __('%s的房间%s已被其它订单占用',
                            date('Y-m-d H:i', $v['time'][1]), $rooms[$rid]['r_name'])
                        );
                    }

                    $_o_btime = max($v['datm'][0], $val['o_bdatm']);
                    $_o_etime = min($v['datm'][1], $val['o_edatm']);
                    while ($_o_btime < $_o_etime)
                    {
                        $this->flash(0, __('%s的房间%s已被其它订单占用',
                            date('Y-m-d', $_o_btime), $rooms[$rid]['r_name'])
                        );
                    }
                }
                unset($v);
            }
            unset($val);
        }
        unset($other);

        /**
         * 开启事务，创建订单
         */
        $this->model('order')->dbase()->beginTransaction();
        try
        {
            if (empty($obill))
            {
                if (!($bid = $this->model('bill')->addBill($this->model('bill')->getNewBill(
                    $this->_hostel['h_id'],
                    $settlem['hs_id'], $settlem['hs_name'],
                    0, 0,
                    mb_substr($gbker['o_gbker_name'], 0, 10) . '-' . date('ymdHi', $_SERVER['REQUEST_TIME']),
                    $ltime
                ))))
                {
                    throw new exception('创建账单失败');
                }

                if ($obill = $this->model('bill')->getBill($bid))
                {
                    $this->model('log.bill')->addLog(
                        $this->model('log.bill')->getNewCreateLog($this->_master, $obill)
                    );
                }
                else
                {
                    throw new exception('读取账单失败');
                }
            }

            $obval = array();
            foreach (array_keys($order) as $rid)
            {
                foreach ($order[$rid] as &$val)
                {
                    /**
                     * 检查房间状态
                     */
                    if (!$this->model('order')->chkCanCreateOrder(
                        $this->_hostel, $rooms[$rid], $val['time'][0], $val['time'][1]))
                    {
                        throw new exception('无法创建订单，房间不可用');
                    }

                    $map = $this->model('order')->getNewOrder(
                        $rooms[$rid], $val['time'][0], $val['time'][1], json_encode($val['pval']), json_encode($val['bval']), $stacode
                    );

                    if ($omber)
                    {
                        $map['o_mid'] = $omber['m_id'];
                        $map['o_mno'] = $omber['m_no'];
                    }

                    $map['o_bid']  = $obill['b_id'];
                    $map['o_sid']  = $saleman['u_id'];
                    $map['o_snm']  = $saleman['u_realname'];
                    $map['o_cid']  = $channel['hc_id'];
                    $map['o_cnm']  = $channel['hc_name'];
                    $map['o_tid']  = $typedef['ht_id'];
                    $map['o_tnm']  = $typedef['ht_name'];
                    $map['o_attr'] = $val['attr'];
                    $map['o_memo'] = $val['memo'];

                    // 加入旅客信息
                    $map = array_merge($gbker, $val['lver'], $map);

                    $oid = $this->model('order')->addOrder($map);
                    if (!$oid) throw new exception('创建订单记录失败');

                    if ($map = $this->model('order')->getOrder($oid))
                    {
                        $this->model('log.order')->addLog(
                            $this->model('log.order')->getNewCreateLog($this->_master, $map)
                        );
                    }

                    $obval[$oid] = $map['o_brice'];
                }
                unset($val);
            }

            if (!$this->model('bill')->modBill($obill['b_id'], array(
                'b_cost' => $this->model('bill')->expr('b_cost + ' . array_sum($obval)),
            )))
            {
                throw new exception('更新账单信息失败');
            }

            foreach ($obval as $idx => $val)
            {
                $obval[$idx] = __('%d号订单的账单房费 %d', $idx, $val/100);
            }

            $this->model('log.bill')->addLog(
                $this->model('log.bill')->getNewUpdateLog(
                    $this->_master, $obill, $this->model('bill')->getBill($obill['b_id']), implode(",\n", $obval)
                )
            );

            $this->model('order')->dbase()->commit();
            $this->flash(1, array(
                'forward' => "/master/bill/detail?bid={$obill['b_id']}",
            ));
        }
        catch (Exception $e)
        {
            $this->model('order')->dbase()->rollBack();
            $this->error($e);
        }

        $this->flash(0);
    }

    /**
     * 变更订单操作（换房、移动）
     */
    public function doModifyAction()
    {
        $older = $this->loadUsableOrder($this->input('oid'));
        if ($this->input('key') !== $older['o_mtime'])
        {
            $this->flash(0, '表单已过期，请刷新重试');
        }

        if ($this->input('sta', 'numeric') !== $older['o_status'])
        {
            $this->flash(0, '订单状态错误或者有冲突');
        }

        $ctime = $_SERVER['REQUEST_TIME'];
        $dtime = strtotime(date('Y-m-d', $ctime));

        if ($older['o_edatm'] <= $dtime
            || !($older['o_attr'] & (int)ORDER_ATTR_YXHF)
            || $older['o_status'] === ORDER_STATUS_YQX || $older['o_status'] === ORDER_STATUS_YJS)
        {
            $this->flash(0, '指定的订单不可以换房');
        }

        $order = $this->input('order', 'array');
        if (empty($order))
        {
            $this->flash(0, '没有选择需更换的房间');
        }

        $price = $this->input('price', 'array');
        if (empty($price))
        {
            $this->flash(0, '没有指定成交房费明细');
        }

        $brice = $this->input('brice', 'array');
        if (empty($brice))
        {
            $this->flash(0, '没有指定账单房费明细');
        }

        $osize = count($order);
        if ($osize !== count($price) || $osize !== count($brice))
        {
            $this->flash(0, '订单和房费明细不匹配');
        }

        $obval = json_decode($older['o_brices'], true);
        $opval = json_decode($older['o_prices'], true);

        $obill = $this->loadUsableBill($older['o_bid']);
        if (isset($obill['b_status']) && $obill['b_status'] !== BILL_STATUS_KF)
        {
            $this->flash(0, '账单已关闭，不允许新的订单与其关联');
        }

        $oroom = $this->loadUsableRoom($older['o_rid']);

        $ostat = $older['o_status'] == ORDER_STATUS_YD ? ORDER_STATUS_YD : ORDER_STATUS_BL;

        // 以下处理订单集合
        $newid = 0;
        $order = array_values($order);
        $other_order_conds = array();
        foreach ($order as $idx => &$val)
        {
            if (!is_array($val)
                || !isset($val['room']) || !isset($val['date']) || !isset($val['lgth'])
                || !Zyon_Util::isUnsignedInt($val['room'])
                || !Zyon_Util::isDate($val['date']) || !Zyon_Util::isUnsignedInt($val['lgth'])
                || $val['room'] < 1 || $val['lgth'] < 1

                || !isset($price[$idx]) || !is_array($price[$idx])
                || !isset($brice[$idx]) || !is_array($brice[$idx])
            )
            {
                $this->flash(0, '参数错误');
            }

            if ($val['room'] == $older['o_rid'])
            {
                $this->flash(0, '调换的房间和当前房间相同');
            }

            $val['date'] = array($val['date'], date('Y-m-d', strtotime($val['date'])+$val['lgth']*86400));
            $val['datm'] = array(strtotime($val['date'][0]), strtotime($val['date'][1]));
            if ($val['datm'][0] < $dtime)
            {
                $this->flash(0, '调房的间夜不能在今天之前');
            }

            $oldot = $val['datm'][0];
            while ($oldot < $val['datm'][1])
            {
                if (!isset($obval[$oldot]) || !isset($opval[$oldot]))
                {
                    $this->flash(0, '目标订单间夜重叠或者超出原订单范围');
                }

                unset($obval[$oldot], $opval[$oldot]);
                $oldot += 86400;
            }

            if ($val['lgth'] < $this->_hostel['h_order_minlens'] || $val['lgth'] > $this->_hostel['h_order_maxlens'])
            {
                $this->flash(0, __('旅店仅支持创建天数介于 %d 到 %d 的订单',
                    $this->_hostel['h_order_minlens'],
                    $this->_hostel['h_order_maxlens']
                ));
            }

            $val['pval'] = $this->fetchOrderPrice($price[$idx], $val['date'][0], $val['date'][1]);
            $val['bval'] = $this->fetchOrderPrice($brice[$idx], $val['date'][0], $val['date'][1]);

            $val['time'][0] = max($val['datm'][0] + $this->_hostel['h_checkin_time'], $older['o_btime']);
            $val['time'][1] = min($val['datm'][1] + $this->_hostel['h_checkout_time'], $older['o_etime']);

            $other_order_conds[] = sprintf('(o_rid = %d AND o_bdatm <= %d AND o_edatm >= %d)',
                $val['room'],
                $val['datm'][1],
                $val['datm'][0]
            );

            if ($val['datm'][0] < $order[$newid]['datm'][0])
            {
                $newid = $idx;
            }
        }
        unset($val);
        $other_order_conds = implode(' OR ', $other_order_conds);

        if (!empty($obval))
        {
            // 有剩余订单间夜，构造新的订单
            $datms = array_keys($obval);
            for ($idx = 0, $len = count($datms); $idx < $len ; $idx++)
            {
                $oitem = array(
                    'room' => $older['o_rid'],
                    'lgth' => 1,
                    'datm' => array($datms[$idx]),
                    'date' => array(),
                    'time' => array(),
                    'pval' => array($datms[$idx] => $opval[$datms[$idx]]),
                    'bval' => array($datms[$idx] => $obval[$datms[$idx]]),
                );

                while ($idx+1 < $len && $datms[$idx] + 86400 == $datms[$idx+1])
                {
                    $idx++;
                    $oitem['lgth']++;
                    $oitem['pval'][$datms[$idx]] = $opval[$datms[$idx]];
                    $oitem['bval'][$datms[$idx]] = $obval[$datms[$idx]];
                }

                $oitem['datm'][1] = $oitem['datm'][0] + $oitem['lgth']*86400;
                $oitem['date'][0] = date('Y-m-d', $oitem['datm'][0]);
                $oitem['date'][1] = date('Y-m-d', $oitem['datm'][1]);
                $oitem['time'][0] = max($oitem['datm'][0] + $this->_hostel['h_checkin_time'], $older['o_btime']);
                $oitem['time'][1] = min($oitem['datm'][1] + $this->_hostel['h_checkout_time'], $older['o_etime']);

                if ($oitem['lgth'] < $this->_hostel['h_order_minlens'])
                {
                    $this->flash(0, __('旅店仅支持创建天数介于 %d 到 %d 的订单',
                        $this->_hostel['h_order_minlens'],
                        $this->_hostel['h_order_maxlens']
                    ));
                }

                $order[$osize] = $oitem;
                if ($oitem['datm'][0] <= $order[$newid]['datm'][0])
                {
                    $newid = $osize;
                }

                ++$osize;
            }
        }

        $oitem = $order[$newid];

        $order = Zyon_Array::group($order, 'room');

        $rooms = Zyon_Array::keyto($this->model('room')->getRoomAryByIds(array_keys($order)), 'r_id');
        if (empty($rooms) || count($rooms) !== count($order))
        {
            $this->flash(0, '找不到指定预订的房间');
        }

        // 以下处理每个房间的停用段以及订单最长时间限制
        foreach ($rooms as $rid => &$val)
        {
            if ($val['r_hid'] !== $this->_hostel['h_id'])
            {
                $this->flash(0, '找不到指定预订的房间');
            }

            foreach ($order[$rid] as &$v)
            {
                $btime = $v['datm'][0];
                $etime = $v['datm'][1]-1;

                if ($btime < $val['r_otime'])
                {
                    $this->flash(0, __('房间%s在%s之前尚未启用', $val['r_name'], date('Y-m-d', $val['r_otime'])));
                }

                for ($_btime = max($btime, $dtime+86400*$this->_hostel['h_order_enddays']); $_btime < $etime; $_btime+=86400)
                {
                    $this->flash(0, __('%s的房间%s超出时间限制', date('Y-m-d', $_btime), $val['r_name']));
                }

                if ($val['r_btime'])
                {
                    $_r_btime = max($btime, $val['r_btime']);
                    $_r_etime = min($etime, $val['r_etime']);

                    while ($_r_btime < $_r_etime)
                    {
                        $this->flash(0, __('%s的房间%s已停用', date('Y-m-d', $_r_btime), $val['r_name']));
                    }
                }
            }
            unset($v);
        }
        unset($val);

        // 加入原订单房间至房间集合
        $rooms[$oroom['r_id']] = $oroom;

        // 以下处理订单集合内的冲突
        foreach ($order as &$val)
        {
            foreach ($val as $k => &$v)
            {
                foreach ($val as $i => &$o)
                {
                    if ($k !== $i && $k['time'][0] < $o['time'][1] && $k['time'][1] > $o['time'][0])
                    {
                        $this->flash(0, __('房间%s的订单时间冲突，不能同时创建', $rooms[$v['room']]['r_name']));
                    }
                }
                unset($o);
            }
            unset($v);
        }
        unset($val);

        // 以下处理与系统订单的冲突
        $other = $this->model('order')->getOrderAryByIds(
            $this->model('order')->fetchIds(array(
                'o_hid = ' . $this->_hostel['h_id'],
                'o_id <> ' . $older['o_id'],
                'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                $other_order_conds,
            ))
        );
        if (!is_array($other))
        {
            $this->flash(0, '无法获取系统订单的信息');
        }

        if (!empty($other))
        {
            foreach ($other as &$val)
            {
                $rid = $val['o_rid'];
                foreach ($order[$rid] as &$v)
                {
                    if ($val['o_edatm'] == $v['datm'][0])
                    {
                        $v['time'][0] = max($v['time'][0], $val['o_etime']);
                    }

                    if ($val['o_bdatm'] == $v['datm'][1])
                    {
                        $v['time'][1] = min($v['time'][1], $val['o_btime']);
                    }

                    $_o_btime = max($v['datm'][0], $val['o_bdatm']);
                    $_o_etime = min($v['datm'][1], $val['o_edatm']);
                    while ($_o_btime < $_o_etime)
                    {
                        $this->flash(0, __('%s的房间%s已被其它订单占用',
                            date('Y-m-d', $_o_btime), $rooms[$rid]['r_name'])
                        );
                    }
                }
                unset($v);
            }
            unset($val);
        }
        unset($other);

        // 从新订单中移除最靠近原订单入住时间的订单，以更改原订单时间实现
        $oitem = $order[$oitem['room']][$newid];
        unset($order[$oitem['room']][$newid]);
        --$osize;

        /**
         * 开启事务，创建订单
         */
        $this->model('order')->dbase()->beginTransaction();
        try
        {
            $bvsum = array_sum($oitem['bval']);
            $pvsum = array_sum($oitem['pval']);

            // 缩短或者取消原订单，更新账单房费
            if (!$this->model('order')->modOrder($older['o_id'], array(
                'o_rid'   => $oitem['room'],
                'o_room'  => $rooms[$oitem['room']]['r_name'],
                'o_bdatm' => $oitem['datm'][0],
                'o_edatm' => $oitem['datm'][1],
                'o_btime' => $oitem['time'][0],
                'o_etime' => $oitem['time'][1],
                'o_brice' => $bvsum,
                'o_price' => $pvsum,
                'o_brices' => json_encode($oitem['bval']),
                'o_prices' => json_encode($oitem['pval']),
            )))
            {
                throw new exception('更新原订单失败');
            }

            if ($bvsum != $older['o_brice'])
            {
                if (!$this->model('bill')->modBill($older['o_bid'], array(
                    'b_cost' => $this->model('bill')->expr('b_cost + ' . ($bvsum - $older['o_brice'])),
                )))
                {
                    throw new exception('更新账单信息失败');
                }

                $bill_old = $obill;
                if (!($obill = $this->model('bill')->getBill($older['o_bid'])))
                {
                    throw new exception('读取账单信息失败');
                }

                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog(
                        $this->_master, $bill_old, $obill,
                        __('%d号订单房费 %d=>%d', $older['o_id'], $older['o_brice']/100, $bvsum/100)
                    )
                );
            }

            $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateLog(
                $this->_master, $older, $older_new = $this->model('order')->getOrder($older['o_id']),
                __("房费明细\n[预计入住 %s=>%s, 间夜 %d=>%d, 成交房费 %d=>%d, 账单房费 %d=>%d]",
                date('Y-m-d H:i', $older['o_btime']), date('Y-m-d H:i', $oitem['time'][0]),
                ($older['o_edatm'] - $older['o_bdatm'])/86400,
                ($oitem['datm'][1] - $oitem['datm'][0])/86400,
                $older['o_price']/100, $pvsum/100,
                $older['o_brice']/100, $bvsum/100
            )));

            // 如果原订单在住且更换房间，则更新房间状态
            if ($older['o_rid'] != $oitem['room'])
            {
                $this->model('log.order')->addLog($this->model('log.order')->getNewChangeRoomLog(
                    $this->_master, $older, $this->model('order')->getOrder($older['o_id'])
                ));

                if ($older['o_status'] == ORDER_STATUS_ZZ)
                {
                    if ($rooms[$oitem['room']]['r_status'] != ROOM_STATUS_GJKF)
                    {
                        throw new exception('入住的目标房间状态不是干净空房');
                    }

                    if (!$this->model('room')->modRoom($older['o_rid'], array('r_status' => ROOM_STATUS_GJKF))
                        || !$this->model('room')->modRoom($oitem['room'], array('r_status' => $rooms[$older['o_rid']]['r_status'])))
                    {
                        throw new exception('更新房间状态失败');
                    }

                    $this->model('log.room')->addLog($this->model('log.room')->getNewUpdateStatusLog(
                        $this->_master, $rooms[$older['o_rid']], $this->model('room')->getRoom($older['o_rid'])
                    ));

                    $this->model('log.room')->addLog($this->model('log.room')->getNewUpdateStatusLog(
                        $this->_master, $rooms[$oitem['room']], $this->model('room')->getRoom($oitem['room'])
                    ));
                }
            }

            if ($osize > 0)
            {
                // 获取原订单中的客人有效信息
                $guest = array();
                $guest['o_gbker_name'] = (string)$older['o_gbker_name'];
                $guest['o_gbker_phone'] = (string)$older['o_gbker_phone'];
                $guest['o_gbker_idtype'] = (string)$older['o_gbker_idtype'];

                $older['o_gbker_idno'] == '' OR $guest['o_gbker_idno'] = $older['o_gbker_idno'];
                $older['o_gbker_email'] == '' OR $guest['o_gbker_email'] = $older['o_gbker_email'];

                $guest['o_glver_name'] = (string)$older['o_glver_name'];
                $guest['o_glver_phone'] = (string)$older['o_glver_phone'];
                $guest['o_glver_idtype'] = (string)$older['o_glver_idtype'];

                $older['o_glver_idno'] == '' OR $guest['o_glver_idno'] = $older['o_glver_idno'];
                $older['o_glver_email'] == '' OR $guest['o_glver_email'] = $older['o_glver_email'];

                $obval = array();
                foreach (array_keys($order) as $rid)
                {
                    foreach ($order[$rid] as &$val)
                    {
                        $map = $this->model('order')->getNewOrder(
                            $rooms[$rid],
                            $val['time'][0], $val['time'][1],
                            json_encode($val['pval']), json_encode($val['bval']),
                            $ostat
                        );

                        $map['o_bid']  = $older['o_bid'];
                        $map['o_sid']  = $older['o_sid'];
                        $map['o_snm']  = $older['o_snm'];
                        $map['o_cid']  = $older['o_cid'];
                        $map['o_cnm']  = $older['o_cnm'];
                        $map['o_tid']  = $older['o_tid'];
                        $map['o_tnm']  = $older['o_tnm'];
                        $map['o_attr'] = $older['o_attr'];
                        $map['o_memo'] = (string)$older['o_memo'];

                        // 加入旅客信息
                        $map = array_merge($guest, $map);

                        $oid = $this->model('order')->addOrder($map);
                        if (!$oid) throw new exception('创建订单记录失败');

                        if ($map = $this->model('order')->getOrder($oid))
                        {
                            $this->model('log.order')->addLog(
                                $this->model('log.order')->getNewCreateLog($this->_master, $map)
                            );
                        }

                        $obval[$oid] = $map['o_brice'];
                    }
                    unset($val);
                }

                if (!$this->model('bill')->modBill($obill['b_id'], array(
                    'b_cost' => $this->model('bill')->expr('b_cost + ' . array_sum($obval)),
                )))
                {
                    throw new exception('更新账单信息失败');
                }

                foreach ($obval as $idx => $val)
                {
                    $obval[$idx] = __('%d号订单的账单房费 %d', $idx, $val/100);
                }

                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog(
                        $this->_master, $obill, $this->model('bill')->getBill($obill['b_id']), implode(",\n", $obval)
                    )
                );
            }

            $this->model('order')->dbase()->commit();
            $this->flash(1, array(
                'forward' => "/master/bill/detail?bid={$obill['b_id']}",
            ));
        }
        catch (Exception $e)
        {
            $this->model('order')->dbase()->rollBack();
            $this->error($e);
        }

        $this->flash(0);
    }

    /**
     * 订单详情页
     */
    public function detailAction()
    {
        $order = $this->loadUsableOrder($oid = $this->input('oid'));
        $bill  = $this->loadUsableBill($bid = $order['o_bid']);
        $room  = $this->loadUsableRoom($rid = $order['o_rid']);

        $room['r_view'] = $this->model('room')->getViewListByCode($room['r_view']);

        $order['r_btime'] = $room['r_btime'];
        $order['r_etime'] = $room['r_etime'];

        $order['price'] = json_decode($order['o_prices'], true);
        $order['brice'] = json_decode($order['o_brices'], true);
        $order['state'] = $this->model('order')->getStateNameByCode($order['o_status']);
        $order['rtsta'] = $this->model('order')->getRealTimeStateNames($order, $this->_hostel);

        $action = $this->model('order')->getUsableActions($order, $this->_hostel);

        $this->view->bill    = $bill;
        $this->view->room    = $room;
        $this->view->order   = $order;
        $this->view->action  = $action;
        $this->view->states  = $this->model('order')->getStatus();

        if (($handle = $this->input('act')) && array_key_exists($handle, $action))
        {
            $this->view->handle = $handle;
        }
    }

    /**
     * 更改销售人员
     */
    public function doUpdateInfoXsryAction()
    {
        $order = $this->loadUsableOrder($oid = $this->input('oid'));
        if (in_array($order['o_status'], array(ORDER_STATUS_YQX, ORDER_STATUS_YJS), true))
        {
            $this->flash(0);
        }

        $saleman = $this->loadUsableSaleman($cid = $this->input('sid'));
        if (!$saleman['u_status'])
        {
            $this->flash(0, '指定销售人员不可用');
        }

        if ($saleman['u_id'] === $order['o_sid'])
        {
            $this->flash(0, '选择的销售人员与当前销售人员相同');
        }

        if ($this->model('order')->modOrder($order['o_id'],
            array('o_sid' => $saleman['u_id'], 'o_snm' => $saleman['u_realname'])
        ))
        {
            $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateLog(
                $this->_master, $order, $this->model('order')->getOrder($order['o_id']),
                __('销售人员 %s=>%s', $order['o_snm'], $saleman['u_realname'])
            ));

            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 更改预订渠道
     */
    public function doUpdateInfoYdqdAction()
    {
        $order = $this->loadUsableOrder($oid = $this->input('oid'));
        if (in_array($order['o_status'], array(ORDER_STATUS_YQX, ORDER_STATUS_YJS), true))
        {
            $this->flash(0);
        }

        $channel = $this->loadUsableChannel($cid = $this->input('cid'));
        if (!$channel['hc_status'])
        {
            $this->flash(0, '指定预订渠道不可用');
        }

        if ($channel['hc_id'] === $order['o_cid'])
        {
            $this->flash(0, '选择的预订渠道与当前预订渠道相同');
        }

        if ($this->model('order')->modOrder($order['o_id'],
            array('o_cid' => $channel['hc_id'], 'o_cnm' => $channel['hc_name'])
        ))
        {
            $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateLog(
                $this->_master, $order, $this->model('order')->getOrder($order['o_id']),
                __('预订渠道 %s=>%s', $order['o_cnm'], $channel['hc_name'])
            ));

            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 更改预订类型
     */
    public function doUpdateInfoYdlxAction()
    {
        $order = $this->loadUsableOrder($oid = $this->input('oid'));
        if (in_array($order['o_status'], array(ORDER_STATUS_YQX, ORDER_STATUS_YJS), true))
        {
            $this->flash(0);
        }

        $typedef = $this->loadUsableTypedef($tid = $this->input('tid'));
        if (!$typedef['ht_status'])
        {
            $this->flash(0, '指定预订类型不可用');
        }

        if ($typedef['ht_id'] === $order['o_tid'])
        {
            $this->flash(0, '选择的预订类型与当前预订类型相同');
        }

        if ($this->model('order')->modOrder($order['o_id'],
            array('o_tid' => $typedef['ht_id'], 'o_tnm' => $typedef['ht_name'])
        ))
        {
            $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateLog(
                $this->_master, $order, $this->model('order')->getOrder($order['o_id']),
                __('预订类型 %s=>%s', $order['o_tnm'], $typedef['ht_name'])
            ));

            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 更改关联会员
     */
    public function doUpdateInfoGlhyAction()
    {
        $order = $this->loadUsableOrder($oid = $this->input('oid'));
        if (in_array($order['o_status'], array(ORDER_STATUS_YQX, ORDER_STATUS_YJS), true))
        {
            $this->flash(0);
        }

        $omber = $guest = array();
        if (($mno = $this->input('mno')) !== '')
        {
            $omber = $this->loadUsableMberByUqno($mno);
            if (!$omber['m_status'])
            {
                $this->flash(0, '指定旅店会员不可用');
            }

            if ($omber['m_id'] === $order['o_mid'])
            {
                $this->flash(0, '选择的会员与当前会员相同');
            }

            if ($this->input('gbk'))
            {
                $guest['o_gbker_name']   = $omber['m_name'];
                $guest['o_gbker_idno']   = $omber['m_idno'];
                $guest['o_gbker_email']  = $omber['m_email'];
                $guest['o_gbker_phone']  = $omber['m_phone'];
                $guest['o_gbker_idtype'] = $omber['m_idtype'];
            }

            if ($this->input('glv'))
            {
                $guest['o_glver_name']   = $omber['m_name'];
                $guest['o_glver_idno']   = $omber['m_idno'];
                $guest['o_glver_email']  = $omber['m_email'];
                $guest['o_glver_phone']  = $omber['m_phone'];
                $guest['o_glver_idtype'] = $omber['m_idtype'];
            }
        }
        else
        {
            $omber = array('m_id' => '0', 'm_no' => '');
        }

        if ($this->model('order')->modOrder($order['o_id'],
            array_merge($guest, array('o_mid' => $omber['m_id'], 'o_mno' => $omber['m_no']))
        ))
        {
            if ($new_order = $this->model('order')->getOrder($order['o_id']))
            {
                $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateLog(
                    $this->_master, $order, $new_order,
                    __('关联会员 %s=>%s', $order['o_mno'], $omber['m_no'])
                ));

                if ($guest)
                {
                    $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateGuestLog(
                        $this->_master, $order, $new_order 
                    ));
                }
            }

            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 更新订单
     */
    public function updateAction()
    {
        $this->_forward('detail');
    }

    public function doUpdateAction()
    {
        $order = $this->loadUsableOrder($oid = $this->input('oid'));
        if ($this->input('key') !== $order['o_mtime'])
        {
            $this->flash(0, '表单已过期，请刷新重试');
        }

        if ($this->input('sta', 'numeric') !== $order['o_status'])
        {
            $this->flash(0, '订单状态错误或者有冲突');
        }

        if ($order['o_status'] === ORDER_STATUS_YQX || $order['o_status'] === ORDER_STATUS_YJS)
        {
            $this->flash(0, '订单已不允许修改');
        }
        elseif ($order['o_status'] === ORDER_STATUS_YD || $order['o_status'] === ORDER_STATUS_BL)
        {
            $this->doUpdateYD($order);
        }
        else if ($order['o_status'] === ORDER_STATUS_ZZ)
        {
            $this->doUpdateZZ($order);
        }

        $this->flash(0);
    }

    /**
     * 更新预订状态的订单
     */
    public function doUpdateYD($order)
    {
        switch ($this->input('tab'))
        {
        case 'hfsx':
            if (!$this->hasUserPerm('/master/order/do-update-attr-yxhf'))
            {
                $this->flash(0, '没有更改该属性的权限');
            }

            if ($this->model('order')->modOrder($order['o_id'],
                array('o_attr' => $this->model('order')->expr('o_attr ^ ' . ORDER_ATTR_YXHF))
            ))
            {
                $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateLog(
                    $this->_master, $order, $this->model('order')->getOrder($order['o_id']),
                    __('更改订单%s属性', getOrderAttrNameByCode(ORDER_ATTR_YXHF))
                ));

                $this->flash(1);
            }

            break;
        case 'jbxx':
            if ($order['o_memo'] === $this->input('memo'))
            {
                $this->flash(1);
            }

            if ($this->model('order')->modOrder($order['o_id'], array(
                'o_memo'  => $this->input('memo'),
            )))
            {
                $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateLog(
                    $this->_master, $order, $this->model('order')->getOrder($order['o_id']), __('基本信息')
                ));

                $this->flash(1);
            }
            
            break;
        case 'ffmx':
            if (!$this->hasUserPerm('/master/order/do-update-info-fxmx'))
            {
                $this->flash(0, '没有权限');
            }

            $bdate = $this->input('bdate');
            $bhour = $this->input('bhour', 'numeric');
            $bminu = $this->input('bminu', 'numeric');

            isset($bhour[1]) OR $bhour = '0' . $bhour;
            isset($bminu[1]) OR $bminu = '0' . $bminu;

            $edate = $this->input('edate');
            $ehour = $this->input('ehour', 'numeric');
            $eminu = $this->input('eminu', 'numeric');

            isset($ehour[1]) OR $ehour = '0' . $ehour;
            isset($eminu[1]) OR $eminu = '0' . $eminu;

            if (!Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate)
                || !Zyon_Util::isTime($bhour . ':' . $bminu)
                || !Zyon_Util::isTime($ehour . ':' . $eminu)
            )
            {
                $this->flash(0, '预订时间格式错误');
            }

            $bdatm = strtotime($bdate);
            $edatm = strtotime($edate);

            $btime = $bdatm + Zyon_Util::timeToSecs($bhour . ':' . $bminu);
            $etime = $edatm + Zyon_Util::timeToSecs($ehour . ':' . $eminu);

            if ($edatm <= $bdatm)
            {
                $this->flash(0, '离店日期必须晚于入住日期');
            }

            if ($edatm < strtotime(date('Y-m-d', $order['o_ctime'])))
            {
                $this->flash(0, '离店日期不能在订单创建日期之前');
            }

            if (($edatm-$bdatm)/86400 < $this->_hostel['h_order_minlens']
                || ($edatm-$bdatm)/86400 > $this->_hostel['h_order_maxlens'])
            {
                $this->flash(0, __('旅店仅支持天数介于 %d 到 %d 的订单',
                    $this->_hostel['h_order_minlens'],
                    $this->_hostel['h_order_maxlens']
                ));
            }

            for ($_btime = max($bdatm, strtotime(date('Y-m-d'))+86400*$this->_hostel['h_order_enddays']); $_btime < $etime; $_btime+=86400)
            {
                $this->flash(0, __('%s 超出旅店允许建立最多%d天内的订单规则',
                    date('Y-m-d', $_btime), $this->_hostel['h_order_enddays']
                ));
            }

            $oroom = $this->loadUsableRoom($order['o_rid']);

            $flash = array();
            if ($etime > $order['o_etime'] || $btime < $order['o_btime'])
            {
                if ($oroom['r_btime'])
                {
                    $_r_btime = max($bdatm, $oroom['r_btime']);
                    $_r_etime = min($edatm-1, $oroom['r_etime']);

                    while ($_r_btime < $_r_etime)
                    {
                        $flash[] = __('%s 该房间已被停用', date('Y-m-d', $_r_btime));
                        $_r_btime += 86400;
                    }
                }

                $other = Zyon_Array::keyto($this->model('order')->getOrderAryByIds($this->model('order')->fetchIds(
                    array(
                        'o_id <> ' . (int)$order['o_id'],
                        'o_hid = ' . (int)$order['o_hid'],
                        'o_rid = ' . (int)$order['o_rid'],
                        'o_status <> ' . (int)ORDER_STATUS_YQX,
                        'o_status <> ' . (int)ORDER_STATUS_YD,
                        'o_bdatm <= ' . (int)$edatm,
                        'o_edatm >= ' . (int)$bdatm,
                    ))
                ), 'o_id');

                if (!empty($other))
                {
                    foreach ($other as $other)
                    {
                        if ($other['o_edatm'] == $bdatm && $other['o_etime'] > $btime)
                        {
                            $flash[] = __('%s 该房间已被其它订单占用', date('Y-m-d H:i', $btime));
                        }

                        if ($other['o_bdatm'] == $edatm && $other['o_btime'] < $etime)
                        {
                            $flash[] = __('%s 该房间已被其它订单占用', date('Y-m-d H:i', $etime));
                        }

                        $_o_btime = max($bdatm, $other['o_bdatm']);
                        $_o_etime = min($edatm, $other['o_edatm']);

                        while ($_o_btime < $_o_etime)
                        {
                            $flash[] = __('%s 该房间已被其它订单占用', date('Y-m-d', $_o_btime));
                            $_o_btime += 86400;
                        }
                    }
                    unset($other);
                }
            }

            if (!empty($flash))
            {
                $this->flash(0, array('content' => $flash));
            }

            $price_array = $this->fetchOrderPrice($this->input('price', 'array'), $bdate, $edate);
            $brice_array = $this->fetchOrderPrice($this->input('brice', 'array'), $bdate, $edate);

            if (!$this->model('order')->chkCanUpdateOrder($this->_hostel, $oroom, $order, $btime, $etime))
            {
                $this->flash(0, '无法更新订单至指定时间段');
            }

            /**
             * 开启事务，更新订单
             */
            $this->model('order')->dbase()->beginTransaction();
            try
            {
                $price = array_sum($price_array);
                $brice = array_sum($brice_array);

                if ((int)$order['o_brice'] !== $brice)
                {
                    $bill_old = $this->loadUsableBill($order['o_bid']);
                    if (!$this->model('bill')->modBill($order['o_bid'], array(
                        'b_cost' => $this->model('bill')->expr('b_cost + ' . ($brice - $order['o_brice'])),
                    )))
                    {
                        throw new exception('更新账单信息失败');
                    }

                    if ($bill = $this->model('bill')->getBill($order['o_bid']))
                    {
                        $this->model('log.bill')->addLog(
                            $this->model('log.bill')->getNewUpdateLog(
                                $this->_master, $bill_old, $bill,
                                __('%d号订单房费 %d=>%d', $order['o_id'], $order['o_brice']/100, $brice/100)
                            )
                        );
                    }
                }

                if (!$this->model('order')->modOrder($order['o_id'], array(
                    'o_btime'  => $btime,
                    'o_etime'  => $etime,
                    'o_price'  => $price,
                    'o_brice'  => $brice,
                    'o_prices' => json_encode($price_array),
                    'o_brices' => json_encode($brice_array),
                )))
                {
                    throw new exception('更新房费明细失败');
                }

                $this->model('order')->dbase()->commit();

                $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateLog(
                    $this->_master, $order, $this->model('order')->getOrder($order['o_id']),
                    __("房费明细\n[预计入住 %s=>%s, 间夜 %d=>%d, 成交房费 %d=>%d, 账单房费 %d=>%d]",
                    date('Y-m-d H:i', $order['o_btime']), date('Y-m-d H:i', $btime),
                    ($order['o_edatm'] - $order['o_bdatm'])/86400,
                    ($edatm - $bdatm)/86400,
                    $order['o_price']/100, $price/100,
                    $order['o_brice']/100, $brice/100
                )));
            }
            catch (Exception $e)
            {
                $this->model('order')->dbase()->rollBack();
                $this->error($e);
                $this->flash(0);
            }

            $this->flash(1);

            break;
        case 'krxx':
            $gbker = $this->fetchOrderGuest($this->input('gbker', 'array'), HOTEL_GUEST_TYPE_BOOK);
            $glver = $this->fetchOrderGuest($this->input('glver', 'array'), HOTEL_GUEST_TYPE_LIVE);

            if ($this->model('order')->modOrder($order['o_id'], array_merge($gbker, $glver)))
            {
                $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateGuestLog(
                    $this->_master, $order, $this->model('order')->getOrder($order['o_id'])
                ));

                $this->flash(1);
            }

            break;
        default :
            $this->flash(0);
            break;
        }
    }

    /**
     * 在住状态的订单更新
     */
    public function doUpdateZZ($order)
    {
        switch ($this->input('tab'))
        {
        case 'hfsx':
            $this->doUpdateYD($order);

            break;
        case 'jbxx':
            $this->doUpdateYD($order);

            break;
        case 'ffmx':
            if (!$this->hasUserPerm('/master/order/do-update-info-fxmx'))
            {
                $this->flash(0, '没有权限');
            }

            $bdate = $this->input('bdate');
            if (!Zyon_Util::isDate($bdate))
            {
                $this->flash(0, '预订日期格式错误');
            }

            if (strtotime($bdate) > strtotime(date('Y-m-d')))
            {
                $this->flash(0, '入住时间不能在今天之后');
            }

            $this->doUpdateYD($order);

            break;
        case 'krxx':
            $this->doUpdateYD($order);

            break;
        default:
            $this->flash(0);
            break;
        }
    }

    /**
     * 订单操作句柄 
     */
    public function handleAction()
    {
        $order = $this->loadUsableOrder($this->input('oid'));

        $act = $this->input('act');
        if (!array_key_exists($act, $acts = $this->model('order')->getUsableActions($order, $this->_hostel)))
        {
            $this->flash(0, '订单当前不允许执行指定操作');
        }
        $this->view->action = $act;

        $func = 'handle' . strtoupper($act);
        $this->$func($order);
    }

    /**
     * 保留订单界面
     */
    public function handleBLDD($order)
    {
        $this->_redirect(sprintf('/master/order/detail?oid=%s&act=%s', $order['o_id'], ORDER_ACTION_BLDD));
    }

    /**
     * 办理入住操作界面
     */
    public function handleBLRZ($order)
    {
        $room = $this->loadUsableRoom($order['o_rid']);
        if ($room['r_status'] !== ROOM_STATUS_GJKF)
        {
            $this->flash(0, array(
                'forward' => null,
                'message' => '当前房间不是空干净房，不能办理入住',
            ));
        }

        $this->_redirect(sprintf('/master/order/detail?oid=%s&act=%s', $order['o_id'], ORDER_ACTION_BLRZ));
    }

    /**
     * 办理退房界面
     */
    public function handleBLTF($order)
    {
        $this->_redirect(sprintf('/master/order/detail?oid=%s&act=%s', $order['o_id'], ORDER_ACTION_BLTF));
    }

    /**
     * 取消订单界面
     */
    public function handleQXDD($order)
    {
        $this->_redirect(sprintf('/master/order/detail?oid=%s&act=%s', $order['o_id'], ORDER_ACTION_QXDD));
    }

    /**
     * 办理换房界面
     */
    public function handleBLHF($order)
    {
        $this->_redirect(sprintf('/master/order/detail?oid=%s&act=%s', $order['o_id'], ORDER_ACTION_BLHF));
    }

    /**
     * 执行订单操作
     */
    public function doActionAction()
    {
        $order = $this->loadUsableOrder($this->input('oid'));
        if ($this->input('key') !== $order['o_mtime'])
        {
            $this->flash(0, '表单已过期，请刷新重试');
        }

        if ($this->input('sta', 'numeric') !== $order['o_status'])
        {
            $this->flash(0, '订单状态错误或者有冲突');
        }

        $act   = $this->input('act');
        if (!array_key_exists($act, $acts = $this->model('order')->getUsableActions($order, $this->_hostel)))
        {
            $this->flash(0, '订单当前不允许执行指定操作');
        }

        $func = 'doAction' . strtoupper($act);
        method_exists($this, $func) AND $this->$func($order);
        $this->flash(0);
    }

    /**
     * 保留订单操作
     */
    public function doActionBLDD($order)
    {
        /**
         * 处理订单备注
         */
        $memo = $this->input('memo');
        $memo = $memo == '' ? $order['o_memo'] : ($memo . "\r\n" . $order['o_memo']);

        if ($this->model('order')->modOrder($order['o_id'], array(
            'o_memo'   => $memo,
            'o_status' => ORDER_STATUS_BL
        )))
        {
            if ($order_new = $this->model('order')->getOrder($order['o_id']))
            {
                $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateStatusLog(
                    $this->_master, $order, $order_new
                ));
            }

            $this->flash(1, array('forward' => "/master/order/detail?oid={$order['o_id']}"));
        }
    }

    /**
     * 取消订单操作
     */
    public function doActionQXDD($order)
    {
        /**
         * 处理订单备注
         */
        $memo = $this->input('memo');
        $memo = $memo == '' ? $order['o_memo'] : ($memo . "\r\n" . $order['o_memo']);

        $this->model('order')->dbase()->beginTransaction();
        try
        {
            if (!$this->model('order')->modOrder($order['o_id'], array(
                // 取消的订单，其房费归零
                'o_brice' => 0,
                'o_price' => 0,
                'o_memo'  => $memo,
                'o_status' => ORDER_STATUS_YQX,
            )))
            {
                throw new exception('更新订单信息失败');
            }

            $bill = $this->loadUsableBill($order['o_bid']);
            if (!$this->model('bill')->modBill($bill['b_id'], array(
                'b_cost' => $this->model('bill')->expr('b_cost - ' . $order['o_brice']),
            )))
            {
                throw new exception('更新账单信息失败');
            }

            $this->model('order')->dbase()->commit();

            if ($order_new = $this->model('order')->getOrder($order['o_id']))
            {
                $log = $this->model('log.order')->getNewUpdateStatusLog(
                    $this->_master, $order, $order_new
                );
                $log['lo_memo'] = $log['lo_memo'] . ' ' . $this->input('memo');

                $this->model('log.order')->addLog($log);
            }

            if ($bill_new = $this->model('bill')->getBill($bill['b_id']))
            {
                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog(
                        $this->_master, $bill, $bill_new,
                        __('%d号订单房费 %d=>%d', $order['o_id'], $order['o_brice']/100, 0)
                    )
                );
            }

            $this->flash(1, array('forward' => "/master/order/detail?oid={$order['o_id']}"));
        }
        catch (Exception $e)
        {
            $this->model('order')->dbase()->rollBack();
            $this->error($e);
        }
    }

    /**
     * 办理入住操作
     */
    public function doActionBLRZ($order)
    {
        $room = $this->loadUsableRoom($order['o_rid']);
        if ($room['r_status'] !== ROOM_STATUS_GJKF)
        {
            $this->flash(0, '当前房间不是空干净房，不能办理入住');
        }

        /**
         * 处理订单备注
         */
        $memo = $this->input('memo');
        $memo = $memo == '' ? $order['o_memo'] : ($memo . "\r\n" . $order['o_memo']);

        $this->model('order')->dbase()->beginTransaction();
        try
        {
            $ret = $this->model('order')->modOrder($order['o_id'], array(
                'o_memo' => $memo,
                'o_status' => ORDER_STATUS_ZZ,
            ));
            if (!$ret) throw new exception('更新订单状态失败');

            $ret = $this->model('room')->modRoom($room['r_id'], array('r_status' => ROOM_STATUS_RZZ));
            if (!$ret) throw new exception('更新房间状态失败');

            $this->model('order')->dbase()->commit();

            if ($order_new = $this->model('order')->getOrder($order['o_id']))
            {
                $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateStatusLog(
                    $this->_master, $order, $order_new 
                ));
            }

            if ($room_new = $this->model('room')->getRoom($room['r_id']))
            {
                $this->model('log.room')->addLog($this->model('log.room')->getNewUpdateStatusLog(
                    $this->_master, $room, $room_new
                ));
            }

            $this->flash(1, array('forward' => "/master/order/detail?oid={$order['o_id']}"));
        }
        catch (Exception $e)
        {
            $this->model('order')->dbase()->rollBack();
            $this->error($e);
        }
    }

    /**
     * 办理退房结算操作
     */
    public function doActionBLTF($order)
    {
        $room = $this->loadUsableRoom($order['o_rid']);

        /**
         * 处理订单备注
         */
        $memo = $this->input('memo');
        $memo = $memo == '' ? $order['o_memo'] : ($memo . "\r\n" . $order['o_memo']);

        $this->model('order')->dbase()->beginTransaction();
        try
        {
            $ret = $this->model('order')->modOrder($order['o_id'], array(
                'o_memo' => $memo,
                'o_status' => ORDER_STATUS_YJS,
            ));
            if (!$ret) throw new exception('更新订单信息失败');

            $ret = $this->model('room')->modRoom($room['r_id'], array('r_status' => ROOM_STATUS_GJKF));
            if (!$ret) throw new exception('更新房间状态失败');

            $this->model('order')->dbase()->commit();

            if ($order_new = $this->model('order')->getOrder($order['o_id']))
            {
                $this->model('log.order')->addLog($this->model('log.order')->getNewUpdateStatusLog(
                    $this->_master, $order, $order_new
                ));
            }

            if ($room_new = $this->model('room')->getRoom($room['r_id']))
            {
                $this->model('log.room')->addLog($this->model('log.room')->getNewUpdateStatusLog(
                    $this->_master, $room, $room_new
                ));
            }

            $this->flash(1, array('forward' => "/master/order/detail?oid={$order['o_id']}"));
        }
        catch (Exception $e)
        {
            $this->model('order')->dbase()->rollBack();
            $this->error($e);
        }
    }

    /**
     * 办理换房
     */
    public function doActionBLHF($order)
    {
    }

    /**
     * fetchOrderGuest
     * 
     * @param array $array
     * @param mixed $gtype
     * @return array
     */
    public function fetchOrderGuest($array, $gtype)
    {
        if (empty($array) || !is_array($array)
            || !isset($array['name']) || !is_string($array['name']) || ($array['name'] = trim($array['name'])) == ''
            || !isset($array['call']))
        {
            $this->flash(0, '客人信息缺失');
        }

        $gtype = $gtype === HOTEL_GUEST_TYPE_LIVE ? 'lver' : 'bker';
        $guest = array(
            "o_g{$gtype}_name" => $array['name'],
            "o_g{$gtype}_phone" => $array['call'],
        );

        if (isset($array['mail']))
        {
            if (!is_string($array['mail'])
                || ((($array['mail'] = trim($array['mail'])) !== '') && !Zyon_Util::isEmail($array['mail'])))
            {
                $this->flash(0, '客人邮箱错误');
            }

            $guest["o_g{$gtype}_email"] = $array['mail'];
        }

        if (isset($array['idtype']))
        {
            if (!$this->model('user')->isIdType($array['idtype']))
            {
                $this->flash(0, '证件类型错误');
            }

            $guest["o_g{$gtype}_idtype"] = $array['idtype'];
        }

        isset($array['idno']) AND $guest["o_g{$gtype}_idno"] = $array['idno'];
        isset($array['gender']) AND $guest["o_g{$gtype}_gender"] = $array['gender'];

        if (!$this->model('order')->verify($guest))
        {
            $this->flash(0, '客人信息错误');
        }

        return $guest;
    }

    /**
     * fetchOrderPrice
     * 
     * @param mixed $value
     * @param mixed $bdate
     * @param mixed $edate
     * @return array
     */
    public function fetchOrderPrice($value, $bdate, $edate)
    {
        if (!Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            $this->flash(0, '订单日期错误');
        }

        if (empty($value) || !is_array($value))
        {
            $this->flash(0, '房费参数错误');
        }

        $btime = strtotime($bdate);
        $etime = strtotime($edate)-86400;

        $count = 0;
        while ($btime <= $etime)
        {
            if (!isset($value[$btime]))
            {
                $this->flash(0, date('Y-m-d', $btime) . '房费未定义');
            }

            if (!Zyon_Util::isMoneyFloat($value[$btime]))
            {
                $this->flash(0, date('Y-m-d', $btime) . '房费参数错误');
            }

            $value[$btime] = $value[$btime]*100;
            if ($value[$btime] < 0)
            {
                $this->flash(0, date('Y-m-d', $btime) . '房费值太小，必须至少大于0');
            }

            if ($value[$btime] >= 10000000)
            {
                $this->flash(0, date('Y-m-d', $btime) . '房费值太大，必须小于100000');
            }

            $count += 1;
            $btime += 86400;
        }

        if ($count !== count($value))
        {
            $this->flash(0, '房费参数错误');
        }

        return $value;
    }
}
// End of file : OrderController.php
