<?php
/**
 * @version    $Id$
 */
class Master_TypedefController extends MasterController
{
    /**
     * 预订类型管理
     */
    public function indexAction()
    {
        $this->view->typedef = $this->model('hotel.typedef')->getTypedefAryByHid($this->_hostel['h_id']);
    }

    /**
     * 查询旅店当前可用预订类型名接口
     */
    public function validTnameAction()
    {
        $tname = $this->input('name');
        if ($tname != '' && mb_strlen($tname) <= 30 && $this->model('hotel.typedef')->chkCanCreateTypedef($this->_hostel['h_id'], $tname))
        {
            $this->close(json_encode(true));
        }

        $this->close(json_encode(false));
    }

    /**
     * 获取可用预订类型列表
     */
    public function fetchAbledIndexAction()
    {
        if ($index = Zyon_Array::keyto($this->model('hotel.typedef')->getUsableTypedefAryByHid($this->_hostel['h_id']), 'ht_id'))
        {
            foreach ($index as $key => $val)
            {
                $index[$key] = $val['ht_name'];
            }

            $this->flash(1, array('context' => $index));
        }

        $this->flash(1, array('context' => array()));
    }

    /**
     * 创建预订类型
     */
    public function createAction()
    {
    }

    /**
     * 执行创建预订类型操作
     */
    public function doCreateAction()
    {
        $name = $this->input('name');
        $memo = $this->input('memo');
        $stat = (int)$this->input('stat');

        if ($name == '')
        {
            $this->flash(0, '预订类型名称不能为空');
        }

        if (mb_strlen($name) > 30)
        {
            $this->flash(0, '预订类型名称不能超过30个字符');
        }

        if (mb_strlen($memo) > 200)
        {
            $this->flash(0, '预订类型说明不能超过200个字符');
        }

        if (!$this->model('hotel.typedef')->chkCanCreateTypedef($this->_hostel['h_id'], $name))
        {
            $this->flash(0, '预订类型已存在');
        }

        if ($hsid = $this->model('hotel.typedef')->addTypedef(
            $this->model('hotel.typedef')->getNewTypedef($this->_hostel['h_id'], $stat, $name, $memo)
        ))
        {
            $this->flash($hsid, array(
                'timeout' => 10,
                'forward' => "/master/typedef/",
                'message' => '创建预订类型成功',
                'content' => array(
                    __("查看预订类型列表？请<a href='%s'>点击这里</a>", "/master/typedef/"),
                    __("修改预订类型信息？请<a href='%s'>点击这里</a>", "/master/typedef/update?hsid={$hsid}"),
                    __("继续创建预订类型？请<a href='%s'>点击这里</a>", '/master/typedef/create'),
                ),
            ));
        }

        $this->flash(0);
    }

    /**
     * 更新预订类型
     */
    public function updateAction()
    {
        $typedef = $this->loadUsableTypedef($hsid = $this->input('hsid'));
        $this->view->typedef = $typedef;
    }

    /**
     * 执行更新预订类型操作
     */
    public function doUpdateAction()
    {
        $typedef = $this->loadUsableTypedef($hsid = $this->input('hsid'));
        $stat = (int)$this->input('stat');
        $memo = $this->input('memo');

        if (!$stat && $typedef['ht_id'] == $this->_hostel['h_order_default_typedef'])
        {
            $this->flash(0, '默认预订类型不允许停用');
        }

        if (mb_strlen($memo) > 200)
        {
            $this->flash(0, '预订类型说明不能超过200个字符');
        }

        if ($this->model('hotel.typedef')->modTypedef($hsid, array(
            'ht_status' => $stat,
            'ht_memo' => $memo
        )))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 执行更新预订类型状态操作
     */
    public function doUpdateStatusAction()
    {
        $typedef = $this->loadUsableTypedef($hsid = $this->input('hsid'));
        $stat = (int)$this->input('stat');

        if (!$stat && $typedef['ht_id'] == $this->_hostel['h_order_default_typedef'])
        {
            $this->flash(0, '默认预订类型不允许停用');
        }

        if ($this->model('hotel.typedef')->modTypedef($hsid, array(
            'ht_status' => $stat,
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
        $typedef = $this->loadUsableTypedef($hsid = $this->input('hsid'));
        if (!$typedef['ht_status'])
        {
            $this->flash(0, array(
                'message' => '该预订类型当前为停用状态，不能设置为默认值',
                'content' => __('尝试<a href="/master/typedef/do-update?hsid=%d&stat=1">启用该预订类型并设置为默认值</a>', $typedef['ht_id'])
            ));
        }

        if ($this->model('hotel')->modHotel($this->_hostel['h_id'], array('h_order_default_typedef' => $typedef['ht_id'])))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }
}
// End of file : TypedefController.php
