<?php
/**
 * @version    $Id$
 */
class Hostel_OrderController extends HostelController
{
    /**
     * 单次预订最大房间数（向七天学习）
     */
    const ORDER_LIMIT = 3;

    /**
     * 预订订单
     */
    public function indexAction()
    {
        if (!$this->getRequest()->isPost())
        {
            $this->flash(0, '错误来源的表单提交');
        }

        $this->checkHotelStat($hotel = $this->loadUsableHotel($this->input('hid')));
        if (!(SYSTEM_GROUPS_GSER & (int)$hotel['h_order_enabled']))
        {
            $this->flash(0, '旅店尚未开放该功能');
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

        $otime = $saved = $price_rules_conds = $other_order_conds = array();
        foreach ($order as $rid => &$val)
        {
            $saved[$rid] = array();
            $otime[$rid] = array($hotel['h_checkin_time'], $hotel['h_checkout_time']);

            if (!Zyon_Util::isUnsignedInt($rid)
                || !is_array($val)
                || !isset($val['date']) || !isset($val['lgth'])
                || !Zyon_Util::isDate($val['date']) || !Zyon_Util::isUnsignedInt($val['lgth']) || $val['lgth'] < 1
            )
            {
                $this->flash(0, '参数错误');
            }

            if ($val['lgth'] < $hotel['h_order_minlens'] || $val['lgth'] > $hotel['h_order_maxlens'])
            {
                $this->flash(0, __('旅店仅支持创建天数介于 %d 到 %d 的订单',
                    $hotel['h_order_minlens'],
                    $hotel['h_order_maxlens']
                ));
            }

            $val['kept'] = false;
            $val['info'] = '';
            $val['datm'] = strtotime($val['date']);

            if ($val['datm'] < $dtime)
            {
                if ($ctime - $dtime <= 1800 && $dtime - $val['datm'] <= 86400)
                {
                    $saved[$rid][$val['datm']] = 1;
                }
                else
                {
                    $this->flash(0, '不支持创建今天之前的订单');
                }
            }

            $price_rules_conds[] = sprintf('(rp_rid = %d AND rp_btime <= %d AND rp_etime >= %d)',
                $rid,
                $val['datm'] + ($val['lgth']-1)*86400,
                $val['datm']
            );

            $other_order_conds[] = sprintf('(o_rid = %d AND o_bdatm <= %d AND o_edatm >= %d)',
                $rid,
                $val['datm'] + $val['lgth']*86400,
                $val['datm']
            );
        }
        unset($val);
        $price_rules_conds = implode(' OR ', $price_rules_conds);
        $other_order_conds = implode(' OR ', $other_order_conds);

        $rooms = Zyon_Array::keyto($this->model('room')->getRoomAryByIds(array_keys($order)), 'r_id');
        if (empty($rooms) || count($rooms) !== $count)
        {
            $this->flash(0, '找不到指定预订的房间');
        }

        // 以下处理每个房间的启用日、停用段以及订单最长时间限制
        foreach ($rooms as $rid => &$val)
        {
            if ($val['r_hid'] !== $hotel['h_id'])
            {
                $this->flash(0, '找不到指定预订的房间');
            }

            if ($val['r_attr'] & ROOM_ATTR_YCFT)
            {
                $this->flash(0, __('指定的房间 %s 不可预订', $val['r_name']));
            }

            $btime = $order[$rid]['datm'];
            $etime = $btime + $order[$rid]['lgth']*86400-1;

            for ($_btime = max($btime, $dtime+86400*$hotel['h_order_enddays']); $_btime < $etime; $_btime+=86400)
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
        unset($val);

        // 以下检测旅店预订订单是否超出限制
        if ($hotel['h_order_default_stacode'] == ORDER_STATUS_YD)
        {
            $oldes = $this->model('order')->calNumAryByRidAryAndSta(
                array_keys($order), ORDER_STATUS_YD, $ctime - getSysLimit('BOOKING_LIVE')
            );

            if (!empty($oldes))
            {
                if ($count + array_sum($oldes) > getSysLimit('BOOKING_QNTY'))
                {
                    $this->flash(0, '旅店预订订单数量已满');
                }

                $per = getSysLimit('BOOKING_PERN');
                foreach ($oldes as $rid => $num)
                {
                    if (array_key_exists($rid, $order) && $num > $per - 1)
                    {
                        $order[$rid]['kept'] = true;
                        $order[$rid]['info'] = '房间预订订单数量已满';
                    }
                }
            }
        }

        // 以下处理每个订单的冲突
        $other = $this->model('order')->getOrderAryByIds(
            $this->model('order')->fetchIds(array(
                'o_hid = ' . $hotel['h_id'],
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

                if ($val['o_edatm'] == $order[$rid]['datm'])
                {
                    $otime[$rid][0] = max($otime[$rid][0], $val['o_etime'] - $val['o_edatm']);
                }

                if ($val['o_bdatm'] == $order[$rid]['datm'] + $order[$rid]['lgth']*86400)
                {
                    $otime[$rid][1] = min($otime[$rid][1], $val['o_btime'] - $val['o_bdatm']);
                }

                $_o_btime = max($order[$rid]['datm'], $val['o_bdatm']);
                $_o_etime = min($order[$rid]['datm'] + $order[$rid]['lgth']*86400, $val['o_edatm']);

                while ($_o_btime < $_o_etime)
                {
                    $saved[$rid][$_o_btime] = 1;
                    $_o_btime += 86400;
                }
            }
            unset($val);
        }
        unset($other);

        // 以下处理每个订单的房费
        $basic = $this->model('room.price')->getBasicPriceByRids(array_keys($rooms));
        if (!$basic || count($basic) !== $count)
        {
            $this->flash(0, '无法获取预订房间的房价');
        }

        $rules = Zyon_Array::group($this->model('room.price')->getPriceAryByIds(
            $this->model('room.price')->fetchIds(array(
                'rp_hid = ' . $hotel['h_id'],
                $price_rules_conds,
            ))
        ), 'rp_rid');

        $price = array();
        foreach (array_keys($basic) as $rid)
        {
            $price[$rid] = $this->model('room.price')->getPriceDotAry(
                $basic[$rid],
                $order[$rid]['datm'], $order[$rid]['datm'] + $order[$rid]['lgth']*86400-1,
                isset($rules[$rid]) ? $rules[$rid] : array()
            );
        }
        unset($basic, $rules);

        $this->view->ctime = $ctime;
        $this->view->hotel = $hotel;
        $this->view->rooms = $rooms;
        $this->view->order = $order;
        $this->view->otime = $otime;
        $this->view->price = $price;
        $this->view->saved = $saved;
    }

    /**
     * 创建订单
     */
    public function doCreateAction()
    {
        if (!$this->getRequest()->isPost())
        {
            $this->flash(0, '错误来源的表单提交');
        }

        $captcha = new Geek_Captcha_Image('/hostel/order/do-create');
        if (!$captcha->isValid($this->input('captcha')))
        {
            $this->flash(0, '请填写正确的验证码');
        }

        if (!Zyon_Util::isUnsignedInt($otime = $this->input('ctime'))
            || !is_array($order = $this->input('order', 'array')) || empty($order)
            || !is_array($orent = $this->input('price', 'array')) || empty($orent)
            || !is_array($gbker = $this->input('cuser', 'array')) || empty($gbker)
            || !is_array($glves = $this->input('guest', 'array')) || empty($glves)
            || ($count = count($order)) !== count($orent)
            || $count !== count($glves)
            || !isset($gbker['name']) || !isset($gbker['call']) || !isset($gbker['mail'])
        )
        {
            $this->flash(0, '提交订单的参数错误');
        }

        $gbker = array(
            'o_gbker_name'  => $gbker['name'],
            'o_gbker_email' => $gbker['mail'],
            'o_gbker_phone' => $gbker['call'],
        );

        if (!$this->model('order')->verify($gbker))
        {
            $this->flash(0, '预订客人信息参数错误');
        }

        $ctime = time();
        $dtime = strtotime(date('Y-m-d', $ctime));
        if ($ctime - $otime < 10 || $ctime - $otime > 1800 || $dtime !== strtotime(date('Y-m-d', $otime)))
        {
            $this->flash(0, '订单已失效，请刷新重试');
        }

        $this->checkHotelStat($hotel = $this->loadUsableHotel($hid = $this->input('hid')));
        if (!(SYSTEM_GROUPS_GSER & (int)$hotel['h_order_enabled']))
        {
            $this->flash(0, '旅店尚未开放该功能');
        }

        if ($count > static::ORDER_LIMIT)
        {
            $this->flash(0, __('单次预订不允许超过%d个房间', static::ORDER_LIMIT));
        }

        $btmad = $hotel['h_checkin_time'];
        $etmad = $hotel['h_checkout_time'];

        $price_rules_conds = $other_order_conds = array();
        foreach ($order as $rid => &$val)
        {
            if (!Zyon_Util::isUnsignedInt($rid)
                || !is_array($val)
                || !isset($val['date']) || !isset($val['lgth'])
                || !Zyon_Util::isDate($val['date']) || !Zyon_Util::isUnsignedInt($val['lgth']) || $val['lgth'] < 1
                || !is_array($val['time']) || !isset($val['time'][0]) || !isset($val['time'][1])
                || !Zyon_Util::isUnsignedInt($val['time'][0]) || !Zyon_Util::isUnsignedInt($val['time'][1])
                || $val['time'][0] < $btmad || $val['time'][0] > 86399
                || $val['time'][1] > $etmad || $val['time'][0] < 1
            )
            {
                $this->flash(0, '参数错误');
            }

            if ($val['lgth'] < $hotel['h_order_minlens'] || $val['lgth'] > $hotel['h_order_maxlens'])
            {
                $this->flash(0, __('旅店仅支持创建天数介于 %d 到 %d 的订单',
                    $hotel['h_order_minlens'],
                    $hotel['h_order_maxlens']
                ));
            }

            $val['datm'] = strtotime($val['date']);
            if ($val['datm'] < $dtime)
            {
                $this->flash(0, '不支持创建今天之前的订单');
            }

            $price_rules_conds[] = sprintf('(rp_rid = %d AND rp_btime <= %d AND rp_etime >= %d)',
                $rid,
                $val['datm'] + ($val['lgth']-1)*86400,
                $val['datm']
            );

            $other_order_conds[] = sprintf('(o_rid = %d AND o_btime < %d AND o_etime > %d)',
                $rid,
                $val['datm'] + $val['lgth']*86400 + $val['time'][1],
                $val['datm'] + $val['time'][0]
            );
        }
        unset($val);
        $price_rules_conds = implode(' OR ', $price_rules_conds);
        $other_order_conds = implode(' OR ', $other_order_conds);

        $rooms = Zyon_Array::keyto($this->model('room')->getRoomAryByIds(array_keys($order)), 'r_id');
        if (empty($rooms) || count($rooms) !== $count)
        {
            $this->flash(0, '找不到指定预订的房间');
        }

        // 以下处理每个房间的启用日、停用段以及订单最长时间限制，
        // 整理入住客人信息
        foreach ($rooms as $rid => &$val)
        {
            if ($val['r_hid'] !== $hotel['h_id'])
            {
                $this->flash(0, '找不到指定预订的房间');
            }

            if ($val['r_attr'] & ROOM_ATTR_YCFT)
            {
                $this->flash(0, __('指定的房间 %s 不可预订', $val['r_name']));
            }

            if (!isset($glves[$rid]) || !is_array($glves[$rid])
                || !isset($glves[$rid]['name'])
                || !isset($glves[$rid]['mail'])
                || !isset($glves[$rid]['call'])
            )
            {
                $this->flash(0, '缺少入住客人信息');
            }

            $glves[$rid] = array(
                'o_glver_name'  => $glves[$rid]['name'],
                'o_glver_email' => $glves[$rid]['mail'],
                'o_glver_phone' => $glves[$rid]['call'],
            );

            if (!$this->model('order')->verify($glves[$rid]))
            {
                $this->flash(0, __('%s 房间的入住客人信息错误', $val['r_name']));
            }

            $btime = $order[$rid]['datm'];
            $etime = $btime + $order[$rid]['lgth']*86400-1;

            for ($_btime = max($btime, $dtime+86400*$hotel['h_order_enddays']); $_btime < $etime; $_btime+=86400)
            {
                $this->flash(0, __('%s 的 %s 房间不可用', date('Y-m-d', $_btime), $val['r_name']));
            }

            if ($val['r_btime'])
            {
                $_r_btime = max($btime, $val['r_btime']);
                $_r_etime = min($etime, $val['r_etime']);

                while ($_r_btime < $_r_etime)
                {
                    $this->flash(0, __('%s 的 %s 房间不可用', date('Y-m-d', $_r_btime), $val['r_name']));
                }
            }

            if ($btime < $val['r_otime'])
            {
                $this->flash(0, __('房间%s在%s之前尚未启用', $val['r_name'], date('Y-m-d', $val['r_otime'])));
            }
        }
        unset($val);

        // 以下检测旅店预订订单是否超出限制
        if ($hotel['h_order_default_stacode'] == ORDER_STATUS_YD)
        {
            $oldes = $this->model('order')->calNumAryByRidAryAndSta(
                array_keys($order), ORDER_STATUS_YD, $ctime - getSysLimit('BOOKING_LIVE')
            );

            if (!empty($oldes))
            {
                if ($count + array_sum($oldes) > getSysLimit('BOOKING_QNTY'))
                {
                    $this->flash(0, '旅店预订订单数量已满');
                }

                $per = getSysLimit('BOOKING_PERN');
                foreach ($oldes as $rid => $num)
                {
                    if (array_key_exists($rid, $order) && $num > $per - 1)
                    {
                        $this->flash(0, __('%s 房间预订订单数量已满', $rooms[$rid]['r_name']));
                    }
                }
            }
        }

        // 以下处理每个订单的冲突
        $other = $this->model('order')->getOrderAryByIds(
            $this->model('order')->fetchIds(array(
                'o_hid = ' . $hotel['h_id'],
                'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                $other_order_conds,
            ), null, 1)
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
                $_o_btime = max($order[$rid]['datm'], $val['o_bdatm']);
                $_o_etime = min($order[$rid]['datm'] + $order[$rid]['lgth']*86400, $val['o_edatm']);

                while ($_o_btime < $_o_etime)
                {
                    $this->flash(0, __('%s 的 %s 房间已被占用', date('Y-m-d', $_o_btime), $rooms[$rid]['r_name']));
                }

                if ($val['o_bdatm'] == $order[$rid]['datm'] + $order[$rid]['lgth']*86400
                    && $order[$rid]['time'][1] > $val['o_btime'] - $val['o_bdatm'])
                {
                    $this->flash(0, __('%s 的 %s 房间已被占用', date('Y-m-d', $val['o_bdatm']), $rooms[$rid]['r_name']));
                }

                if ($val['o_edatm'] == $order[$rid]['datm']
                    && $order[$rid]['time'][0] < $val['o_etime'] - $val['o_edatm'])
                {
                    $this->flash(0, __('%s 的 %s 房间已被占用', date('Y-m-d', $val['o_edatm']), $rooms[$rid]['r_name']));
                }
            }
            unset($val);
        }
        unset($other);

        // 以下处理每个订单的房费
        $basic = $this->model('room.price')->getBasicPriceByRids(array_keys($rooms));
        if (!$basic || count($basic) !== $count)
        {
            $this->flash(0, '无法获取预订房间的房价');
        }

        $rules = Zyon_Array::group($this->model('room.price')->getPriceAryByIds(
            $this->model('room.price')->fetchIds(array(
                'rp_hid = ' . $hotel['h_id'],
                $price_rules_conds,
            ))
        ), 'rp_rid');

        $price = array();
        foreach (array_keys($basic) as $rid)
        {
            $price[$rid] = $this->model('room.price')->getPriceDotAry(
                $basic[$rid],
                $order[$rid]['datm'], $order[$rid]['datm'] + $order[$rid]['lgth']*86400-1,
                isset($rules[$rid]) ? $rules[$rid] : array()
            );
        }
        unset($basic, $rules);

        // 以下校验提交订单时的房费和当前是否相同
        if (count($price) !== count($orent))
        {
            $this->flash(0, '订单价格错误');
        }

        $obsum = 0;
        foreach ($price as $rid => &$val)
        {
            if (!isset($orent[$rid]) || count($val) !== count($orent[$rid]))
            {
                $this->flash(0, '订单价格错误');
            }

            foreach ($val as $k => $v)
            {
                if (!isset($orent[$rid][$k]))
                {
                    $this->flash(0, '订单价格错误');
                }

                if ((string)($v/100) !== $orent[$rid][$k])
                {
                    $this->flash(0, '订单价格已失效，请刷新重试');
                }

                $obsum += $v;
            }
        }
        unset($val);

        $sales = $this->model('user')->getUser($hotel['h_order_default_saleman']);
        if (!$sales)
        {
            $this->flash(0, '读取旅店销售人员信息失败');
        }

        $otype = $this->model('hotel.typedef')->getTypedef($hotel['h_order_default_typedef']);
        if (!$otype)
        {
            $this->flash(0, '读取旅店预订类型信息失败');
        }

        $cfrom = $this->model('hotel.channel')->getChannel($hotel['h_order_default_channel']);
        if (!$cfrom)
        {
            $this->flash(0, '读取旅店预订渠道信息失败');
        }

        $osetm = $this->model('hotel.settlem')->getSettlem($hotel['h_obill_default_settlem']);
        if (!$osetm)
        {
            $this->flash(0, '读取旅店结算方式信息失败');
        }

        $ltime = $hotel['h_attr'] & (int)HOTEL_ATTR_ZDGQ ? $ctime + $hotel['h_obill_keptime'] : null;

        /**
         * 开启事务，创建订单
         */
        $this->model('order')->dbase()->beginTransaction();
        try
        {
            $bid = $this->model('bill')->addBill(
                $this->model('bill')->getNewBill(
                    $hid,
                    $osetm['hs_id'], $osetm['hs_name'],
                    $obsum, 0,
                    mb_substr($gbker['o_gbker_name'], 0, 10) . '-' . date('ymdHi', $ctime),
                    $ltime
                )
            );
            if (!$bid) throw new exception('创建订单相关账单信息失败');

            foreach ($order as $rid => $order)
            {
                $order = $this->model('order')->getNewOrder(
                    $rooms[$rid],
                    $order['datm']+$order['time'][0], $order['datm']+86400*$order['lgth']+$order['time'][1],
                    json_encode($price[$rid]), json_encode($price[$rid]), $hotel['h_order_default_stacode']
                );
                $order['o_bid']  = $bid;
                $order['o_cid']  = $cfrom['hc_id'];
                $order['o_cnm']  = $cfrom['hc_name'];
                $order['o_tid']  = $otype['ht_id'];
                $order['o_tnm']  = $otype['ht_name'];
                $order['o_sid']  = $sales['u_id'];
                $order['o_snm']  = $sales['u_realname'];
                $order['o_attr'] = ORDER_ATTR_ZXDD;

                $order = array_merge($gbker, $glves[$rid], $order);

                $oid = $this->model('order')->addOrder($order);
                if (!$oid) throw new exception('创建订单失败');

                if ($order = $this->model('order')->getOrder($oid))
                {
                    $this->model('log.order')->addLog(
                        $this->model('log.order')->getNewCreateByGserLog($gbker, $order)
                    );
                }
            }

            if ($bill = $this->model('bill')->getBill($bid))
            {
                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewCreateByGserLog($gbker, $bill)
                );
            }

            $this->model('order')->dbase()->commit();

            $this->flash(1, array(
                'forward' => "/hostel/rosta?hid={$hid}",
            ));
        }
        catch (Exception $e)
        {
            $this->model('order')->dbase()->rollBack();
            $this->error($e);
        }

        $this->flash(0);
    }
}
// End of file : OrderController.php
