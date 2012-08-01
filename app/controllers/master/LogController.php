<?php
/**
 * @version    $Id$
 */
class Master_LogController extends MasterController
{
    /**
     * 用户日志
     */
    public function userAction()
    {
        $this->initQuery();

        $user = $this->loadUsableUser($this->input('uid'));
        $this->view->user = $user;

        $where = array(
            'lu_xid = ' . $this->model('log.user')->quote($user['u_id']),
        );

        if ($this->view->query['bdate'])
        {
            $where[] = 'lu_date >= ' . $this->model('log.user')->quote($this->view->query['bdate']);
        }

        if ($this->view->query['edate'])
        {
            $where[] = 'lu_date <= ' . $this->model('log.user')->quote($this->model('log.user')->getDateTime(strtotime($this->view->query['edate'])+86399));
        }

        $logs = $this->model('log.user')->fetch(array(
            'where' => $where,
            'order' => 'lu_id DESC',
            'limit' => array($this->view->pager['qnty'], $this->view->pager['qnty']*($this->view->pager['page']-1))
        ));
        empty($logs) AND $logs = array();

        $this->view->logs = $logs;
        $this->view->pager['list'] = count($logs);
    }

    /**
     * 账单日志
     */
    public function billAction()
    {
        $this->initQuery();

        $bill = $this->loadUsableBill($this->input('bid'));
        $this->view->bill = $bill;

        $where = array(
            'lb_xid = ' . $this->model('log.bill')->quote($bill['b_id']),
        );

        if ($this->view->query['bdate'])
        {
            $where[] = 'lb_date >= ' . $this->model('log.bill')->quote($this->view->query['bdate']);
        }

        if ($this->view->query['edate'])
        {
            $where[] = 'lb_date <= ' . $this->model('log.bill')->quote($this->model('log.bill')->getDateTime(strtotime($this->view->query['edate'])+86399));
        }

        $logs = $this->model('log.bill')->fetch(array(
            'where' => $where,
            'order' => 'lb_id DESC',
            'limit' => array($this->view->pager['qnty'], $this->view->pager['qnty']*($this->view->pager['page']-1))
        ));
        empty($logs) AND $logs = array();

        $this->view->logs = $logs;
        $this->view->pager['list'] = count($logs);
    }

    /**
     * 房间日志
     */
    public function roomAction()
    {
        $this->initQuery();

        $room = $this->loadUsableRoom($this->input('rid'));
        $this->view->room = $room;

        $where = array(
            'lr_xid = ' . $this->model('log.room')->quote($room['r_id']),
        );

        if ($this->view->query['bdate'])
        {
            $where[] = 'lr_date >= ' . $this->model('log.room')->quote($this->view->query['bdate']);
        }

        if ($this->view->query['edate'])
        {
            $where[] = 'lr_date <= ' . $this->model('log.room')->quote($this->model('log.room')->getDateTime(strtotime($this->view->query['edate'])+86399));
        }

        $logs = $this->model('log.room')->fetch(array(
            'where' => $where,
            'order' => 'lr_id DESC',
            'limit' => array($this->view->pager['qnty'], $this->view->pager['qnty']*($this->view->pager['page']-1))
        ));
        empty($logs) AND $logs = array();

        $this->view->logs = $logs;
        $this->view->pager['list'] = count($logs);
    }

    /**
     * 订单日志
     */
    public function orderAction()
    {
        $this->initQuery();

        $order = $this->loadUsableOrder($this->input('oid'));
        $this->view->order = $order;

        $where = array(
            'lo_xid = ' . $this->model('log.order')->quote($order['o_id']),
        );

        if ($this->view->query['bdate'])
        {
            $where[] = 'lo_date >= ' . $this->model('log.order')->quote($this->view->query['bdate']);
        }

        if ($this->view->query['edate'])
        {
            $where[] = 'lo_date <= ' . $this->model('log.order')->quote($this->model('log.order')->getDateTime(strtotime($this->view->query['edate'])+86399));
        }

        $logs = $this->model('log.order')->fetch(array(
            'where' => $where,
            'order' => 'lo_id DESC',
            'limit' => array($this->view->pager['qnty'], $this->view->pager['qnty']*($this->view->pager['page']-1))
        ));
        empty($logs) AND $logs = array();

        $this->view->logs = $logs;
        $this->view->pager['list'] = count($logs);
    }

    /**
     * initQuery
     * 
     * @return void
     */
    public function initQuery()
    {
        $pager = $query = array();

        $pager['qnty'] = (int)$this->input('qnty', 'numeric');
        empty($pager['qnty']) AND $pager['qnty'] = 30;
        $pager['qnty'] = $query['qnty'] = min($pager['qnty'], 30);

        $pager['page'] = (int)$this->input('page', 'numeric');
        empty($pager['page']) AND $pager['page'] = 1;

        $query['bdate'] = $this->input('bdate', 'string');
        Zyon_Util::isDate($query['bdate']) OR $query['bdate'] = null;

        $query['edate'] = $this->input('edate', 'string');
        Zyon_Util::isDate($query['edate']) OR $query['edate'] = null;

        if ($query['bdate'] && $query['edate'])
        {
            $btime = strtotime($query['bdate']);
            $etime = strtotime($query['edate']);
            if ($etime < $btime)
            {
                $this->flash(0, '查询时间范围错误');
            }
        }

        $pager['args'] = $query;

        $this->view->query = $query;
        $this->view->pager = $pager;
    }
}
// End of file : LogController.php
