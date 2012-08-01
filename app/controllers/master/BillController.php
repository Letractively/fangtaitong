<?php
/**
 * @version    $Id$
 */
class Master_BillController extends MasterController
{
    /**
     * 账单列表
     */
    public function indexAction()
    {
        $where = array(
            'b_hid = ' . $this->model('bill')->quote($this->_master['u_hid']),
        );

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
        in_array($query['type'], array('bill', 'name'), true) OR $query['type'] = $query['name'] = null;

        $query['bdate'] = $this->input('bdate');
        $query['edate'] = $this->input('edate');

        if ($query['bdate'] != '')
        {
            if (!Zyon_Util::isDate($query['bdate']))
            {
                $this->flash(0, '查询起始时间错误');
            }

            $btime = strtotime($query['bdate']);
            $where[] = 'b_ctime >= ' . $this->model('bill')->quote($btime);
        }

        if ($query['edate'] != '')
        {
            if (!Zyon_Util::isDate($query['edate']))
            {
                $this->flash(0, '查询结束时间错误');
            }

            $etime = strtotime($query['edate']) + 86399;
            if (isset($btime) && $etime <= $btime)
            {
                $this->flash(0, '查询时间范围错误');
            }

            $where[] = 'b_ctime <= ' . $this->model('bill')->quote($etime);
        }

        if ($query['name'] != '' && $query['type'] != '')
        {
            if ($query['type'] == 'bill')
            {
                $where[] = 'b_id = ' . $this->model('bill')->quote($query['name']);
            }
            else if ($query['type'] == 'name')
            {
                $where[] = 'b_name = ' . $this->model('bill')->quote($query['name']);
            }
        }

        $query['money'] = $this->input('money', 'array') ?: array();
        if (!empty($query['money']))
        {
            $where['money'] = array();
            foreach ($query['money'] as $money)
            {
                switch ($money)
                {
                case '0':
                    $where['money'][] = 'b_paid = 0';
                    break;

                case '>':
                    $where['money'][] = '(b_cost - b_paid > 0 AND b_paid > 0)';
                    break;

                case '=':
                    $where['money'][] = 'b_cost - b_paid = 0';
                    break;

                case '<':
                    $where['money'][] = 'b_cost - b_paid < 0';
                    break;

                default :
                    $this->flash(0, '付款情况错误');
                    break;
                }
            }

            if (!empty($where['money']))
            {
                $where[] = implode(' OR ', $where['money']);
            }
            unset($where['money']);
        }

        $query['setms'] = $this->input('setms', 'array');
        $query['setms'] OR $query['setms'] = array();
        foreach ($query['setms'] as $stmid)
        {
            if (!is_numeric($stmid))
            {
                $this->flash(0, '结算方式错误');
            }
        }
        $query['setms'] = array_unique($query['setms']);

        if (!empty($query['setms']))
        {
            $where[] = 'b_sid IN (' . implode(',', array_map(array($this->model('bill'), 'quote'), $query['setms'])) . ')';
        }

        $query['rtsta'] = $this->input('rtsta', 'array');
        $query['rtsta'] OR $query['rtsta'] = array();
        $query['rtsta'] = array_unique($query['rtsta']);

        if (!empty($query['rtsta']))
        {
            $rtstas = $this->model('bill')->getRealTimeStateConds($this->_hostel, $query['rtsta']);
            if (!empty($rtstas))
            {
                $where[] = implode(' OR ', $rtstas);
            }
        }

        $query['state'] = $this->input('state', 'array');
        $query['state'] OR $query['state'] = array();
        foreach ($query['state'] as $state)
        {
            if (!$this->model('bill')->getStateNameByCode($state))
            {
                $this->flash(0, '账单状态错误');
            }
        }
        $query['state'] = array_unique($query['state']);

        if (!empty($query['state']))
        {
            $where[] = 'b_status IN (' . implode(',', array_map(array($this->model('bill'), 'quote'), $query['state'])) . ')';
        }

        $bills = Zyon_Array::keyto(
            $this->model('bill')->fetchAry(
                $where,
                'b_id DESC',
                array($pager['qnty'], $pager['qnty']*($pager['page'] - 1))
            ),
            'b_id'
        );
        empty($bills) AND $bills = array();

        if (!empty($bills))
        {
            foreach ($bills as &$bill)
            {
                $bill['state'] = $this->model('bill')->getStateNameByCode($bill['b_status']);
                $bill['rtsta'] = $this->model('bill')->getRealTimeStateNames($bill, $this->_hostel);
            }
            unset($bill);
        }

        $pager['list'] = count($bills);
        $pager['args'] = $query;

        $this->view->bills = $bills;
        $this->view->rtsta = $this->model('bill')->getRtstas();
        $this->view->state = $this->model('bill')->getStatus();
        $this->view->setms = $this->model('hotel.settlem')->getSettlemAryByHid($this->_hostel['h_id']);
        $this->view->pager = $pager;
        $this->view->query = $query;
    }

    /**
     * 账单详情
     */
    public function detailAction()
    {
        $bill = $this->loadUsableBill($bid = $this->input('bid'));
        $bill['state'] = $this->model('bill')->getStateNameByCode($bill['b_status']);
        $bill['rtsta'] = $this->model('bill')->getRealTimeStateNames($bill, $this->_hostel);

        $orders = Zyon_Array::keyto($this->model('order')->getOrderAryByBid($bid), 'o_id');

        $this->view->bill    = $bill;
        $this->view->stms    = $this->model('hotel.settlem')->getUsableSettlemAryByHid($this->_hostel['h_id']);
        $this->view->cnns    = $this->model('hotel.payment')->getUsablePaymentAryByHid($this->_hostel['h_id']);
        $this->view->orders  = $orders;
        $this->view->action  = $this->model('bill')->getUsableActions($bill, $this->_hostel);
        $this->view->expense = $this->model('bill.expense')->getExpenseAryByBid($bid);
        $this->view->journal = $this->model('bill.journal')->getJournalAryByBid($bid);
    }

    /**
     * 执行操作
     */
    public function doActionAction()
    {
        $bill = $this->loadUsableBill($this->input('bid'));
        if ($this->input('key') !== $bill['b_mtime'])
        {
            $this->flash(0, '表单已过期，请刷新重试');
        }

        if ($this->input('sta', 'numeric') !== $bill['b_status'])
        {
            $this->flash(0, '账单状态错误或者有冲突');
        }

        $act = $this->input('act');
        if (!array_key_exists($act, $acts = $this->model('bill')->getUsableActions($bill, $this->_hostel)))
        {
            $this->flash(0, '账单当前不允许执行指定操作');
        }

        $func = 'doAction' . strtoupper($act);
        method_exists($this, $func) AND $this->$func($bill);
        $this->flash(0);
    }

    /**
     * 执行操作：其它费用
     */
    public function doActionQtfy($bill)
    {
        $maps = $this->fetchBillCost('cost', $cost);

        $this->model('bill')->dbase()->beginTransaction();
        try
        {
            if (!empty($maps))
            {
                foreach ($maps as &$val)
                {
                    $val['be_bid'] = $bill['b_id'];
                    if (!$this->model('bill.expense')->addExpense($val)) throw new exception('创建应收明细失败');
                }
                unset($val);
            }

            if (!$this->model('bill')->modBill($bill['b_id'], array(
                'b_cost' => $this->model('bill')->expr('b_cost + ' . (int)$cost),
            )))
            {
                throw new exception('更新账单信息失败');
            }

            $this->model('bill')->dbase()->commit();

            if ($bill_new = $this->model('bill')->getBill($bill['b_id']))
            {
                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog(
                        $this->_master,
                        $bill, $bill_new, __('增加账单应收 %d', $cost/100)
                    )
                );
            }

            $this->flash(1);
        }
        catch (Exception $e)
        {
            $this->model('bill')->dbase()->rollBack();
            $this->error($e);
        }
    }

    /**
     * 执行操作：收款退款
     */
    public function doActionSktk($bill)
    {
        $maps = $this->fetchBillPaid('paid', $paid);

        $this->model('bill')->dbase()->beginTransaction();
        try
        {
            if (!empty($maps))
            {
                foreach ($maps as &$val)
                {
                    $val['bj_bid'] = $bill['b_id'];
                    if (!$this->model('bill.journal')->addJournal($val)) throw new exception('创建已收明细失败');
                }
                unset($val);
            }

            if (!$this->model('bill')->modBill($bill['b_id'], array(
                'b_paid' => $this->model('bill')->expr('b_paid + ' . (int)$paid),
            )))
            {
                throw new exception('更新账单信息失败');
            }

            $this->model('bill')->dbase()->commit();

            if ($bill_new = $this->model('bill')->getBill($bill['b_id']))
            {
                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog(
                        $this->_master,
                        $bill, $bill_new, __('增加账单已收 %d', $paid/100)
                    )
                );
            }

            $this->flash(1);
        }
        catch (Exception $e)
        {
            $this->model('bill')->dbase()->rollBack();
            $this->error($e);
        }
    }

    /**
     * 执行操作：修改备注
     */
    public function doActionXgbz($bill)
    {
        $memo = $this->input('memo');

        if ($this->model('bill')->modBill($bill['b_id'], array(
            'b_memo' => $memo,
        )))
        {
            if ($bill_new = $this->model('bill')->getBill($bill['b_id']))
            {
                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog($this->_master, $bill, $bill_new)
                );
            }

            $this->flash(1, '修改账单备注成功');
        }
    }

    /**
     * 执行操作：结算方式
     */
    public function doActionJsfs($bill)
    {
        $settlem = $this->loadUsableSettlem($this->input('sid'));
        if ($settlem['hs_id'] === $bill['b_sid'])
        {
            $this->flash(0, '选择的结算方式与原结算方式相同');
        }

        if ($this->model('bill')->modBill($bill['b_id'], array(
            'b_sid' => $settlem['hs_id'],
            'b_snm' => $settlem['hs_name'],
        )))
        {
            if ($bill_new = $this->model('bill')->getBill($bill['b_id']))
            {
                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog(
                        $this->_master, $bill, $bill_new,
                        __('结算方式 %s=>%s', $bill['b_snm'], $bill_new['b_snm'])
                    )
                );
            }

            $this->flash(1, '修改账单结算方式成功');
        }
    }

    /**
     * 执行操作：过期时间
     */
    public function doActionGqsj($bill)
    {
        $gqsj = $this->input('gqsj', 'numeric');
        if ($gqsj)
        {
            $date = $this->input('date');
            $hour = $this->input('hour', 'numeric');
            $minu = $this->input('minu', 'numeric');

            isset($hour[1]) OR $hour = '0' . $hour;
            isset($minu[1]) OR $minu = '0' . $minu;

            if (!Zyon_Util::isDate($date) || !Zyon_Util::isTime($hour . ':' . $minu))
            {
                $this->flash(0, '过期时间格式错误');
            }

            $time = strtotime($date . ' ' . $hour . ':' . $minu);
            if ($time < $bill['b_ctime']-59)
            {
                $this->flash(0, __('过期时间不能早于账单创建时间%s',
                    date('Y-m-d H:i', $bill['b_ctime'])
                ));
            }
        }
        else
        {
            if (!($bill['b_attr'] & (int)BILL_ATTR_GQTX))
            {
                $this->flash(0, '没有变更');
            }

            $time = $bill['b_ltime'];
        }

        if ($this->model('bill')->modBill($bill['b_id'], array(
            'b_attr'  => $this->model('bill')->expr($gqsj ? ('b_attr | ' . BILL_ATTR_GQTX) : ('b_attr & ' . ~(int)BILL_ATTR_GQTX)),
            'b_ltime' => $time,
        )))
        {
            if ($bill_new = $this->model('bill')->getBill($bill['b_id']))
            {
                $memo = $gqsj ? (
                    ($bill['b_attr'] & (int)BILL_ATTR_GQTX) && is_numeric($bill['b_ltime'])
                    ? __('过期时间：%s=>%s', date('Y-m-d H:i', $bill['b_ltime']), date('Y-m-d H:i', $bill_new['b_ltime']))
                    : __('过期时间：%s', date('Y-m-d H:i', $bill_new['b_ltime']))
                ) : '取消账单过期时间设定';

                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog($this->_master, $bill, $bill_new, $memo)
                );
            }

            $this->flash(1, '修改账单过期时间成功');
        }
    }

    /**
     * 执行操作：关闭账单
     */
    public function doActionGbzd($bill)
    {
        $this->checkUserPerm('/master/bill/do-locked');

        if ($this->model('bill')->modBill($bill['b_id'], array(
            'b_status' => BILL_STATUS_GB,
        )))
        {
            if ($bill_new = $this->model('bill')->getBill($bill['b_id']))
            {
                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog($this->_master, $bill, $bill_new, __('关闭账单'))
                );
            }

            $this->flash(1, '关闭账单成功');
        }
    }

    /**
     * 执行操作：开放账单
     */
    public function doActionKfzd($bill)
    {
        $this->checkUserPerm('/master/bill/do-unlock');

        if ($this->model('bill')->modBill($bill['b_id'], array(
            'b_status' => BILL_STATUS_KF,
        )))
        {
            if ($bill_new = $this->model('bill')->getBill($bill['b_id']))
            {
                $this->model('log.bill')->addLog(
                    $this->model('log.bill')->getNewUpdateLog($this->_master, $bill, $bill_new, __('开放账单'))
                );
            }

            $this->flash(1, '开放账单成功');
        }
    }

    /**
     * fetchBillCost
     * 
     * @param string $input
     * @param int    $total
     * @return array
     */
    public function fetchBillCost($input, &$total)
    {
        /**
         * 处理账单应收
         */
        $bill = $this->input($input, 'array');
        if (empty($bill))
        {
            $this->flash(0, '应收款项错误');
        }

        empty($total) AND $total = 0;

        foreach ($bill as $key => $val)
        {
            if (!is_array($val) || !isset($val['qnty']) || !is_string($val['qnty']) || trim($val['qnty']) == '')
            {
                unset($bill[$key]);
                continue;
            }

            if (!Zyon_Util::isMoneyFloat($val['qnty']) || $val['qnty'] < 0
                || !isset($val['memo']) || !is_string($val['memo'])
            )
            {
                $this->flash(0, '应收款项错误');
            }

            $val['qnty'] = (empty($val['oper']) ? $val['qnty'] : -$val['qnty']) * 100;
            $val['memo'] = trim($val['memo']);

            if (mb_strlen($val['memo']) > 200)
            {
                $this->flash(0, '备注内容不能超过200个字符');
            }

            if ($val['qnty'] < 0)
            {
                $this->checkUserPerm('/master/bill/do-discount-expense');
            }

            $total += (int)($val['qnty']);
            if ($total >= 10000000)
            {
                $this->flash(0, '应收总金额超出系统限制，必须小于100000');
            }

            if ($total <= -10000000)
            {
                $this->flash(0, '应收总金额超出系统限制，必须大于-100000');
            }

            $bill[$key] = $this->model('bill.expense')->getNewExpense(
                0,
                $this->_hostel['h_id'],
                $this->_master['u_id'],
                $val['qnty'],
                $this->_master['u_realname'],
                $val['memo']
            );
        }
        $total = (string)$total;

        if (empty($bill))
        {
            $this->flash(0, '没有可用的应收款项记录');
        }

        return array_values($bill);
    }

    /**
     * fetchBillPaid
     * 
     * @param string $input
     * @param int    $total
     * @return array
     */
    public function fetchBillPaid($input, &$total)
    {
        /**
         * 处理账单已收
         */
        $bill = $this->input($input, 'array');
        if (empty($bill))
        {
            $this->flash(0, '已收款项错误');
        }

        empty($total) AND $total = 0;

        $froms = Zyon_Array::keyto(
            $this->model('hotel.payment')->getUsablePaymentAryByHid($this->_hostel['h_id']), 'hp_id'
        );
        empty($froms) AND $froms = array();

        foreach ($bill as $key => $val)
        {
            if (!is_array($val) || !isset($val['qnty']) || !is_string($val['qnty']) || trim($val['qnty']) == '')
            {
                unset($bill[$key]);
                continue;
            }

            if (!Zyon_Util::isUnsignedInt($val['from'])
                || !Zyon_Util::isMoneyFloat($val['qnty']) || $val['qnty'] < 0
                || !Zyon_Util::isDate($val['date'])
                || !isset($val['memo']) || !is_string($val['memo'])
            )
            {
                $this->flash(0, '已收款项错误');
            }

            if (!array_key_exists($val['from'], $froms))
            {
                $this->flash(0, '已收款项渠道错误');
            }

            $val['qnty'] = (empty($val['oper']) ? $val['qnty'] : -$val['qnty']) * 100;
            $val['memo'] = trim($val['memo']);

            if (mb_strlen($val['memo']) > 200)
            {
                $this->flash(0, '备注内容不能超过200个字符');
            }

            $total += (int)($val['qnty']);
            if ($total >= 10000000)
            {
                $this->flash(0, '已收总金额超出系统限制，必须小于100000');
            }

            if ($total <= -10000000)
            {
                $this->flash(0, '已收总金额超出系统限制，必须大于-100000');
            }

            $bill[$key] = $this->model('bill.journal')->getNewJournal(
                0,
                $this->_hostel['h_id'],
                $this->_master['u_id'],
                $val['from'],
                $val['qnty'],
                strtotime($val['date']),
                $this->_master['u_realname'],
                $froms[$val['from']]['hp_name'],
                $val['memo']
            );
        }
        $total = (string)$total;

        if (empty($bill))
        {
            $this->flash(0, '没有可用的已收款项记录');
        }

        return array_values($bill);
    }
}
// End of file : BillController.php
