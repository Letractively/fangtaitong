<?php
/**
 * @version    $Id$
 */
class Process_SendRoomStatMail extends Process
{
    /**
     * _idx
     * 
     * @var int
     */
    protected $_idx;

    /**
     * _all
     * 
     * @var int
     */
    protected $_all;

    /**
     * __construct
     * 
     * @param int $idx
     * @param int $all
     * @return mixed
     */
    public function __construct($idx, $all)
    {
        $this->_idx = $idx;
        $this->_all = $all;

        if (!Zyon_Util::isUnsignedInt($idx) || !Zyon_Util::isUnsignedInt($all))
        {
            exit($this->flash(1, "Falied to init SendRoomStatMail: Invalid Arguments[{$idx}, {$all}]"));
        }
    }

    /**
     * _execute
     * 
     * @return mixed
     */
    protected function _execute()
    {
        $success = 0;

        $hids = $this->model('hotel')->fetch(array(
            'where' => array('h_id%' . $this->_all . '=' . $this->_idx),
            'field' => 'h_id'
        ));

        if (!empty($hids))
        {
            $ctime = time();
            $cdate = date('Y-m-d', $ctime);

            while ($hid = array_shift($hids))
            {
                $hotel = $this->model('hotel')->getHotel($hid = $hid['h_id']);
                $dtime = $this->model('task')->getTodoDaysByHotel($hotel)*86400;
                $btime = $ctime - $dtime;
                $etime = $ctime + $dtime;

                /**
                 * 统计待办事项
                 */
                $tasks = 0;
                $order = $this->model('order')->getRealTimeStateOrderAryByHid($hid, $btime, $etime, $ctime);
                if (!empty($order))
                {
                    foreach ($order as $order)
                    {
                        $tasks += sizeof($this->model('order')->getRealTimeStateCodes($order, $hotel));
                    }
                }

                /**
                 * 统计旅店入住率
                 */
                $livep = $this->model('stat')->calDateStat($cdate, $hotel['h_id'], HSTAT_CLASS_HOTEL_RZL);
                $livep = empty($livep[HSTAT_CLASS_HOTEL_RZL]['datas'][0]['value']) ? 0 : $livep[HSTAT_CLASS_HOTEL_RZL]['datas'][0]['value'];

                /**
                 * 增加发送整体入住率、待办事项提醒邮件的任务
                 */
                $email = $this->model('mail')->getTpl('stat-room', array(
                    'hotel' => $hotel,
                    'tasks' => $tasks,
                    'livep' => $livep,
                ));

                $this->model('mail.job')->addJob($this->model('mail.job')->getNewJob(
                    $hotel['h_name'],
                    $hotel['h_email'],
                    __(sprintf('房态日报[%s]', date('c', strtotime($cdate)))),
                    $email,
                    __('房态通系统邮件')
                ));

                $success++;
            }
        }

        return $this->flash(0, "SendRoomStatMail Done! Success:" . (int)$success);
    }
}
// End of file : SendRoomStatMail.php
