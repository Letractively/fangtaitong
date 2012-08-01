<?php
class MasterController extends Controller
{
    /**
     * _master
     * 
     * @var array
     */
    protected $_master;

    /**
     * _hostel
     * 
     * @var array
     */
    protected $_hostel;

    /**
     * init
     * 
     * @return void
     */
    public function init()
    {
        if (($session = $this->model('user')->getAuth()->verify()->getSession())
            && ($uid = $this->model('user')->parseAuthUqid($session->getIdentity(), SIGNIN_SINGLEPT))
        )
        {
            $this->_master = $this->model('user')->getUser($uid);
            $this->checkUserStat();
        }

        $this->checkUserPerm();

        if ($this->_master)
        {
            $this->_hostel = $this->model('hotel')->getHotel($this->_master['u_hid']);
            $this->checkHotelStat();
        }

        $this->view->master === null AND $this->view->master = $this->_master;
        $this->view->hostel === null AND $this->view->hostel = $this->_hostel;
    }

    /**
     * postDispatch
     * 
     * @return void
     */
    public function postDispatch()
    {
        $this->view->master === null AND $this->view->master = $this->_master;
        $this->view->hostel === null AND $this->view->hostel = $this->_hostel;
    }

    /**
     * checkUserStat
     * 
     * @return void
     */
    public function checkUserStat()
    {
        if (empty($this->_master))
        {
            $this->model('user')->getAuth()->logout();
            $this->flash(0, array('message' => '系统没有当前用户的会话信息', 'forward' => null));
        }

        // 非活跃账户不允许登录
        if (!$this->_master['u_status']
            || !($this->_master['u_active'] & (int)USER_ACTIVE_JH)
            || !($this->_master['u_active'] & (int)USER_ACTIVE_DL))
        {
            $this->model('user')->getAuth()->logout();
            $this->flash(0, array('message' => '您的帐号当前不可用', 'forward' => null));
        }
    }

    /**
     * checkUserPerm
     *
     * @param mixed $resource
     * @return void
     */
    public function checkUserPerm($resource = null)
    {
        $accessor = empty($this->_master['u_id'])
            ? null : new Zyon_Acl_Accessor($this->_master['u_id'], $this->getRequest()->getModuleName());

        if (!$this->check($accessor, $resource))
        {
            $this->flash(-1, array(
                // 'rescode' => 403,
                'forward' => null,
                'message' => '您没有访问该模块或使用该功能的权限',
                'content' => empty($this->_master['u_id'])
                ? sprintf(__('可以尝试<a href="%s">重新登录</a>'), '/master/')
                : sprintf(__('可以尝试<a href="%s">退出系统，换一个帐号重新登录</a>'), '/master/index/do-logout')
            ));
        }
    }

    /**
     * hasUserPerm
     * 
     * @param mixed $resource
     * @return bool
     */
    public function hasUserPerm($resource = null)
    {
        $accessor = empty($this->_master['u_id'])
            ? null : new Zyon_Acl_Accessor($this->_master['u_id'], $this->getRequest()->getModuleName());

        return $this->check($accessor, $resource);
    }

    /**
     * checkHotelStat
     * 
     * @return void
     */
    public function checkHotelStat()
    {
        if (!$this->_hostel)
        {
            $this->flash(-1, array(
                'forward' => null,
                'message' => '找不到旅店信息',
            ));
        }

        if (!$this->_hostel['h_status'])
        {
            $this->flash(-1, array(
                'forward' => null,
                'message' => '旅店帐号不可用',
            ));
        }
    }

    /**
     * loadUsableUser
     * 获取操作的帐号实体，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableUser($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定用户');
        }

        $user = $this->model('user')->getUser($name);
        if (empty($user) || $user['u_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的用户');
        }

        return $user;
    }

    /**
     * loadUsableBill
     * 获取操作的账单实体，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableBill($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定账单');
        }

        $bill = $this->model('bill')->getBill($name);
        if (empty($bill) || $bill['b_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的账单');
        }

        return $bill;
    }

    /**
     * loadUsableRoom
     * 获取操作的房间实体，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableRoom($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定房间');
        }

        $room = $this->model('room')->getRoom($name);
        if (empty($room) || $room['r_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的房间');
        }

        return $room;
    }

    /**
     * loadUsableMber
     * 获取操作的会员实体，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableMber($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定会员');
        }

        $mber = $this->model('mber')->getMber($name);
        if (empty($mber) || $mber['m_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的会员');
        }

        return $mber;
    }

    /**
     * loadUsableMberByUqno
     * 根据会员编号获取操作的会员实体，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableMberByUqno($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定会员');
        }

        $mber = $this->model('mber')->getMberByNo($name, $this->_master['u_hid']);
        if (empty($mber))
        {
            $this->flash(0, '找不到指定的会员');
        }

        return $mber;
    }

    /**
     * loadUsableOrder
     * 获取操作的订单实体，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableOrder($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定订单');
        }

        $order = $this->model('order')->getOrder($name);
        if (empty($order) || $order['o_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的订单');
        }

        return $order;
    }

    /**
     * loadUsableTypedef
     * 获取操作的预订类型，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableTypedef($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定预订类型');
        }

        $typedef = $this->model('hotel.typedef')->getTypedef($name);
        if (empty($typedef) || $typedef['ht_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的预订类型');
        }

        return $typedef;
    }

    /**
     * loadUsableChannel
     * 获取操作的渠道实体，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableChannel($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定渠道');
        }

        $channel = $this->model('hotel.channel')->getChannel($name);
        if (empty($channel) || $channel['hc_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的渠道');
        }

        return $channel;
    }

    /**
     * loadUsablePayment
     * 获取操作的收款方式，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsablePayment($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定收款方式');
        }

        $payment = $this->model('hotel.payment')->getPayment($name);
        if (empty($payment) || $payment['hp_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的收款方式');
        }

        return $payment;
    }

    /**
     * loadUsableSaleman
     * 获取操作的销售人员，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableSaleman($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定销售人员');
        }

        $saleman = $this->model('user')->getUser($name);
        if (empty($saleman) || $saleman['u_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的销售人员');
        }

        return $saleman;
    }

    /**
     * loadUsableSettlem
     * 获取操作的结算方式，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableSettlem($name)
    {
        if (empty($name))
        {
            $this->flash(0, '未指定结算方式');
        }

        $settlem = $this->model('hotel.settlem')->getSettlem($name);
        if (empty($settlem) || $settlem['hs_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的结算方式');
        }

        return $settlem;
    }
}
// End of file : MasterController.php
