<?php
/**
 * @version    $Id$
 */
class Master_PaymentController extends MasterController
{
    /**
     * 收款渠道管理
     */
    public function indexAction()
    {
        $this->view->payment = $this->model('hotel.payment')->getPaymentAryByHid($this->_hostel['h_id']);
    }

    /**
     * 查询旅店当前可用收款渠道
     */
    public function validPnameAction()
    {
        $pname = $this->input('name');
        if ($pname != '' && mb_strlen($pname) <= 30 && $this->model('hotel.payment')->chkCanCreatePayment($this->_hostel['h_id'], $pname))
        {
            exit(json_encode(true));
        }

        exit(json_encode(false));
    }

    /**
     * 获取可用收款渠道列表
     */
    public function fetchAbledIndexAction()
    {
        if ($index = Zyon_Array::keyto($this->model('hotel.payment')->getUsablePaymentAryByHid($this->_hostel['h_id']), 'hp_id'))
        {
            foreach ($index as $key => $val)
            {
                $index[$key] = $val['hp_name'];
            }

            $this->flash(1, array('context' => $index));
        }

        $this->flash(1, array('context' => array()));
    }

    /**
     * 创建类型
     */
    public function createAction()
    {
    }

    /**
     * 执行创建收款渠道操作
     */
    public function doCreateAction()
    {
        $name = $this->input('name');
        $memo = $this->input('memo');
        $stat = (int)$this->input('stat');

        if ($name == '')
        {
            $this->flash(0, '收款渠道名称不能为空');
        }

        if (mb_strlen($name) > 30)
        {
            $this->flash(0, '收款渠道名称不能超过30个字符');
        }

        if (mb_strlen($memo) > 200)
        {
            $this->flash(0, '收款渠道说明不能超过200个字符');
        }

        if (!$this->model('hotel.payment')->chkCanCreatePayment($this->_hostel['h_id'], $name))
        {
            $this->flash(0, '收款渠道已存在');
        }

        if ($hpid = $this->model('hotel.payment')->addPayment(
            $this->model('hotel.payment')->getNewPayment($this->_hostel['h_id'], $stat, $name, $memo)
        ))
        {
            $this->flash($hpid, array(
                'timeout' => 10,
                'forward' => "/master/payment/",
                'message' => '创建收款渠道成功',
                'content' => array(
                    __("查看收款渠道列表？请<a href='%s'>点击这里</a>", "/master/payment/"),
                    __("修改收款渠道信息？请<a href='%s'>点击这里</a>", "/master/payment/update?hpid={$hpid}"),
                    __("继续创建收款渠道？请<a href='%s'>点击这里</a>", '/master/payment/create'),
                ),
            ));
        }

        $this->flash(0);
    }

    /**
     * 更新收款渠道
     */
    public function updateAction()
    {
        $payment = $this->loadUsablePayment($this->input('hpid'));
        $this->view->payment = $payment;
    }

    /**
     * 执行更新收款渠道操作
     */
    public function doUpdateAction()
    {
        $payment = $this->loadUsablePayment($hpid = $this->input('hpid'));
        $stat = (int)$this->input('stat');
        $memo = $this->input('memo');

        if (!$stat && $payment['hp_id'] == $this->_hostel['h_order_default_payment'])
        {
            $this->flash(0, '默认收款渠道不允许停用');
        }

        if (mb_strlen($memo) > 200)
        {
            $this->flash(0, '收款渠道说明不能超过200个字符');
        }

        if ($this->model('hotel.payment')->modPayment($hpid, array(
            'hp_status' => $stat,
            'hp_memo' => $memo
        )))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 执行更新收款渠道状态操作
     */
    public function doUpdateStatusAction()
    {
        $payment = $this->loadUsablePayment($hpid = $this->input('hpid'));
        $stat = (int)$this->input('stat');

        if (!$stat && $payment['hp_id'] == $this->_hostel['h_order_default_payment'])
        {
            $this->flash(0, '默认收款渠道不允许停用');
        }

        if ($this->model('hotel.payment')->modPayment($hpid, array(
            'hp_status' => $stat,
        )))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 执行设为默认值操作
     */
    public function doLocateAction()
    {
        $payment = $this->loadUsablePayment($hpid = $this->input('hpid'));
        if (!$payment['hp_status'])
        {
            $this->flash(0, array(
                'message' => '该收款渠道当前为停用状态，不能设置为默认值',
                'content' => __('尝试<a href="/master/payment/do-update?hpid=%d&stat=1">启用该收款渠道并设置为默认值</a>', $payment['hp_id'])
            ));
        }

        if ($this->model('hotel')->modHotel($this->_hostel['h_id'], array('h_order_default_payment' => $payment['hp_id'])))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }
}
// End of file : PaymentController.php
