<?php
/**
 * @version    $Id$
 */
class Master_ProfileController extends MasterController
{
    public function indexAction()
    {
        $this->_forward('update-self');
    }

    public function updateSelfAction()
    {
    }

    public function doUpdateSelfAction()
    {
        $idtype = $this->input('idtype');
        if (!$this->model('user')->isIdType($idtype))
        {
            $this->flash(0, '不被允许的证件类型');
        }

        $map = array(
            // 'u_nickname' => $this->input('nickname'),
            'u_idtype'   => $idtype,
            'u_idno'     => $this->input('idno'),
            'u_phone'   => $this->input('mobile'),
        );

        if ($this->model('user')->modUser($this->_master['u_id'], $map))
        {
            $this->flash(1, '成功更新个人信息');
        }
        else
        {
            $this->flash(0, '更新个人信息失败');
        }
    }

    public function doUpdateSelfPasswordAction()
    {
        $original = $this->input('original');
        $password = $this->input('password');

        if (!isset($original[5]) || !isset($password[5]))
        {
            $this->flash(0, '密码长度最少要6个字符');
        }

        if ($original === $password)
        {
            $this->flash(0, '输入的新密码和原密码相同');
        }

        if (!$this->model('user')->chkUserPassword($this->_master['u_id'], $original))
        {
            $this->flash(0, '原密码错误');
        }

        if ($this->model('user')->modUserPassword($this->_master['u_id'], $password))
        {
            $this->model('log.user')->addLog(
                $this->model('log.user')->getNewUpdatePasswdLog(
                    $this->_master,
                    $this->_master,
                    $this->model('user')->getUser($this->_master['u_id'])
                )
            );

            $this->flash(1, '修改成功');
        }
        else
        {
            $this->flash(0, '修改失败');
        }
    }
}
// End of file : ProfileController.php
