<?php
/**
 * @version    $Id$
 */
class Master_ChannelController extends MasterController
{
    /**
     * 预订渠道管理
     */
    public function indexAction()
    {
        $this->view->channel = $this->model('hotel.channel')->getChannelAryByHid($this->_hostel['h_id']);
    }

    /**
     * 查询旅店当前可用预订渠道名接口
     */
    public function validCnameAction()
    {
        $cname = $this->input('name');
        if ($cname != '' && mb_strlen($cname) <= 30 && $this->model('hotel.channel')->chkCanCreateChannel($this->_hostel['h_id'], $cname))
        {
            $this->close(json_encode(true));
        }

        $this->close(json_encode(false));
    }

    /**
     * 获取可用预订渠道列表
     */
    public function fetchAbledIndexAction()
    {
        if ($index = Zyon_Array::keyto($this->model('hotel.channel')->getUsableChannelAryByHid($this->_hostel['h_id']), 'hc_id'))
        {
            foreach ($index as $key => $val)
            {
                $index[$key] = $val['hc_name'];
            }

            $this->flash(1, array('context' => $index));
        }

        $this->flash(1, array('context' => array()));
    }

    /**
     * 创建预订渠道
     */
    public function createAction()
    {
    }

    /**
     * 执行创建预订渠道操作
     */
    public function doCreateAction()
    {
        $name = $this->input('name');
        $memo = $this->input('memo');
        $stat = (int)$this->input('stat');

        if ($name == '')
        {
            $this->flash(0, '预订渠道名称不能为空');
        }

        if (mb_strlen($name) > 30)
        {
            $this->flash(0, '预订渠道名称不能超过30个字符');
        }

        if (mb_strlen($memo) > 200)
        {
            $this->flash(0, '预订渠道说明不能超过200个字符');
        }

        if (!$this->model('hotel.channel')->chkCanCreateChannel($this->_hostel['h_id'], $name))
        {
            $this->flash(0, '预订渠道已存在');
        }

        if ($hcid = $this->model('hotel.channel')->addChannel(
            $this->model('hotel.channel')->getNewChannel($this->_hostel['h_id'], $stat, $name, $memo)
        ))
        {
            $this->flash($hcid, array(
                'timeout' => 10,
                'forward' => "/master/channel/",
                'message' => '创建预订渠道成功',
                'content' => array(
                    __("查看预订渠道列表？请<a href='%s'>点击这里</a>", "/master/channel/"),
                    __("修改预订渠道信息？请<a href='%s'>点击这里</a>", "/master/channel/update?hcid={$hcid}"),
                    __("继续创建预订渠道？请<a href='%s'>点击这里</a>", '/master/channel/create'),
                ),
            ));
        }

        $this->flash(0);
    }

    /**
     * 更新预订渠道
     */
    public function updateAction()
    {
        $channel = $this->loadUsableChannel($hcid = $this->input('hcid'));
        $this->view->channel = $channel;
    }

    /**
     * 执行更新预订渠道操作
     */
    public function doUpdateAction()
    {
        $channel = $this->loadUsableChannel($hcid = $this->input('hcid'));
        $stat = (int)$this->input('stat');
        $memo = $this->input('memo');

        if (!$stat && $channel['hc_id'] == $this->_hostel['h_order_default_channel'])
        {
            $this->flash(0, '默认预订渠道不允许停用');
        }

        if (mb_strlen($memo) > 200)
        {
            $this->flash(0, '预订渠道说明不能超过200个字符');
        }

        if ($this->model('hotel.channel')->modChannel($hcid, array(
            'hc_status' => $stat,
            'hc_memo' => $memo
        )))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 执行更新预订渠道状态操作
     */
    public function doUpdateStatusAction()
    {
        $channel = $this->loadUsableChannel($hcid = $this->input('hcid'));
        $stat = (int)$this->input('stat');

        if (!$stat && $channel['hc_id'] == $this->_hostel['h_order_default_channel'])
        {
            $this->flash(0, '默认预订渠道不允许停用');
        }

        if ($this->model('hotel.channel')->modChannel($hcid, array(
            'hc_status' => $stat,
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
        $channel = $this->loadUsableChannel($hcid = $this->input('hcid'));
        if (!$channel['hc_status'])
        {
            $this->flash(0, array(
                'message' => '该预订渠道当前为停用状态，不能设置为默认值',
                'content' => __('尝试<a href="/master/channel/do-update?hcid=%d&stat=1">启用该预订渠道并设置为默认值</a>', $channel['hc_id'])
            ));
        }

        if ($this->model('hotel')->modHotel($this->_hostel['h_id'], array('h_order_default_channel' => $channel['hc_id'])))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }
}
// End of file : ChannelController.php
