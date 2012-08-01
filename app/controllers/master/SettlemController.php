<?php
/**
 * @version    $Id$
 */
class Master_SettlemController extends MasterController
{
    /**
     * 结算方式管理
     */
    public function indexAction()
    {
        $this->view->settlem = $this->model('hotel.settlem')->getSettlemAryByHid($this->_hostel['h_id']);
    }

    /**
     * 查询旅店当前可用结算方式名接口
     */
    public function validSnameAction()
    {
        $sname = $this->input('name');
        if ($sname != '' && mb_strlen($sname) <= 30 && $this->model('hotel.settlem')->chkCanCreateSettlem($this->_hostel['h_id'], $sname))
        {
            $this->close(json_encode(true));
        }

        $this->close(json_encode(false));
    }

    /**
     * 获取可用结算方式列表
     */
    public function fetchAbledIndexAction()
    {
        if ($index = Zyon_Array::keyto($this->model('hotel.settlem')->getUsableSettlemAryByHid($this->_hostel['h_id']), 'hs_id'))
        {
            foreach ($index as $key => $val)
            {
                $index[$key] = $val['hs_name'];
            }

            $this->flash(1, array('context' => $index));
        }

        $this->flash(1, array('context' => array()));
    }

    /**
     * 创建结算方式
     */
    public function createAction()
    {
    }

    /**
     * 执行创建结算方式操作
     */
    public function doCreateAction()
    {
        $name = $this->input('name');
        $memo = $this->input('memo');
        $stat = (int)$this->input('stat');

        if ($name == '')
        {
            $this->flash(0, '结算方式名称不能为空');
        }

        if (mb_strlen($name) > 30)
        {
            $this->flash(0, '结算方式名称不能超过30个字符');
        }

        if (mb_strlen($memo) > 200)
        {
            $this->flash(0, '结算方式说明不能超过200个字符');
        }

        if (!$this->model('hotel.settlem')->chkCanCreateSettlem($this->_hostel['h_id'], $name))
        {
            $this->flash(0, '结算方式已存在');
        }

        if ($hsid = $this->model('hotel.settlem')->addSettlem(
            $this->model('hotel.settlem')->getNewSettlem($this->_hostel['h_id'], $stat, $name, $memo)
        ))
        {
            $this->flash($hsid, array(
                'timeout' => 10,
                'forward' => "/master/settlem/",
                'message' => '创建结算方式成功',
                'content' => array(
                    __("查看结算方式列表？请<a href='%s'>点击这里</a>", "/master/settlem/"),
                    __("修改结算方式信息？请<a href='%s'>点击这里</a>", "/master/settlem/update?hsid={$hsid}"),
                    __("继续创建结算方式？请<a href='%s'>点击这里</a>", '/master/settlem/create'),
                ),
            ));
        }

        $this->flash(0);
    }

    /**
     * 更新结算方式
     */
    public function updateAction()
    {
        $settlem = $this->loadUsableSettlem($hsid = $this->input('hsid'));
        $this->view->settlem = $settlem;
    }

    /**
     * 执行更新结算方式操作
     */
    public function doUpdateAction()
    {
        $settlem = $this->loadUsableSettlem($hsid = $this->input('hsid'));
        $stat = (int)$this->input('stat');
        $memo = $this->input('memo');

        if (!$stat && $settlem['hs_id'] == $this->_hostel['h_obill_default_settlem'])
        {
            $this->flash(0, '默认结算方式不允许停用');
        }

        if (mb_strlen($memo) > 200)
        {
            $this->flash(0, '结算方式说明不能超过200个字符');
        }

        if ($this->model('hotel.settlem')->modSettlem($hsid, array(
            'hs_status' => $stat,
            'hs_memo' => $memo
        )))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * 执行更新结算方式状态操作
     */
    public function doUpdateStatusAction()
    {
        $settlem = $this->loadUsableSettlem($hsid = $this->input('hsid'));
        $stat = (int)$this->input('stat');

        if (!$stat && $settlem['hs_id'] == $this->_hostel['h_obill_default_settlem'])
        {
            $this->flash(0, '默认结算方式不允许停用');
        }

        if ($this->model('hotel.settlem')->modSettlem($hsid, array(
            'hs_status' => $stat,
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
        $settlem = $this->loadUsableSettlem($hsid = $this->input('hsid'));
        if (!$settlem['hs_status'])
        {
            $this->flash(0, array(
                'message' => '该结算方式当前为停用状态，不能设置为默认值',
                'content' => __('尝试<a href="/master/settlem/do-update?hsid=%d&stat=1">启用该结算方式并设置为默认值</a>', $settlem['hs_id'])
            ));
        }

        if ($this->model('hotel')->modHotel($this->_hostel['h_id'], array('h_obill_default_settlem' => $settlem['hs_id'])))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }
}
// End of file : SettlemController.php
