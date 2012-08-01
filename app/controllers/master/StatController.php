<?php
/**
 * @version    $Id$
 */
class Master_StatController extends MasterController
{
    /**
     * _limitedActions
     * 
     * @var array
     */
    protected static $_limitedActions = array(
        'skls' => '收款流水',
        'rzjl' => '入住记录',
        'tfjl' => '退房记录',
        'yzjl' => '应住记录',
        'ytjl' => '应退记录',

        'xsbb-xsry' => '销售报表（销售人员）',
        'xsbb-ydlx' => '销售报表（预订类型）',
        'xsbb-ydqd' => '销售报表（预订渠道）',
        'xsbb-syfj' => '销售报表（所有房间）',
        'xsbb-qtfy' => '销售报表（其它费用）',

        'jsbb-skqddzmx' => '结算报表（收款渠道对账明细）',
        'jsbb-xsrydzmx' => '结算报表（销售人员对账明细）',
        'jsbb-ydqddzmx' => '结算报表（预订渠道对账明细）',
        'jsbb-ydkrdzmx' => '结算报表（预订客人对账明细）',
    );

    /**
     * getHashUqri
     * 
     * @param mixed $user
     * @return string
     */
    public function getHashUqri(&$user)
    {
        return md5(__CLASS__ . ':' . strtolower($this->getRequest()->getActionName()) . "@{$user['u_id']}#");
    }

    /**
     * postDispatch
     * 
     * @return void
     */
    public function postDispatch()
    {
        if (array_key_exists($act = strtolower($this->getRequest()->getActionName()), static::$_limitedActions))
        {
            // 导出CSV格式报表
            if (($ext = strtolower($this->input('ct'))) === 'csv')
            {
                // 数据格式以Windows下使用为准
                // 参考 http://msdn.microsoft.com/zh-cn/library/ms155919%28v=SQL.100%29.aspx
                $data = $this->view->render("master/stat/{$act}.{$ext}");
                $data = str_replace("\n", "\r\n", preg_replace('/\s*\n{2,}\s*/', "\n", str_replace("\r", "\n", $data)));
                $data = preg_replace('/(\s*,)\s*\r\n\s*/', '\1', $data);

                // 需要保证在Excel中打开不出现乱码，编码为 CP936
                $data = mb_convert_encoding($data, 'CP936', 'UTF-8');

                // 报表数据时间范围
                $date = isset($this->view->tmln) && is_array($this->view->tmln)
                    ? date('Y-m-d', $this->view->tmln[0]) . '至' . date('Y-m-d', $this->view->tmln[1])
                    : (isset($this->view->date) && Zyon_Util::isDate($this->view->date) ? $this->view->date : '');

                $name = static::$_limitedActions[$act] . $date . '.' . $ext;

                $this->force($data, $name, 'text/csv; charset=CP936');
            }
        }

        parent::postDispatch();
    }

    /**
     * 报表统计主页
     */
    public function indexAction()
    {
    }

    /**
     * 收款流水
     */
    public function sklsAction()
    {
        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], STAT_LGTH_D, $date);
        if (!$tmln || $tmln[0] > ($time = time()))
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $skls = $this->model('bill.journal')->getJournalAryByHidAndDay($this->_hostel['h_id'], $date);
        empty($skls) AND $skls = array();

        $qdhz = array();
        foreach ($skls as &$item)
        {
            isset($qdhz[$item['bj_pid']]) OR $qdhz[$item['bj_pid']] = array('name' => $item['bj_pynm'], 'sum' => 0);
            $qdhz[$item['bj_pid']]['sum'] += $item['bj_sum'];
        }
        unset($item);

        $this->view->date = $date;
        $this->view->time = $time;
        $this->view->skls = $skls;
        $this->view->qdhz = $qdhz;
    }

    /**
     * 入住记录
     */
    public function rzjlAction()
    {
        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], STAT_LGTH_D, $date);
        if (!$tmln || $tmln[0] > ($time = time()))
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $this->view->date = $date;
        $this->view->time = (int)$time;
        $this->view->rzjl = $this->model('order')->fetchAry(
            array(
                'o_hid = ' . $this->_hostel['h_id'],
                'o_btime >= ' . $tmln[0],
                'o_btime <= ' . $tmln[1],
                sprintf('o_status IN (%d, %d)', ORDER_STATUS_ZZ, ORDER_STATUS_YJS),
            )
        );
    }

    /**
     * 退房记录
     */
    public function tfjlAction()
    {
        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], STAT_LGTH_D, $date);
        if (!$tmln || $tmln[0] > ($time = time()))
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $this->view->date = $date;
        $this->view->time = (int)$time;
        $this->view->tfjl = $this->model('order')->fetchAry(
            array(
                'o_hid = ' . $this->_hostel['h_id'],
                'o_etime >= ' . $tmln[0],
                'o_etime <= ' . $tmln[1],
                'o_status = ' . ORDER_STATUS_YJS,
            )
        );
    }

    /**
     * 应住记录
     */
    public function yzjlAction()
    {
        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], STAT_LGTH_D, $date);
        if (!$tmln)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $this->view->date = $date;
        $this->view->time = $time;
        $this->view->yzjl = $this->model('order')->fetchAry(
            array(
                'o_hid = ' . $this->_hostel['h_id'],
                'o_btime >= ' . $tmln[0],
                'o_btime <= ' . $tmln[1],
                'o_status = ' . ORDER_STATUS_BL
            )
        );
    }

    /**
     * 应退记录
     */
    public function ytjlAction()
    {
        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], STAT_LGTH_D, $date);
        if (!$tmln)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $time = time();

        $this->view->date = $date;
        $this->view->time = $time;
        $this->view->ytjl = $this->model('order')->fetchAry(
            array(
                'o_hid = ' . $this->_hostel['h_id'],
                'o_etime >= ' . $tmln[0],
                'o_etime <= ' . $tmln[1],
                'o_status = ' . ORDER_STATUS_ZZ,
            )
        );
    }

    /**
     * 查询表单（销售报表 - 预订渠道）
     */
    public function xsbbYdqdFormAction()
    {
		$this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 销售报表 - 预订渠道
     */
    public function xsbbYdqdAction()
    {
        if (($lgth = $this->input('lgth')) == '')
        {
            $this->_forward('xsbb-ydqd-form', 'stat', 'master');
            return;
        }

        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], $lgth, $date);
        if (!$tmln)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $time = time();
        $datm = strtotime(date('Y-m-d', $time));

        $temp = array(
            'time' => (int)$time,
            'xszl' => 0, // 销售总量
            'fjzl' => 0, // 房间总量
            'ydqd' => array(),
        );

        $temp['fjzl'] = (int)$this->model('stat')->calZongFangLiang(
            $this->_hostel['h_id'],
            date('Y-m-d', $tmln[0]),
            date('Y-m-d', $tmln[1])
        );

        $data = $this->model('hotel.channel')->fetch(array(
            'where' => array('hc_hid = ' . $this->_hostel['h_id']),
            'right' => array(
                array(
                    'order',
                    implode(' AND ', array(
                        'hc_id = o_cid',
                        'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                        'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                        'o_bdatm < ' . $tmln[1],
                        'o_edatm > ' . $tmln[0]
                    )),
                    array(
                        'SUM(o_price) AS rent',
                        sprintf(
                            'SUM(IF(o_status IN (%d, %d) AND %d > %d, (LEAST(o_edatm, %d, %d)-GREATEST(o_bdatm, %d))/86400, 0)) AS live_past',
                            ORDER_STATUS_ZZ, ORDER_STATUS_YJS, $datm, $tmln[0], $tmln[1]+1, $datm, $tmln[0]
                        ),
                        sprintf(
                            'SUM(IF(o_status=%d OR o_bdatm >= %d,
                            (LEAST(o_edatm, %d)-GREATEST(o_bdatm, %d))/86400,
                            IF(o_edatm>%d AND %d > %d, (LEAST(o_edatm, %d)-GREATEST(o_bdatm, %d, %d))/86400, 0))) AS live_fore',

                            ORDER_STATUS_BL, $datm, $tmln[1]+1, $tmln[0],
                            $datm, $tmln[1], $datm, $tmln[1]+1, $tmln[0], $datm
                        ),
                    )
                ),
            ),
            'group' => 'hc_id',
        ));

        if (!empty($data))
        {
            foreach ($data as &$val)
            {
                isset($temp['ydqd'][$val['hc_id']]) OR $temp['ydqd'][$val['hc_id']] = array(
                    'name' => $val['hc_name'],
                    'rent' => 0,
                    'live' => array(
                        'past' => 0,
                        'fore' => 0,
                    ),
                );

                $temp['xszl'] += ((int)$val['live_past'] + (int)$val['live_fore']);
                $temp['ydqd'][$val['hc_id']]['rent'] += (int)$val['rent'];
                $temp['ydqd'][$val['hc_id']]['live']['past'] += (int)$val['live_past'];
                $temp['ydqd'][$val['hc_id']]['live']['fore'] += (int)$val['live_fore'];
            }
            unset($val, $data);

            // 减去 timeline 外的房费
            $rent = $this->model('order')->fetch(array(
                'where' => array(
                    'o_hid = ' . $this->_hostel['h_id'],
                    'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                    'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                    'o_bdatm < ' . $tmln[1],
                    'o_edatm > ' . $tmln[0],
                    'o_bdatm < ' . $tmln[0] . ' OR o_edatm > ' . ($tmln[1]+1),
                ),
                'field' => array('o_cid', 'o_bdatm', 'o_edatm', 'o_prices'),
            ));

            if (!empty($rent))
            {
                foreach ($rent as &$val)
                {
                    $val['o_prices'] = json_decode($val['o_prices'], true);

                    $btm = max($val['o_bdatm'], $tmln[0]);
                    $etm = min($val['o_edatm'], $tmln[1]);
                    while ($btm < $etm)
                    {
                        unset($val['o_prices'][$btm]);
                        $btm += 86400;
                    }

                    $temp['ydqd'][$val['o_cid']]['rent'] -= (int)array_sum($val['o_prices']);
                }
                unset($rent, $val);
            }
        }

        $this->view->tmln = $tmln;
        $this->view->lgth = $lgth;
        $this->view->date = $date;
        $this->view->time = $temp['time'];
        $this->view->xszl = $temp['xszl'];
        $this->view->fjzl = $temp['fjzl'];
        $this->view->ydqd = $temp['ydqd'];

        $this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 查询表单（销售报表 - 销售人员）
     */
    public function xsbbXsryFormAction()
    {
		$this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 销售报表 - 销售人员
     */
    public function xsbbXsryAction()
    {
        if (($lgth = $this->input('lgth')) == '')
        {
            $this->_forward('xsbb-xsry-form', 'stat', 'master');
            return;
        }

        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], $lgth, $date);
        if (!$tmln)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $time = time();
        $datm = strtotime(date('Y-m-d', $time));

        $temp = array(
            'time' => (int)$time,

            'xszl' => 0, // 销售总量
            'fjzl' => 0, // 房间总量
            'xsry' => array(),
        );

        $temp['fjzl'] = (int)$this->model('stat')->calZongFangLiang(
            $this->_hostel['h_id'],
            date('Y-m-d', $tmln[0]),
            date('Y-m-d', $tmln[1])
        );

        $data = $this->model('user')->fetch(array(
            'where' => array('u_hid = ' . $this->_hostel['h_id']),
            'right' => array(
                array(
                    'order',
                    implode(' AND ', array(
                        'u_id = o_sid',
                        'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                        'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                        'o_bdatm < ' . $tmln[1],
                        'o_edatm > ' . $tmln[0]
                    )),
                    array(
                        'SUM(o_price) AS rent',
                        sprintf(
                            'SUM(IF(o_status IN (%d, %d) AND %d > %d, (LEAST(o_edatm, %d, %d)-GREATEST(o_bdatm, %d))/86400, 0)) AS live_past',
                            ORDER_STATUS_ZZ, ORDER_STATUS_YJS, $datm, $tmln[0], $tmln[1]+1, $datm, $tmln[0]
                        ),
                        sprintf(
                            'SUM(IF(o_status=%d OR o_bdatm >= %d,
                            (LEAST(o_edatm, %d)-GREATEST(o_bdatm, %d))/86400,
                            IF(o_edatm>%d AND %d > %d, (LEAST(o_edatm, %d)-GREATEST(o_bdatm, %d, %d))/86400, 0))) AS live_fore',

                            ORDER_STATUS_BL, $datm, $tmln[1]+1, $tmln[0],
                            $datm, $tmln[1], $datm, $tmln[1]+1, $tmln[0], $datm
                        ),
                    )
                ),
            ),
            'group' => 'u_id',
        ));

        if (!empty($data))
        {
            foreach ($data as &$val)
            {
                isset($temp['xsry'][$val['u_id']]) OR $temp['xsry'][$val['u_id']] = array(
                    'name' => $val['u_realname'],
                    'rent' => 0,
                    'live' => array(
                        'past' => 0,
                        'fore' => 0,
                    ),
                );

                $temp['xszl'] += ((int)$val['live_past'] + (int)$val['live_fore']);
                $temp['xsry'][$val['u_id']]['rent'] += (int)$val['rent'];
                $temp['xsry'][$val['u_id']]['live']['past'] += (int)$val['live_past'];
                $temp['xsry'][$val['u_id']]['live']['fore'] += (int)$val['live_fore'];
            }
            unset($val, $data);

            // 减去 timeline 外的房费
            $rent = $this->model('order')->fetch(array(
                'where' => array(
                    'o_hid = ' . $this->_hostel['h_id'],
                    'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                    'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                    'o_bdatm < ' . $tmln[1],
                    'o_edatm > ' . $tmln[0],
                    'o_bdatm < ' . $tmln[0] . ' OR o_edatm > ' . ($tmln[1]+1),
                ),
                'field' => array('o_sid', 'o_bdatm', 'o_edatm', 'o_prices'),
            ));

            if (!empty($rent))
            {
                foreach ($rent as &$val)
                {
                    $val['o_prices'] = json_decode($val['o_prices'], true);

                    $btm = max($val['o_bdatm'], $tmln[0]);
                    $etm = min($val['o_edatm'], $tmln[1]);
                    while ($btm < $etm)
                    {
                        unset($val['o_prices'][$btm]);
                        $btm += 86400;
                    }

                    $temp['xsry'][$val['o_sid']]['rent'] -= (int)array_sum($val['o_prices']);
                }
                unset($rent, $val);
            }
        }

        $this->view->tmln = $tmln;
        $this->view->lgth = $lgth;
        $this->view->date = $date;
        $this->view->time = $temp['time'];
        $this->view->xszl = $temp['xszl'];
        $this->view->fjzl = $temp['fjzl'];
        $this->view->xsry = $temp['xsry'];

        $this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 查询表单（销售报表 - 预订类型）
     */
    public function xsbbYdlxFormAction()
    {
		$this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 销售报表 - 预订类型
     */
    public function xsbbYdlxAction()
    {
        if (($lgth = $this->input('lgth')) == '')
        {
            $this->_forward('xsbb-ydlx-form', 'stat', 'master');
            return;
        }

        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], $lgth, $date);
        if (!$tmln)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $time = time();
        $datm = strtotime(date('Y-m-d', $time));

        $temp = array(
            'time' => (int)$time,
            'life' => (int)$this->model('stat')->getStatWait($this->_hostel['h_id'], $lgth),

            'xszl' => 0, // 销售总量
            'fjzl' => 0, // 房间总量
            'ydlx' => array(),
        );

        $temp['fjzl'] = (int)$this->model('stat')->calZongFangLiang(
            $this->_hostel['h_id'],
            date('Y-m-d', $tmln[0]),
            date('Y-m-d', $tmln[1])
        );

        $data = $this->model('hotel.typedef')->fetch(array(
            'where' => array('ht_hid = ' . $this->_hostel['h_id']),
            'right' => array(
                array(
                    'order',
                    implode(' AND ', array(
                        'ht_id = o_tid',
                        'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                        'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                        'o_bdatm < ' . $tmln[1],
                        'o_edatm > ' . $tmln[0]
                    )),
                    array(
                        'SUM(o_price) AS rent',
                        sprintf(
                            'SUM(IF(o_status IN (%d, %d) AND %d > %d, (LEAST(o_edatm, %d, %d)-GREATEST(o_bdatm, %d))/86400, 0)) AS live_past',
                            ORDER_STATUS_ZZ, ORDER_STATUS_YJS, $datm, $tmln[0], $tmln[1]+1, $datm, $tmln[0]
                        ),
                        sprintf(
                            'SUM(IF(o_status=%d OR o_bdatm >= %d,
                            (LEAST(o_edatm, %d)-GREATEST(o_bdatm, %d))/86400,
                            IF(o_edatm>%d AND %d > %d, (LEAST(o_edatm, %d)-GREATEST(o_bdatm, %d, %d))/86400, 0))) AS live_fore',

                            ORDER_STATUS_BL, $datm, $tmln[1]+1, $tmln[0],
                            $datm, $tmln[1], $datm, $tmln[1]+1, $tmln[0], $datm
                        ),
                    )
                ),
            ),
            'group' => 'ht_id',
        ));

        if (!empty($data))
        {
            foreach ($data as &$val)
            {
                isset($temp['ydlx'][$val['ht_id']]) OR $temp['ydlx'][$val['ht_id']] = array(
                    'name' => $val['ht_name'],
                    'rent' => 0,
                    'live' => array(
                        'past' => 0,
                        'fore' => 0,
                    ),
                );

                $temp['xszl'] += ((int)$val['live_past'] + (int)$val['live_fore']);
                $temp['ydlx'][$val['ht_id']]['rent'] += (int)$val['rent'];
                $temp['ydlx'][$val['ht_id']]['live']['past'] += (int)$val['live_past'];
                $temp['ydlx'][$val['ht_id']]['live']['fore'] += (int)$val['live_fore'];
            }
            unset($val, $data);

            // 减去 timeline 外的房费
            $rent = $this->model('order')->fetch(array(
                'where' => array(
                    'o_hid = ' . $this->_hostel['h_id'],
                    'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                    'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                    'o_bdatm < ' . $tmln[1],
                    'o_edatm > ' . $tmln[0],
                    'o_bdatm < ' . $tmln[0] . ' OR o_edatm > ' . ($tmln[1]+1),
                ),
                'field' => array('o_tid', 'o_bdatm', 'o_edatm', 'o_prices'),
            ));

            if (!empty($rent))
            {
                foreach ($rent as &$val)
                {
                    $val['o_prices'] = json_decode($val['o_prices'], true);

                    $btm = max($val['o_bdatm'], $tmln[0]);
                    $etm = min($val['o_edatm'], $tmln[1]);
                    while ($btm < $etm)
                    {
                        unset($val['o_prices'][$btm]);
                        $btm += 86400;
                    }

                    $temp['ydlx'][$val['o_tid']]['rent'] -= (int)array_sum($val['o_prices']);
                }
                unset($rent, $val);
            }
        }

        $this->view->tmln = $tmln;
        $this->view->lgth = $lgth;
        $this->view->date = $date;
        $this->view->time = $temp['time'];
        $this->view->xszl = $temp['xszl'];
        $this->view->fjzl = $temp['fjzl'];
        $this->view->ydlx = $temp['ydlx'];

        $this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 查询表单（销售报表 - 所有房间）
     */
    public function xsbbSyfjFormAction()
    {
		$this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 销售报表 - 所有房间
     */
    public function xsbbSyfjAction()
    {
        if (($lgth = $this->input('lgth')) == '')
        {
            $this->_forward('xsbb-syfj-form', 'stat', 'master');
            return;
        }

        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], $lgth, $date);
        if (!$tmln)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $time = time();
        $datm = strtotime(date('Y-m-d', $time));

        $temp = array(
            'time' => (int)$time,
            'life' => (int)$this->model('stat')->getStatWait($this->_hostel['h_id'], $lgth),

            'xszl' => 0, // 销售总量
            'fjzl' => 0, // 房间总量
            'syfj' => array(),
        );

        $temp['fjzl'] = (int)$this->model('stat')->calZongFangLiang(
            $this->_hostel['h_id'],
            date('Y-m-d', $tmln[0]),
            date('Y-m-d', $tmln[1])
        );

        $data = $this->model('room')->fetch(array(
            'where' => array('r_hid = ' . $this->_hostel['h_id']),
            'right' => array(
                array(
                    'order',
                    implode(' AND ', array(
                        'r_id = o_rid',
                        'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                        'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                        'o_bdatm < ' . $tmln[1],
                        'o_edatm > ' . $tmln[0]
                    )),
                    array(
                        'SUM(o_price) AS rent',
                        sprintf(
                            'SUM(IF(o_status IN (%d, %d) AND %d > %d, (LEAST(o_edatm, %d, %d)-GREATEST(o_bdatm, %d))/86400, 0)) AS live_past',
                            ORDER_STATUS_ZZ, ORDER_STATUS_YJS, $datm, $tmln[0], $tmln[1]+1, $datm, $tmln[0]
                        ),
                        sprintf(
                            'SUM(IF(o_status=%d OR o_bdatm >= %d,
                            (LEAST(o_edatm, %d)-GREATEST(o_bdatm, %d))/86400,
                            IF(o_edatm>%d AND %d > %d, (LEAST(o_edatm, %d)-GREATEST(o_bdatm, %d, %d))/86400, 0))) AS live_fore',

                            ORDER_STATUS_BL, $datm, $tmln[1]+1, $tmln[0],
                            $datm, $tmln[1], $datm, $tmln[1]+1, $tmln[0], $datm
                        ),
                    )
                ),
            ),
            'group' => 'r_id',
        ));

        if (!empty($data))
        {
            foreach ($data as &$val)
            {
                isset($temp['syfj'][$val['r_id']]) OR $temp['syfj'][$val['r_id']] = array(
                    'name' => $val['r_name'],
                    'type' => $val['r_type'],
                    'rent' => 0,
                    'live' => array(
                        'past' => 0,
                        'fore' => 0,
                    ),
                );

                $temp['xszl'] += ((int)$val['live_past'] + (int)$val['live_fore']);
                $temp['syfj'][$val['r_id']]['rent'] += (int)$val['rent'];
                $temp['syfj'][$val['r_id']]['live']['past'] += (int)$val['live_past'];
                $temp['syfj'][$val['r_id']]['live']['fore'] += (int)$val['live_fore'];
            }
            unset($val, $data);

            // 减去 timeline 外的房费
            $rent = $this->model('order')->fetch(array(
                'where' => array(
                    'o_hid = ' . $this->_hostel['h_id'],
                    'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YQX),
                    'o_status <> ' . $this->model('order')->quote(ORDER_STATUS_YD),
                    'o_bdatm < ' . $tmln[1],
                    'o_edatm > ' . $tmln[0],
                    'o_bdatm < ' . $tmln[0] . ' OR o_edatm > ' . ($tmln[1]+1),
                ),
                'field' => array('o_rid', 'o_bdatm', 'o_edatm', 'o_prices'),
            ));

            if (!empty($rent))
            {
                foreach ($rent as &$val)
                {
                    $val['o_prices'] = json_decode($val['o_prices'], true);

                    $btm = max($val['o_bdatm'], $tmln[0]);
                    $etm = min($val['o_edatm'], $tmln[1]);
                    while ($btm < $etm)
                    {
                        unset($val['o_prices'][$btm]);
                        $btm += 86400;
                    }

                    $temp['syfj'][$val['o_rid']]['rent'] -= (int)array_sum($val['o_prices']);
                }
                unset($rent, $val);
            }
        }

        $this->view->tmln = $tmln;
        $this->view->lgth = $lgth;
        $this->view->date = $date;
        $this->view->time = $temp['time'];
        $this->view->xszl = $temp['xszl'];
        $this->view->fjzl = $temp['fjzl'];
        $this->view->syfj = $temp['syfj'];

        $this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 查询表单（销售报表 - 其它费用）
     */
    public function xsbbQtfyFormAction()
    {
		$this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 销售报表 - 其它费用
     */
    public function xsbbQtfyAction()
    {
        if (($lgth = $this->input('lgth')) == '')
        {
            $this->_forward('xsbb-qtfy-form', 'stat', 'master');
            return;
        }

        $date = $this->input('date');
        Zyon_Util::isDate($date) OR $date = date('Y-m-d');
        $tmln = $this->model('stat')->getStatTimeLine($this->_hostel['h_id'], $lgth, $date);
        if (!$tmln)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $time = time();

        $qtfy = $this->model('bill.expense')->fetch(array(
            'where' => array(
                'be_hid = ' . (int)$this->_hostel['h_id'],
                'be_time >= ' . (int)$tmln[0],
                'be_time <= ' . (int)$tmln[1],
            ),
            'field' => array('be_memo as name', 'SUM(be_sum) as sum'),
            'group' => 'be_memo',
        ));

        $this->view->tmln = $tmln;
        $this->view->lgth = $lgth;
        $this->view->date = $date;
        $this->view->time = $time;
        $this->view->qtfy = $qtfy;

        $this->view->periods = $this->model('stat')->getStatLgth($this->_hostel['h_id']);
    }

    /**
     * 查询表单（结算报表 - 收款渠道对账明细）
     */
    public function jsbbSkqddzmxFormAction()
    {
        $this->view->payments = Zyon_Array::keyto(
            $this->model('hotel.payment')->getPaymentAryByHid($this->_hostel['h_id']),
            'hp_id'
        );
    }

    /**
     * 结算报表 - 收款渠道对账明细
     */
    public function jsbbSkqddzmxAction()
    {
        if (!($date = $this->input('date', 'array')))
        {
            $this->_forward('jsbb-skqddzmx-form', 'stat', 'master');
            return;
        }

        if (!isset($date[0]) || !isset($date[1])
            || !Zyon_Util::isDate($date[0]) || !Zyon_Util::isDate($date[1]))
        {
            $this->flash(0, '查询的日期格式错误');
        }

        $tmln = array();
        $tmln[0] = strtotime($date[0]);
        $tmln[1] = strtotime($date[1])+86400;

        $lgth = ($tmln[1] - $tmln[0])/86400;
        if (!Zyon_Util::isUnsignedInt($lgth) || $lgth > 31 || $lgth < 1)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $tmln[1] -= 1;

        $pyid = $this->input('pyid');
        $pyid == '' AND $pyid = $this->_hostel['h_order_default_payment'];
        $pyls = Zyon_Array::keyto($this->model('hotel.payment')->getPaymentAryByHid($this->_hostel['h_id']), 'hp_id');
        empty($pyls) AND $pyls = array();
        if (!array_key_exists($pyid, $pyls))
        {
            $this->flash(0, '找不到指定的收款渠道');
        }

        $time = time();
        $skqd = $this->model('bill.journal')->fetch(array(
            'where' => array(
                'bj_hid = ' . (int)$this->_hostel['h_id'],
                'bj_pid = ' . (int)$pyid,
                'b_mtime >= ' . (int)$tmln[0],
                'b_mtime <= ' . (int)$tmln[1],
            ),
            'field' => 'SUM(bj_sum) as bj_sum',
            'group' => 'bj_bid',
            'right' => array(
                array('bill', 'bj_bid=b_id', array('*')),
            ),
        ));

        $this->view->time = $time;
        $this->view->tmln = $tmln;
        $this->view->skqd = $skqd;
        $this->view->pyid = $pyid;
        $this->view->pyls = $pyls;
    }

    /**
     * 查询表单（结算报表 - 销售人员对账明细）
     */
    public function jsbbXsrydzmxFormAction()
    {
        $this->view->salemans = Zyon_Array::keyto(
            $this->model('user')->getUserAryByHid($this->_hostel['h_id']),
            'u_id'
        );
    }

    /**
     * 结算报表 - 销售人员对账明细
     */
    public function jsbbXsrydzmxAction()
    {
        if (!($date = $this->input('date', 'array')))
        {
            $this->_forward('jsbb-xsrydzmx-form', 'stat', 'master');
            return;
        }

        if (!isset($date[0]) || !isset($date[1])
            || !Zyon_Util::isDate($date[0]) || !Zyon_Util::isDate($date[1]))
        {
            $this->flash(0, '查询的日期格式错误');
        }

        $tmln = array();
        $tmln[0] = strtotime($date[0]);
        $tmln[1] = strtotime($date[1])+86400;

        $lgth = ($tmln[1] - $tmln[0])/86400;
        if (!Zyon_Util::isUnsignedInt($lgth) || $lgth > 31 || $lgth < 1)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $tmln[1] -= 1;

        $suid = $this->input('suid');
        $suid == '' AND $suid = $this->_hostel['h_order_default_saleman'];
        $suls = Zyon_Array::keyto($this->model('user')->getUserAryByHid($this->_hostel['h_id']), 'u_id');
        empty($suls) AND $suls = array();
        if (!array_key_exists($suid, $suls))
        {
            $this->flash(0, '找不到指定的销售人员');
        }

        $time = time();
        $xsry = $this->model('order')->fetchAry(
            array(
                'o_hid = ' . (int)$this->_hostel['h_id'],
                'o_sid = ' . (int)$suid,
                'o_etime >= ' . (int)$tmln[0],
                'o_etime <= ' . (int)$tmln[1],
                'o_status = ' . (int)ORDER_STATUS_YJS
            )
        );

        $this->view->time = $time;
        $this->view->tmln = $tmln;
        $this->view->suid = $suid;
        $this->view->suls = $suls;
        $this->view->xsry = $xsry;
    }

    /**
     * 查询表单（结算报表 - 预订渠道对账明细）
     */
    public function jsbbYdqddzmxFormAction()
    {
        $this->view->channels = Zyon_Array::keyto(
            $this->model('hotel.channel')->getChannelAryByHid($this->_hostel['h_id']),
            'hc_id'
        );
    }

    /**
     * 结算报表 - 预订渠道对账明细
     */
    public function jsbbYdqddzmxAction()
    {
        if (!($date = $this->input('date', 'array')))
        {
            $this->_forward('jsbb-ydqddzmx-form', 'stat', 'master');
            return;
        }

        if (!isset($date[0]) || !isset($date[1])
            || !Zyon_Util::isDate($date[0]) || !Zyon_Util::isDate($date[1]))
        {
            $this->flash(0, '查询的日期格式错误');
        }

        $tmln = array();
        $tmln[0] = strtotime($date[0]);
        $tmln[1] = strtotime($date[1])+86400;

        $lgth = ($tmln[1] - $tmln[0])/86400;
        if (!Zyon_Util::isUnsignedInt($lgth) || $lgth > 31 || $lgth < 1)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $tmln[1] -= 1;

        $cnid = $this->input('cnid');
        $cnid == '' AND $cnid = $this->_hostel['h_order_default_channel'];
        $cnls = Zyon_Array::keyto($this->model('hotel.channel')->getChannelAryByHid($this->_hostel['h_id']), 'hc_id');
        empty($cnls) AND $cnls = array();
        if (!array_key_exists($cnid, $cnls))
        {
            $this->flash(0, '找不到指定的预订渠道');
        }

        $time = time();
        $ydqd = $this->model('order')->fetchAry(
            array(
                'o_hid = ' . (int)$this->_hostel['h_id'],
                'o_cid = ' . (int)$cnid,
                'o_etime >= ' . (int)$tmln[0],
                'o_etime <= ' . (int)$tmln[1],
                'o_status = ' . (int)ORDER_STATUS_YJS
            )
        );

        $this->view->time = $time;
        $this->view->tmln = $tmln;
        $this->view->cnid = $cnid;
        $this->view->cnls = $cnls;
        $this->view->ydqd = $ydqd;
    }

    /**
     * 查询表单（结算报表 - 预订客人对账明细）
     */
    public function jsbbYdkrdzmxFormAction()
    {
    }

    /**
     * 结算报表 - 预订客人对账明细
     */
    public function jsbbYdkrdzmxAction()
    {
        if (!($date = $this->input('date', 'array')))
        {
            $this->_forward('jsbb-ydkrdzmx-form', 'stat', 'master');
            return;
        }

        if (!isset($date[0]) || !isset($date[1])
            || !Zyon_Util::isDate($date[0]) || !Zyon_Util::isDate($date[1]))
        {
            $this->flash(0, '查询的日期格式错误');
        }

        $tmln = array();
        $tmln[0] = strtotime($date[0]);
        $tmln[1] = strtotime($date[1])+86400;

        $lgth = ($tmln[1] - $tmln[0])/86400;
        if (!Zyon_Util::isUnsignedInt($lgth) || $lgth > 31 || $lgth < 1)
        {
            $this->flash(0, '选择的日期超出了可查询的范围');
        }

        $tmln[1] -= 1;

        if (($bknm = $this->input('bknm')) == '')
        {
            $this->flash(0, '预订客人姓名不能为空');
        }

        $time = time();
        $ydkr = $this->model('order')->getIndexOrderAry(
            array(
                'o_hid = ' . (int)$this->_hostel['h_id'],
                'o_etime >= ' . (int)$tmln[0],
                'o_etime <= ' . (int)$tmln[1],
                'o_status = ' . (int)ORDER_STATUS_YJS,
            ),
            array('type' => HOTEL_GUEST_TYPE_BOOK, 'name' => $bknm)
        );

        $this->view->time = $time;
        $this->view->tmln = $tmln;
        $this->view->bknm = $bknm;
        $this->view->ydkr = $ydkr;
    }
}
// End of file : StatController.php
