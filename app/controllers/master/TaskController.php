<?php
/**
 * @version    $Id$
 */
class Master_TaskController extends MasterController
{
    /**
     * 待办事项提醒
     */
    public function indexAction()
    {
        $ctime = time();

        if (!(($task = $this->cache()->load($hash = md5(__CLASS__ . "{$this->_hostel['h_id']}.NOTE.PAGE:")))
            && isset($task['time']) && isset($task['life']) && isset($task['data']) && is_array($task['data'])
            && $task['time'] + $task['life'] > $ctime))
        {
            $task = array(
                'time' => (int)$ctime,
                'life' => $this->model('task')->getTodoWaitByHotel($this->_hostel),
                'data' => array(),
            );

            $dtime = $this->model('task')->getTodoDaysByHotel($this->_hostel)*86400;
            $btime = $ctime - $dtime;
            $etime = $ctime + $dtime;

            $orders = $this->model('order')->getRealTimeStateOrderAryByHid($this->_hostel['h_id'], $btime, $etime, $ctime);
            if (!empty($orders))
            {
                $uqid = 0;
                foreach ($orders as $order)
                {
                    $codes = $this->model('order')->getRealTimeStateCodes($order, $this->_hostel);

                    foreach ($codes as $rtsta)
                    {
                        $uqid++;

                        $item = array(
                            'done' => 0,
                            'href' => '/master/order/detail?oid=' . $order['o_id'],
                            'info' => $this->view->escape(__('房间%s的%d号订单 %s',
                                $order['o_room'],
                                $order['o_id'],
                                getOrderRealTimeStatusNameByCode($rtsta)
                            )),
                        );

                        $task['data']["o{$uqid}"] = $item;
                    }
                }
            }

            $obills = $this->model('bill')->getRealTimeStateBillAryByHid($this->_hostel['h_id'], $btime, $etime, $ctime);
            if (!empty($obills))
            {
                $uqid = 0;
                foreach ($obills as $obill)
                {
                    $codes = $this->model('bill')->getRealTimeStateCodes($obill, $this->_hostel);

                    foreach ($codes as $rtsta)
                    {
                        $uqid++;

                        $item = array(
                            'done' => 0,
                            'href' => '/master/bill/detail?bid=' . $obill['b_id'],
                            'info' => $this->view->escape(__('%d号账单%s %s',
                                $obill['b_id'],
                                $obill['b_name'],
                                getBillRealTimeStatusNameByCode($rtsta)
                            )),
                        );

                        $task['data']["b{$uqid}"] = $item;
                    }
                }
            }

            $this->cache()->save($task, $hash);
        }

        if ($this->input('ct') === 'json')
        {
            $this->flash(1, array('context' => $task));
        }

        $this->view->task = $task;
    }

    /**
     * 标识任务已执行
     */
    public function doneAction()
    {
        $code = $this->input('code');
        if (empty($code))
        {
            $this->flash(0, '任务识别码是必须的');
        }

        $ctime = time();
        if (($task = $this->cache()->load($hash = md5(__CLASS__ . "{$this->_hostel['h_id']}.NOTE.PAGE:")))
            && isset($task['time']) && isset($task['life']) && isset($task['data']) && is_array($task['data'])
            && $task['time'] + $task['life'] > $ctime
            && isset($task['data'][$code]) && !$task['data'][$code]['done'])
        {
            $task['data'][$code]['done'] = 1;
            $this->cache()->save($task, $hash);
        }

        $this->flash(1);
    }
}
// End of file : TaskController.php
