<?php
/**
 * @version    $Id$
 */
class Master_AccountController extends MasterController
{
    /**
     * 帐号管理
     */
    public function indexAction()
    {
        $masters = Zyon_Array::keyto(
            $this->model('user')->getUserAryByHid($this->_master['u_hid']), 'u_id'
        );

        $signinLogs = $this->model('log.user')->getSingleSigninLogAryByHid($this->_hostel['h_id']);

        $this->view->masters = $masters;
        $this->view->signinLogs = $signinLogs;
    }

    /**
     * 获取可用销售人员列表
     */
    public function fetchAbledIndexAction()
    {
        if ($index = Zyon_Array::keyto($this->model('user')->getUsableUserAryByHid($this->_hostel['h_id']), 'u_id'))
        {
            foreach ($index as $key => $val)
            {
                $index[$key] = $val['u_realname'];
            }

            $this->flash(1, array('context' => $index));
        }

        $this->flash(1, array('context' => array()));
    }

    /**
     * 创建员工帐号
     */
    public function createAction()
    {
    }

    public function doCreateAction()
    {
        $email = $this->input('email');
        $rname = $this->input('rname');

        if (empty($email) || !Zyon_Util::isEmail($email))
        {
            $this->flash(0, '邮件地址错误');
        }

        if ($rname == '')
        {
            $this->flash(0, '真实姓名必须填写');
        }

        if ($this->model('user')->getUserByEmail($email))
        {
            $this->flash(0, '邮件地址已存在');
        }

        $idtype = $this->input('idtype');
        if (!array_key_exists($idtype, $this->model('user')->getIdTypes()))
        {
            $this->flash(0, '证件类型错误');
        }

        $user = $this->model('user')->getNewUser($email, $rname, STAFFS_PASSWORD);
        $user['u_hid']      = $this->_master['u_hid'];
        $user['u_idtype']   = $idtype;
        $user['u_idno']     = $this->input('idno');
        $user['u_rolename'] = $this->input('rolename');

        $mobile = $this->input('mobile');
        if (!empty($mobile))
        {
            $user['u_phone'] = $mobile;
        }

        $permit = $this->input('permit', 'array');
        if (!empty($permit))
        {
            $user['u_permit'] = 0;
            foreach ($permit as $key => $val)
            {
                if (!isset($key[1]) || $key[0] !== 'b'
                    || !Zyon_Util::isBin($bin = substr($key, 1)) || ($dec = bindec($bin)) > PERMIT_MASTER || $dec < 1)
                {
                    $this->flash(0, '权限分配错误');
                }

                if ($val > 0)
                {
                    $user['u_permit'] = $user['u_permit'] | $dec;
                }
            }
        }

        $user['u_status'] = 1;
        $user['u_active'] = USER_ACTIVE_DL | USER_ACTIVE_JH;
        if ($uid = $this->model('user')->addUser($user))
        {
            $user = $this->model('user')->getUser($uid);
            $this->model('log.user')->addLog($this->model('log.user')->getNewSignupLog($this->_master, $user));

            $this->flash(1, array('message' => '添加帐号成功！', 'forward' => '/master/account/'));
        }

        $this->flash(0);
    }

    /**
     * 更新帐号信息页
     */
    public function updateAction()
    {
        $this->checkActionTarget($user = $this->loadUsableUser($this->input('uid', 'numeric')));
        $this->view->account = $user;
    }

    public function doUpdateAction()
    {
        if (!array_key_exists($this->input('idtype'), $this->model('user')->getIdTypes()))
        {
            $this->flash(0, '证件类型错误');
        }

        $this->checkActionTarget($user = $this->loadUsableUser($uid = $this->input('uid', 'numeric')));

        $account = array();
        $account['u_permit']   = 0;
        $account['u_phone']   = $this->input('mobile');
        $account['u_idtype']   = $this->input('idtype');
        $account['u_idno']     = $this->input('idno');
        $account['u_rolename'] = $this->input('rolename');

        $permit = $this->input('permit', 'array');
        if (!empty($permit))
        {
            foreach ($permit as $key => $val)
            {
                if (!isset($key[1]) || $key[0] !== 'b'
                    || !Zyon_Util::isBin($bin = substr($key, 1)) || ($dec = bindec($bin)) > PERMIT_MASTER || $dec < 1)
                {
                    $this->flash(0, '权限分配错误');
                }

                if ($val > 0)
                {
                    $account['u_permit'] = $account['u_permit'] | $dec;
                }
            }
        }

        if ($this->model('user')->modUser($uid, $account))
        {
            $this->flash(1, '更新帐号信息成功');
        }

        $this->flash(0);
    }

    /**
     * 修改帐号登录许可
     */
    public function doChangeAccessAction()
    {
        $uid = $this->input('uid', 'numeric');
        if ($uid === null)
        {
            $this->flash(0, '缺少参数');
        }

        if ($user = $this->model('user')->getUser($uid))
        {
            $this->checkActionTarget($user);
            if ($user = $this->model('user')->getUser($uid))
            {
                if ($this->model('user')->modUser($uid, array(
                    'u_active' => $this->model('user')->expr('u_active ^ ' . USER_ACTIVE_DL)
                )))
                {
                    $this->model('log.user')->addLog(
                        $this->model('log.user')->getNewUpdateAccessLog(
                            $this->_master,
                            $user,
                            $user = $this->model('user')->getUser($uid)
                        )
                    );

                    $this->flash(1);
                }
            }
        }

        $this->flash(0);
    }

    /**
     * 修改用户帐号状态
     */
    public function doUpdateStatusAction()
    {
        $uid = $this->input('uid', 'numeric');
        $sta = $this->input('sta', 'numeric');

        if ($uid === null || $sta === null)
        {
            $this->flash(0, '缺少参数');
        }

        $sta = $sta ? '1' : '0';

        if ($user = $this->model('user')->getUser($uid))
        {
            $this->checkActionTarget($user);

            if (!$sta && $user['u_id'] === $this->_hostel['h_order_default_saleman'])
            {
                $this->flash(0, '默认销售人员帐号不允许停用');
            }

            if ($user['u_status'] === $sta)
            {
                $this->flash(1);
            }

            if ($this->model('user')->modUser($uid, array('u_status' => $sta)))
            {
                $this->model('log.user')->addLog(
                    $this->model('log.user')->getNewUpdateStatusLog(
                        $this->_master,
                        $user,
                        $user = $this->model('user')->getUser($uid)
                    )
                );

                $this->flash(1);
            }
        }

        $this->flash(0);
    }

    /**
     * 执行设为默认值操作
     */
    public function doLocateAction()
    {
        $saleman = $this->loadUsableSaleman($this->input('sid'));
        if (!$saleman['u_status'])
        {
            $this->flash(0, array(
                'message' => '该员工帐号当前不可用，不能设置为默认销售人员',
                'content' => __('尝试<a href="/master/account/do-update-status?uid=%d&sta=1">启用该员工帐号并设置为默认值</a>', $saleman['u_id'])
            ));
        }

        if ($saleman['u_id'] === $this->_hostel['h_order_default_saleman'])
        {
            $this->flash(0, '指定的员工帐号与当前默认销售人员帐号相同');
        }

        if ($this->model('hotel')->modHotel($this->_hostel['h_id'], array(
            'h_order_default_saleman' => $saleman['u_id']
        )))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }

    /**
     * checkActionTarget
     * 检测是否可执行更新操作
     * 
     * @param array $user
     * @return void
     */
    public function checkActionTarget(array $user)
    {
        if ($user['u_hid'] !== $this->_master['u_hid'])
        {
            $this->flash(0, '找不到指定的账户');
        }

        if ($user['u_id'] === $this->_master['u_id'] || ((int)$user['u_role'] & USER_ROLE_ROOTER))
        {
            $this->flash(0, '不允许对该用户信息进行操作');
        }
    }
}
// End of file : AccountController.php
