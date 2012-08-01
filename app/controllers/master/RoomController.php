<?php
/**
 * @version    $Id$
 */
class Master_RoomController extends MasterController
{
    /**
     * 房间列表 
     */
    public function indexAction()
    {
        $rooms = $this->model('room')->getRoomAryByHid($this->_hostel['h_id']);
        $rooms = empty($rooms) ? array() : $rooms;

        $this->view->rooms = $rooms;
        $this->view->index = $this->model('room')->getIndexAryByRoomAry($rooms);

        foreach ($this->view->rooms as &$val)
        {
            $val['r_oper'] = $this->model('room')->getUsableActions($val, $this->_hostel);
            $val['r_view'] = $this->model('room')->getViewNameByCode($val['r_view']);
            $val['r_stat'] = $this->model('room')->getStateNameByCode($val['r_status']);
            $val['r_rsta'] = $this->model('room')->getRealTimeStateNames($val, $this->_hostel);
        }
    }

    /**
     * 查询旅店当前可用房间名接口
     */
    public function validRnameAction()
    {
        $rname = $this->input('rname');
        if ($rname != '' && mb_strlen($rname) <= 15 &&
            !$this->model('room')->getRoomIdByName($rname, $this->_hostel['h_id']))
        {
            exit(json_encode(true));
        }

        exit(json_encode(false));
    }

    /**
     * 获取房间价格，包含price和brice
     */
    public function fetchValueAction()
    {
        $room  = $this->loadUsableRoom($this->input('rid'));

        $bdate = $this->input('bdate');
        $edate = $this->input('edate');
        $valid = $this->input('valid');
        if (!Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            $this->flash(0, '日期格式错误');
        }

        $btime = strtotime($bdate);
        $etime = strtotime($edate);

        if ($etime < $btime)
        {
            $this->flash(0, '结束日期不能早于起始日期');
        }

        if ($prices = $this->model('room.price')->getPriceDotAryByRid(
            $room['r_id'], $btime, $etime
        ))
        {
            // Does not hava book price now.
            $flash = array('context' => array('price' => $prices, 'brice' => $prices));

            if ($valid)
            {
                $flash['invalid'] = array();
                $flash['exttime'] = array(0, 86399);

                $etime += 86399;

                for ($_btime = max($btime, strtotime(date('Y-m-d'))+86400*$this->_hostel['h_order_enddays']); $_btime < $etime; $_btime+=86400)
                {
                    $flash['invalid'][$_btime] = 1;
                }

                if ($room['r_btime'])
                {
                    $_r_btime = max($btime, $room['r_btime']);
                    $_r_etime = min($etime, $room['r_etime']);

                    while ($_r_btime < $_r_etime)
                    {
                        $flash['invalid'][$_r_btime] = 1;
                        $_r_btime += 86400;
                    }
                }

                if ($btime < $room['r_otime'])
                {
                    $_r_btime = $btime;
                    $_r_otime = min($etime, $room['r_otime']);

                    while ($_r_btime < $_r_otime)
                    {
                        $flash['invalid'][$_r_btime] = 1;
                        $_r_btime += 86400;
                    }
                }

                $owhere = array(
                    'o_hid = ' . (int)$room['r_hid'],
                    'o_rid = ' . (int)$room['r_id'],
                    'o_status <> ' . (int)ORDER_STATUS_YQX,
                    'o_status <> ' . (int)ORDER_STATUS_YD,
                    'o_bdatm <= ' . (int)$etime+1,
                    'o_edatm >= ' . (int)$btime,
                );

                if ($oid = $this->input('oid', 'numeric'))
                {
                    $owhere[] = 'o_id <> ' . (int)$oid;
                }

                $orders = Zyon_Array::keyto(
                    $this->model('order')->getOrderAryByIds($this->model('order')->fetchIds($owhere)),
                    'o_id'
                );

                if (!empty($orders))
                {
                    foreach ($orders as &$order)
                    {
                        if ($order['o_edatm'] == $btime)
                        {
                            $flash['exttime'][0] = max($flash['exttime'][0], $order['o_etime'] - $order['o_edatm']);
                        }

                        if ($order['o_bdatm'] == $etime+1)
                        {
                            $flash['exttime'][1] = min($flash['exttime'][1], $order['o_btime'] - $order['o_bdatm']);
                        }

                        $_o_btime = max($btime, $order['o_bdatm']);
                        $_o_etime = min($etime, $order['o_edatm']);

                        while ($_o_btime < $_o_etime)
                        {
                            $flash['invalid'][$_o_btime] = 1;
                            $_o_btime += 86400;
                        }
                    }
                    unset($order);
                }
            }

            $this->flash(1, $flash);
        }

        $this->flash(0);
    }

    /**
     * 获取价格计划，包含price和brice
     */
    public function fetchValuePlansAction()
    {
        $room  = $this->loadUsableRoom($this->input('rid'));
        $bdate = $this->input('bdate');
        $edate = $this->input('edate');
        if (!Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            $this->flash(0, '日期格式错误');
        }

        $btime = strtotime($bdate);
        $etime = strtotime($edate);

        if ($etime < $btime)
        {
            $this->flash(0, '结束日期不能早于起始日期');
        }

        $prices = $this->model('room.price')->getPriceAryByRidAndTimeLine(
            $room['r_id'], $btime, $etime
        );

        if (is_array($prices))
        {
            $prices = Zyon_Array::keyto($prices, 'rp_id');
            ksort($prices, SORT_NUMERIC);

            foreach ($prices as &$val)
            {
                $val['rp_uname'] = $this->view->escape($val['rp_uname']);
            }

            $this->flash(1, array('context' => array('basic' => $room['r_price'], 'plans' => $prices)));
        }

        $this->flash(0);
    }

    /**
     * 获取房间价格
     */
    public function fetchPriceAction()
    {
        $this->flash(0, '接口已失效');
    }

    /**
     * 获取价格计划
     */
    public function fetchPricePlansAction()
    {
        $this->flash(0, '接口已失效');
    }

    /**
     * 创建房间
     */
    public function createAction()
    {
        if ($this->input('rid', 'numeric'))
        {
            $room = $this->loadUsableRoom($this->input('rid', 'numeric'));
            $room['r_price'] = $room['r_price']/100;
        }
        else
        {
            $room = array(
                'r_id'      => null,
                'r_desc'    => null,
                'r_area'    => null,
                'r_zone'    => null,
                'r_address' => null,
                'r_type'    => null,
                'r_price'   => null,
                'r_otime'   => $_SERVER['REQUEST_TIME']-86400*31,
                'r_layout'  => null,
                'r_floor'   => null,
                'r_tfloor'  => null,
                'r_view'    => 0
            );
        }

        $room['views'] = $this->model('room')->getViewAryByCode($room['r_view']);

        $this->view->room  = $room;
        $this->view->index = $this->model('room')->getIndexAryByHid($this->_master['u_hid']);
    }

    /**
     * 执行创建房间动作
     */
    public function doCreateAction()
    {
        if (!Zyon_Util::isDate($date = $this->input('odate')))
        {
            $this->flash(0, '旅店开张日期格式错误');
        }

        $info = array(
            'r_desc'    => $this->input('desc'),
            'r_area'    => $this->input('area'),
            'r_zone'    => $this->input('zone'),
            'r_view'    => $this->model('room')->getViewCodeByList($this->input('view', 'array') ?: array()),
            'r_type'    => $this->input('type'),
            'r_price'   => $this->input('price', 'numeric'),
            'r_otime'   => strtotime($date),
            'r_layout'  => $this->input('layout'),
        );

        if ($info['r_type'] == '')
        {
            $this->flash(0, '房型不能为空');
        }

        if ($info['r_price'] == '' || $info['r_price'] < 0)
        {
            $this->flash(0, '价格不能为空或者小于0');
        }

        if (!Zyon_Util::isMoneyFloat($info['r_price']))
        {
            $this->flash(0, '房间价格输入错误');
        }

        $info['r_price'] = $info['r_price']*100;
        if ($info['r_price'] >= 10000000)
        {
            $this->flash(0, '房间价格数值过大，必须小于100000');
        }

        if ($info['r_otime'] < 86400)
        {
            $this->flash(0, '开张时间值超出最小限制');
        }

        $size = sizeof($info);
        foreach ($info as $key => $val)
        {
            $info[$key] = $val = trim((string)$val);
            if ($val == '')
            {
                unset($info[$key]);
                continue;
            }
        }
        $done = floor(sizeof($info)/$size*100) . '%';

        $names = $this->input('names', 'array');
        if (empty($names))
        {
            $this->flash(0, '房间集合不能为空');
        }

        foreach ($names as &$rname)
        {
            if (!is_string($rname))
            {
                $this->flash(0, '房间名称格式错误');
            }

            $rname = trim($rname);
            if ($rname == '')
            {
                $this->flash(0, '房间名称不能为空');
            }

            if (mb_strlen($rname) > 15)
            {
                $this->flash(0, '房间名称不能超过15个字符');
            }

            if (count(array_keys($names, $rname, true)) > 1)
            {
                $this->flash(0, __('房间名称%s重复，请选择一个新的房间名', $rname));
            }

            if ($this->model('room')->getRoomIdByName($rname, $this->_hostel['h_id']))
            {
                $this->flash(0, __('已有同名房间%s，请选择一个新的房间名', $rname));
            }
        }
        unset($rname);

        foreach ($names as $rname)
        {
            if ($rid = $this->model('room')->addRoom(array_merge($info, array(
                'r_hid'  => $this->_hostel['h_id'],
                'r_name' => $rname,
            ))))
            {
                if ($room = $this->model('room')->getRoom($rid))
                {
                    $this->model('log.room')->addLog($this->model('log.room')->getNewCreateLog(
                        $this->_master, $room
                    ));
                }
            }
            else
            {
                $this->flash(0, __('房间%s创建失败', $rname));
            }
        }

        $this->flash(1, array(
            'timeout' => 10,
            'forward' => "/master/room/",
            'message' => __('创建房间成功，当前房间信息完善度：%s', $done),
            'content' => array(
                __("查看房间列表？请<a href='%s'>点击这里</a>", "/master/room/"),
                __("修改房间信息？请<a href='%s'>点击这里</a>", "/master/room/update?rid={$rid}"),
                __("继续创建房间？请<a href='%s'>点击这里</a>", '/master/room/create'),
                __("克隆当前房间？请<a href='%s'>点击这里</a>", "/master/room/create?rid={$rid}"),
            ),
        ));
    }

    /**
     * 更新房间信息
     */
    public function updateAction()
    {
        $room = $this->loadUsableRoom($this->input('rid'));
        $room['views'] = $this->model('room')->getViewAryByCode($room['r_view']);
        $room['attrs'] = $this->model('room')->getAttrAryByCode($room['r_attr']);
        $this->view->room = $room;
        $this->view->stop = $room['r_btime'] > 0;

        if (false)
        {
            $equips = $this->model('room.equip')->getEquipAryByRid($room['r_id']);
            if (empty($equips) && is_array($equips))
            {
                $this->view->init = true;
            }
            else
            {
                $this->view->init = false;
            }

            $this->view->equips = array();
            if (!empty($equips))
            {
                foreach ($equips as &$equip)
                {
                    if (!$equip['re_qnty'])
                    {
                        continue;
                    }

                    $type = $this->model('room.equip')->getNameByType($equip['re_type']);
                    if (!isset($this->view->equips[$type]))
                    {
                        $this->view->equips[$type] = array();
                    }

                    $this->view->equips[$type][] = $equip;
                }
            }

            foreach (array_keys($this->model('room.equip')->getTypes()) as $type)
            {
                if (!isset($this->view->equips[$type]))
                {
                    $this->view->equips[$type] = array();
                }
            }
        }

        $this->view->index = $this->model('room')->getIndexAryByHid($this->_master['u_hid']);
    }

    /**
     * 执行更新房间信息
     */
    public function doUpdateAction()
    {
        $room = $this->loadUsableRoom($rid = $this->input('rid'));

        if (!Zyon_Util::isDate($odate = $this->input('odate')))
        {
            $this->flash(0, '房间开张日期格式错误');
        }
        $otime = strtotime($odate);

        if ($otime > $room['r_otime'])
        {
            $otmin = $this->model('order')->fetch(array(
                'where' => array(
                    'o_hid = ' . (int)$this->_hostel['h_id'],
                    'o_rid = ' . (int)$room['r_id'],
                    'o_bdatm <= ' . $otime,
                ),
                'field' => 'MIN(o_bdatm) as otmin',
            ));

            $otmin = @$otmin[0]['otmin'];
            if (is_numeric($otmin) && $otime > $otmin)
            {
                $this->flash(0, __('房间开张日期必须在%s之前（已有订单）', date('Y-m-d', $otmin)));
            }
        }

        $info = array(
            'r_desc'    => $this->input('desc'),
            'r_area'    => $this->input('area'),
            'r_zone'    => $this->input('zone'),
            'r_type'    => $this->input('type'),
            'r_view'    => $this->model('room')->getViewCodeByList($this->input('view', 'array') ?: array()),
            'r_otime'   => $otime,
            'r_layout'  => $this->input('layout'),
        );

        if ($info['r_type'] == '')
        {
            $this->flash(0, '房型不能为空');
        }

        if ($info['r_otime'] < 86400)
        {
            $this->flash(0, '开张时间值超出最小限制');
        }

        foreach ($info as $key => $val)
        {
            $info[$key] = trim((string)$val);
        }

        if (($room = $this->model('room')->getRoom($rid)) && $this->model('room')->modRoom($rid, $info))
        {
            $this->model('log.room')->addLog($this->model('log.room')->getNewUpdateLog(
                $this->_master, $room, $room = $this->model('room')->getRoom($rid)
            ));

            $this->flash(1, '修改房间信息成功');
        }

        $this->flash(0);
    }

    /**
     * 执行批创建房间设施操作
     */
    public function doCreateEquipsAction()
    {
        $room = $this->loadUsableRoom($rid = $this->input('rid'));

        $equips = $this->input('equips', 'array');
        $addons = $this->input('addons', 'array');

        if (empty($equips['t']) || !is_array($equips['t'])
            || empty($equips['q']) || !is_array($equips['q'])
            || empty($equips['a']) || !is_array($equips['a']))
        {
            $this->flash(1);
        }

        $value = array();
        $types = $this->model('room.equip')->getTypes();

        foreach ($equips['t'] as $type => $list)
        {
            if (!array_key_exists($type, $types))
            {
                $this->flash(0, '不被支持的设施类型：' . $type);
            }

            foreach ($list as $hash => $name)
            {
                if (empty($name)
                    || empty($equips['a'][$type][$hash])
                    || empty($equips['q'][$type][$hash]) || !is_numeric($equips['q'][$type][$hash]))
                {
                    continue;
                }

                $value[$name] = $this->model('room.equip')->getNewEquip(
                    $name, $equips['q'][$type][$hash], $types[$type], $this->_master['u_hid'], $room['r_id']
                );
            }
        }

        if (!empty($addons))
        {
            foreach ($addons as $type => $list)
            {
                if (!array_key_exists($type, $types))
                {
                    $this->flash(0, '不被支持的设施类型：' . $type);
                }

                foreach ($list as $hash => $name)
                {
                    if (!empty($name))
                    {
                        $value[$name] = $this->model('room.equip')->getNewEquip(
                            $name, 1, $types[$type], $this->_master['u_hid'], $room['r_id']
                        );
                    }
                }
            }
        }

        $existEquip = $this->model('room.equip')->getEquipIds(array('re_rid' => $room['r_id']));
        if (is_array($existEquip) && empty($existEquip) && $this->model('room.equip')->addEquipAry($value))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }

    public function doCreateEquipAction()
    {
        $room = $this->loadUsableRoom($this->input('rid'));

        $equip = $this->input('equip', 'array');
        if (empty($equip['q']) || !is_array($equip['q']) || count($equip['q']) !== 1
            || empty($equip['t']) || !is_array($equip['t']) || count($equip['t']) !== 1)
        {
            $this->flash(0, '参数错误');
        }

        $type = key($equip['q']);
        $list = $equip['q'][$type];
        if (count($list) !== 1)
        {
            $this->flash(0);
        }
        $hash = key($list);
        $qnty = $list[$hash];

        if (!is_numeric($qnty) || $qnty <= 0)
        {
            $this->flash(0, '新增设施数量不能小于1');
        }

        if (empty($equip['t'][$type][$hash]))
        {
            $this->flash(0);
        }

        $name = $equip['t'][$type][$hash];

        if ($equips = $this->model('room.equip')->getEquipAry(array(
            're_name' => $name,
            're_type' => $this->model('room.equip')->getTypeByName($type),
            're_rid'  => $room['r_id']
        )))
        {
            $equip = array_shift($equips);

            if ($equip['re_qnty'] > 0)
            {
                $this->flash(0, '已存在同名设施');
            }

            if ($this->model('room.equip')->modEquip($equip['re_id'], array('re_qnty' => $qnty)))
            {
                $this->flash($equip['re_id']);
            }
        }
        else
        {
            if ($eid = $this->model('room.equip')->addEquip($this->model('room.equip')->getNewEquip(
                $name, $qnty, $this->model('room.equip')->getTypeByName($type), $room['r_hid'], $room['r_id']
            )))
            {
                $this->flash($eid);
            }
        }

        $this->flash(0);
    }

    public function doUpdateEquipQntyAction()
    {
        $rid = $this->input('rid');
        $eid = $this->input('eid');
        $qty = $this->input('qty');

        if (empty($rid) || empty($eid) || !is_numeric($qty))
        {
            $this->flash(0, '参数错误');
        }

        $room = $this->loadUsableRoom($rid);
        $equip = $this->model('room.equip')->getEquip($eid);
        if (!$equip || $equip['re_rid'] !== $room['r_id'])
        {
            $this->flash(0, '找不到指定的设施');
        }

        if ($qty === $equip['re_qnty'])
        {
            $this->flash(1);
        }

        if ($this->model('room.equip')->modEquip($eid, array('re_qnty' => $qty)))
        {
            $this->flash(1, '设施数量已更新');
        }

        $this->flash(0);
    }

    /**
     * 更新基础房价
     */
    public function doUpdateBasicPriceAction()
    {
        $room = $this->loadUsableRoom($this->input('rid'));
        $hash = $this->input('hash');
        if ($hash !== $room['r_mtime'])
        {
            $this->flash(0);
        }

        $price = $this->input('price');
        if (!Zyon_Util::isMoneyFloat($price))
        {
            $this->flash(0, '价格错误');
        }

        if ($price < 0)
        {
            $this->flash(0, '价格不能小于0');
        }

        $price = $price*100;
        if ($price >= 10000000)
        {
            $this->flash(0, '价格超出系统限制范围，必须小于100000');
        }

        if ($price === (int)$room['r_price'])
        {
            $this->flash(0, '价格没有变化');
        }

        if ($this->model('room')->modRoom($room['r_id'], array('r_price' => $price)))
        {
            $this->model('log.room')->addLog(
                $this->model('log.room')->getNewUpdateBasicPriceLog(
                    $this->_master, $room, $this->model('room')->getRoom($room['r_id'])
                )
            );

            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 批量更新基础房价
     */
    public function doUpdateBasicPricesAction()
    {
        if ($this->cache()->load($hash = md5(__METHOD__ . '@' . $this->_hostel['h_id'] . '#')))
        {
            $this->flash(0, '操作太过频繁，请稍侯再试！');
        }
        $this->cache()->save(1, $hash, array(), 5);

        $price = $this->input('price');
        if (!Zyon_Util::isMoneyFloat($price))
        {
            $this->flash(0, '价格错误');
        }

        if ($price < 0)
        {
            $this->flash(0, '价格不能小于0');
        }

        $price = $price*100;
        if ($price >= 10000000)
        {
            $this->flash(0, '价格超出系统限制范围，必须小于100000');
        }

        $rids = $this->input('rids', 'array');
        if (empty($rids))
        {
            $this->flash(0, '没有选中任何房间');
        }

        foreach ($rids as &$val)
        {
            if (!Zyon_Util::isUnsignedInt($val))
            {
                $this->flash(0, '指定的房间列表错误');
            }
        }
        unset($val);

        $count = count($rids);
        $rooms = Zyon_Array::keyto($this->model('room')->getRoomAryByIds($rids), 'r_id');
        if (empty($rooms) || count($rooms) !== $count)
        {
            $this->flash(0, '指定的房间列表错误');
        }

        foreach ($rooms as $rid => &$room)
        {
            if ($room['r_hid'] !== $this->_hostel['h_id'])
            {
                $this->flash(0, '指定的房间列表错误');
            }

            if ($room['r_price'] === $price)
            {
                unset($rooms[$rid]);
            }
        }
        unset($room);

        $rids = array_keys($rooms);
        if (empty($rids))
        {
            $this->flash(1);
        }

        if ($this->model('room')->modRoomByIds($rids, array('r_price' => $price)))
        {
            if ($newRooms = Zyon_Array::keyto($this->model('room')->getRoomAryByIds($rids), 'r_id'))
            {
                foreach ($rooms as &$room)
                {
                    $this->model('log.room')->addLog(
                        $this->model('log.room')->getNewUpdateBasicPriceLog(
                            $this->_master, $room, $newRooms[$room['r_id']]
                        )
                    );
                }
                unset($room);
            }

            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 创建价格计划
     */
    public function doCreatePricePlanAction()
    {
        $room  = $this->loadUsableRoom($this->input('rid'));
        $hash = $this->input('hash');
        if ($hash !== $room['r_mtime'])
        {
            $this->flash(0);
        }

        $bdate = $this->input('bdate');
        $edate = $this->input('edate');
        $price = $this->input('price');

        if (!Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            $this->flash(0, '日期格式错误');
        }

        $btime = strtotime($bdate);
        $etime = strtotime($edate);

        if ($etime < $btime)
        {
            $this->flash(0, '结束日期不能早于起始日期');
        }

        if (!Zyon_Util::isMoneyFloat($price))
        {
            $this->flash(0, '价格错误');
        }

        if ($price < 0)
        {
            $this->flash(0, '价格不能小于0');
        }

        $price = $price*100;
        if ($price >= 10000000)
        {
            $this->flash(0, '价格超出系统限制范围，必须小于100000');
        }

        if ($this->model('room.price')->addPrice($this->model('room.price')->getNewPrice(
            $room['r_hid'], $room['r_id'], $this->_master['u_id'], $this->_master['u_realname'], $price, $btime, $etime
        )))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 批量修改价格规则房间列表 
     */
    public function updateRuleAction()
    {
        $rooms = $this->model('room')->getRoomAryByHid($this->_hostel['h_id']);
        $rooms = empty($rooms) ? array() : $rooms;

        $this->view->rooms = $rooms;
        $this->view->index = $this->model('room')->getIndexAryByRoomAry($rooms);

        foreach ($this->view->rooms as &$val)
        {
            $val['r_oper'] = $this->model('room')->getUsableActions($val, $this->_hostel);
            $val['r_view'] = $this->model('room')->getViewNameByCode($val['r_view']);
            $val['r_stat'] = $this->model('room')->getStateNameByCode($val['r_status']);
            $val['r_rsta'] = $this->model('room')->getRealTimeStateNames($val, $this->_hostel);
        }
    }

    /**
     * 批量创建价格计划
     */
    public function doCreatePricePlansAction()
    {
        $bdate = $this->input('bdate');
        $edate = $this->input('edate');
        $price = $this->input('price');

        if (!Zyon_Util::isDate($bdate) || !Zyon_Util::isDate($edate))
        {
            $this->flash(0, '日期格式错误');
        }

        $btime = strtotime($bdate);
        $etime = strtotime($edate);

        if ($etime < $btime)
        {
            $this->flash(0, '结束日期不能早于起始日期');
        }

        if (!Zyon_Util::isMoneyFloat($price))
        {
            $this->flash(0, '价格错误');
        }

        if ($price < 0)
        {
            $this->flash(0, '价格不能小于0');
        }

        $price = $price*100;
        if ($price >= 10000000)
        {
            $this->flash(0, '价格超出系统限制范围，必须小于100000');
        }

        $rids = $this->input('rids', 'array');
        if (empty($rids))
        {
            $this->flash(0, '没有选中任何房间');
        }

        foreach ($rids as &$val)
        {
            if (!Zyon_Util::isUnsignedInt($val))
            {
                $this->flash(0, '指定的房间列表错误');
            }
        }
        unset($val);

        $rooms = $this->model('room')->getRoomAryByIds($rids);
        if (empty($rooms) || count($rooms) !== count($rids))
        {
            $this->flash(0, '指定的房间列表错误');
        }

        $maps = array();
        foreach ($rooms as &$room)
        {
            if ($room['r_hid'] !== $this->_hostel['h_id'])
            {
                $this->flash(0, '指定的房间列表错误');
            }

            $maps[] = $this->model('room.price')->getNewPrice(
                $room['r_hid'], $room['r_id'], $this->_master['u_id'], $this->_master['u_realname'], $price, $btime, $etime
            );
        }
        unset($room);

        if ($ret = $this->model('room.price')->addPriceAry($maps))
        {
            $this->flash(1, "{$ret} 个房间已经应用新的价格计划");
        }

        $this->flash(0);
    }

    /**
     * 房间详情页
     */
    public function detailAction()
    {
        return $this->_forward('update', 'room', 'master');
    }

    /**
     * 执行停用、启用房间操作
     */
    public function doRetainAction()
    {
        $room = $this->loadUsableRoom($this->input('rid'));
        $oper = $this->input('pause');
        $desc = $this->input('desc');

        if (!$oper)
        {
            if (!$this->model('room')->modRoom($room['r_id'], array('r_btime' => 0, 'r_etime' => 0)))
            {
                $this->flash(0);
            }

            $log = $this->model('log.room')->getNewRetainToLiveLog(
                $this->_master, $room, $this->model('room')->getRoom($room['r_id'])
            );
            $log['lr_memo'] = $desc;

            $this->model('log.room')->addLog($log);

            $this->flash(1);
        }

        /**
         * 处理停用起始时间
         */
        if (!Zyon_Util::isDate($bdate = $this->input('bdate')))
        {
            $this->flash(0, ' 停用起始时间必须是合法的日期格式');
        }
        $btime = strtotime($bdate);

        /**
         * 处理停用结束时间
         */
        if (!Zyon_Util::isDate($edate = $this->input('edate')))
        {
            $this->flash(0, '停用结束时间必须是合法的日期格式');
        }
        $etime = strtotime($edate)+86399;

        if ($etime < $btime)
        {
            $this->flash(0, '停用结束时间不能早于停用起始时间');
        }

        if (!$this->model('room')->modRoom($room['r_id'], array(
            'r_btime' => $btime, 'r_etime' => $etime,
        )))
        {
            $this->flash(0);
        }

        $log = $this->model('log.room')->getNewRetainToStopLog(
            $this->_master, $room, $this->model('room')->getRoom($room['r_id'])
        );
        $log['lr_memo'] = $desc;

        $this->model('log.room')->addLog($log);

        $this->flash(1);
    }

    /**
     * 执行修改房间扩展属性操作
     */
    public function doUpdateAttrsAction()
    {
        $room = $this->loadUsableRoom($this->input('rid'));
        $attr = $this->model('room')->getAttrCodeByList($this->input('attr', 'array') ?: array());

        if ($this->model('room')->modRoom($room['r_id'], array('r_attr' => $attr)))
        {
            $this->model('log.room')->addLog($this->model('log.room')->getNewUpdateLog(
                $this->_master, $room, $this->model('room')->getRoom($room['r_id']),
                '扩展属性：' . implode("\r\n, ", $this->model('room')->getAttrListByCode($attr))
            ));

            $this->flash(1, '修改房间属性成功');
        }

        $this->flash(0);
    }

    /**
     * 房间相关操作
     */
    public function handleAction()
    {
        $room  = $this->loadUsableRoom($this->input('rid'));
        $act   = $this->input('act');
        if (!array_key_exists($act, $acts = $this->model('room')->getUsableActions($room, $this->_hostel)))
        {
            $this->flash(0, '房间当前状态不允许执行指定操作');
        }

        $this->view->room   = $room;
        $this->view->action = $acts[$act];
        $this->view->states = $this->model('room')->getStatus();

        $func = 'handle' . strtoupper($act);
        method_exists($this, $func) AND $this->$func($room);
    }

    /**
     * 克隆房间操作
     */
    public function handleKLFJ($room)
    {
        $this->_redirect('/master/room/create?rid=' . $room['r_id']);
    }

    /**
     * 退房结帐操作
     */
    public function handleTFJZ($room)
    {
        $order = $this->model('order')->fetchAry(
            array(
                'o_rid = ' . $this->model('order')->quote($room['r_id']),
                'o_status = ' . $this->model('order')->quote(ORDER_STATUS_ZZ),
            ),
            null,
            1
        );
        if (!$order)
        {
            $this->flash(0, '没有可操作的订单');
        }

        $order = array_pop($order);

        $this->_redirect(sprintf('/master/order/handle?oid=%s&sta=%s&act=%s',
            rawurlencode($order['o_id']),
            rawurlencode($order['o_status']),
            rawurlencode(ORDER_ACTION_BLTF)
        ));
    }
}
// End of file : RoomController.php
