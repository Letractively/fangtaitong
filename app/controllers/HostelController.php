<?php
class HostelController extends Controller
{
    /**
     * _master
     * 
     * @var array
     */
    protected $_master;

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
            if (!$this->_master || !$this->_master['u_status'] || !$this->_master['u_active'])
            {
                $this->_master = null;
            }
        }

        $this->view->master === null AND $this->view->master = $this->_master;
    }

    /**
     * postDispatch
     * 
     * @return void
     */
    public function postDispatch()
    {
        $this->view->master === null AND $this->view->master = $this->_master;
    }

    /**
     * checkUserStat
     * 
     * @param mixed $user
     * @return void
     */
    public function checkUserStat($user)
    {
        if (empty($user))
        {
            $this->flash(0, array('message' => '系统没有当前用户的会话信息', 'forward' => null));
        }

        // 非活跃账户不允许登录
        if (!$user['u_status']
            || !($user['u_active'] & (int)USER_ACTIVE_JH)
            || !($user['u_active'] & (int)USER_ACTIVE_DL))
        {
            $this->flash(0, array('message' => '用户帐号当前不可用', 'forward' => null));
        }
    }

    /**
     * checkHotelStat
     * 
     * @param mixed $hotel
     * @return void
     */
    public function checkHotelStat($hotel)
    {
        if (empty($hotel))
        {
            $this->flash(-1, array(
                'forward' => null,
                'message' => '找不到旅店信息',
            ));
        }

        if (!$hotel['h_status'])
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
        if (empty($user))
        {
            $this->flash(0, '找不到指定的用户');
        }

        return $user;
    }

    /**
     * loadUsableHotel
     * 获取操作的旅店实体，正确则返回该实体
     * 
     * @param string $name
     * @return mixed
     */
    public function loadUsableHotel($name)
    {
        if (empty($name))
        {
            $this->flash(0, '没有指定旅店');
        }

        $hotel = $this->model('hotel')->getHotel($name);
        if (empty($hotel))
        {
            $this->flash(0, '找不到指定的旅店');
        }

        return $hotel;
    }
}
// End of file : HostelController.php
