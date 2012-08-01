<?php
class Master_IndexController extends MasterController implements Zyon_Auth_Adapter
{
    /**
     * 旅店推荐链接的客户端存储键
     */
    const KEY_REFER = 'Z_LINK_rfid';

    /**
     * authenticate
     * 
     * @see Zyon_Auth_Adapter
     * @return Zyon_Auth_Outcome
     */
    public function authenticate()
    {
        if (($username = $this->input('username')) && ($password = $this->input('password')))
        {
            if ($user = $this->model('user')->getUserByEmail($username))
            {
                $time = $_SERVER['REQUEST_TIME'];
                if ($time - $user['u_atime'] <= SIGNIN_INTERVAL)
                {
                    return new Zyon_Auth_Outcome_Failure(new Zyon_Auth_Message('登录次数太过频繁，请稍侯再试！', -1));
                }

                if ($this->model('user')->chkUserPassword($user['u_id'], $password))
                {
                    $this->model('user')->modUser($user['u_id'], array('u_atime' => $time));
					
                    return new Zyon_Auth_Outcome_Success(
                        $this->model('user')->buildAuthUqid($user['u_id']),
                        $this->input('remember') ? SIGNIN_REMEMBER : 0
                    );
                }
                else
                {
                    return new Zyon_Auth_Outcome_Failure('用户名或密码错误');
                }
            }
            else
            {
                return new Zyon_Auth_Outcome_Failure('该用户不存在');
            }
        }

        return new Zyon_Auth_Outcome_Failure('登录失败');
    }

    /**
     * 店家首页
     */
    public function indexAction()
    {
        if (!$this->_master)
        {
            return $this->_forward('signin');
        }

        $this->_forward('index', 'rosta', 'master');
    }

    /**
     * 新手向导
     */
    public function guideAction()
    {
    }

    /**
     * 查询系统当前可用邮箱接口
     */
    public function validEmailAction()
    {
        $email = $this->input('email');
        if (!empty($email) && !$this->model('user')->getUserByEmail($email))
        {
            exit(json_encode(true));
        }

        exit(json_encode(false));
    }

    /**
     * 店家注册页
     */
    public function signupAction()
    {
        if ($this->_master)
        {
            $this->_redirect('/master');
        }

        if (SIGNUP_DISABLED)
        {
            $this->flash(0, '系统关闭注册');
        }
    }

    /**
     * 执行注册动作
     */
    public function doSignupAction()
    {
        if (SIGNUP_DISABLED)
        {
            $this->flash(0, '系统关闭注册');
        }

        $captcha = new Geek_Captcha_Image('/master/index/do-signup');
        if (!$captcha->isValid($this->input('captcha')))
        {
            $this->flash(0, '验证码错误');
        }

        $hotelnm  = $this->input('hotelnm');
        $contact  = $this->input('contact');
        $address  = $this->input('address');

        $username = $this->input('username');
        $password = $this->input('password');
        $realname = $this->input('realname');

        $typedef = $this->input('typedef');
        $channel = $this->input('channel');
        $payment = $this->input('payment');
        $settlem = $this->input('settlem');

        if (!Zyon_Util::isEmail($username))
        {
            $this->flash(0, '登录邮箱格式错误');
        }

        if ($this->model('user')->getUserByEmail($username))
        {
            $this->flash(0, array(
                'message' => 'Sorry!邮箱地址已被使用，请重新申请注册',
                'forward' => '/master/index/signup',
            ));
        }

        if ($hotelnm == '' || $address == '' || $contact == ''
            || $username == '' || $password == '' || $realname == ''
            || $typedef == '' || $channel == '' || $payment == '' || $settlem == ''
        )
        {
            $this->flash(0, '必填项没有填写完整');
        }

        if (!isset($password[5]))
        {
            $this->flash(0, '密码长度至少为6个字符');
        }

        $hotel = $this->model('hotel')->getNewHotel($hotelnm, $username, $contact);
        $hotel['h_email']    = $username;
        $hotel['h_country']  = $this->input('country');
        $hotel['h_province'] = $this->input('province');
        $hotel['h_city']     = $this->input('city');
        $hotel['h_address']  = $address;

        $this->model('hotel')->dbase()->beginTransaction();
        try
        {
            if (!($hid = $this->model('hotel')->addHotel($hotel)))
            {
                throw new exception('创建旅店失败，请稍后再试');
            }

            $user = $this->model('user')->getNewUser($username, $realname, $password);
            $user['u_hid']    = $hid;
            $user['u_phone']  = $contact;
            $user['u_role']   = USER_ROLE_ROOTER;
            $user['u_permit'] = PERMIT_ROOTER;
            $user['u_active'] = USER_ACTIVE_JH | USER_ACTIVE_DL;
            if (!($hotel_saleman = $uid = $this->model('user')->addUser($user)))
            {
                throw new exception("创建旅店默认登录帐号失败");
            }

            if (!($hotel_typedef = $this->model('hotel.typedef')->addTypedef(
                $this->model('hotel.typedef')->getNewTypedef($hid, 1, $typedef)
            )))
            {
                throw new exception('创建旅店默认预订类型失败');
            }

            if (!($hotel_channel = $this->model('hotel.channel')->addChannel(
                $this->model('hotel.channel')->getNewChannel($hid, 1, $channel)
            )))
            {
                throw new exception('创建旅店默认预订渠道失败');
            }

            if (!($hotel_payment = $this->model('hotel.payment')->addPayment(
                $this->model('hotel.payment')->getNewPayment($hid, 1, $payment)
            )))
            {
                throw new exception('创建旅店默认支付渠道失败');
            }

            if (!($hotel_settlem = $this->model('hotel.settlem')->addSettlem(
                $this->model('hotel.settlem')->getNewSettlem($hid, 1, $settlem)
            )))
            {
                throw new exception('创建账单默认结算方式失败');
            }

            if (!$this->model('hotel')->modHotel($hid, array(
                'h_order_default_saleman' => $hotel_saleman,
                'h_order_default_typedef' => $hotel_typedef,
                'h_order_default_channel' => $hotel_channel,
                'h_order_default_payment' => $hotel_payment,
                'h_obill_default_settlem' => $hotel_settlem,
            )))
            {
                throw new exception("旅店设置失败");
            }

            $this->model('hotel')->dbase()->commit();

            // Signin Now!
            $user = $this->model('user')->getUser($uid);
            $this->model('log.user')->addLog($this->model('log.user')->getNewSignupLog($user));

            if ($this->model('user')->getAuth()->verify($this)->getSession())
            {
                $this->model('log.user')->addLog($this->model('log.user')->getNewSigninLog($user));
            }

            $this->flash(1, array('message' => '注册成功，可以开始管理您的旅店了', 'forward' => '/master/index/guide'));
        }
        catch (Exception $e)
        {
            $this->model('hotel')->dbase()->rollBack();
            $this->error($e);
            $this->flash(0, array(
                'forward' => null,
                'message' => $e->getMessage(),
            ));
        }
    }

    /**
     * 店家登录页
     */
    public function signinAction()
    {
        if ($this->_master)
        {
            $this->_redirect('/master');
        }
    }

    /**
     * 执行登录动作
     */
    public function doSigninAction()
    {
        if ($this->_master)
        {
            $this->flash(1, '您已经登录过了');
        }

        if (!Zyon_Util::isEmail($usign = $this->input('username')))
        {
            $this->flash(0, '请填写正确的邮箱地址');
        }

        $chash = md5(__CLASS__ . ':signin#' . $usign);
        if ($this->input('captcha') !== '' || $this->cache()->load($chash))
        {
            $captcha = new Geek_Captcha_Image('/master/index/do-signin');
            if (!$captcha->isValid($this->input('captcha')))
            {
                $this->flash($this->input('captcha') !== '' ? 0 : -1, '请填写正确的验证码');
            }
        }

        $outcome = $this->model('user')->getAuth()->verify($this);
        if (($session = $outcome->getSession()) && ($uid = $this->model('user')->parseAuthUqid($session->getIdentity())))
        {
            $this->_master = $this->model('user')->getUser($uid);

            $this->checkUserStat();

            $this->model('log.user')->addLog($this->model('log.user')->getNewSigninLog($this->_master));

            $this->cache()->remove($chash);
            $this->flash(1, array('timeout' => 0, 'forward' => '/master'));
        }

        $this->cache()->save(1, $chash);
        $this->flash($this->input('captcha') !== '' ? 0 : -1, $outcome->getMessage());
    }

    /**
     * 执行登出动作
     */
    public function doLogoutAction()
    {
        if ($this->model('user')->getAuth()->logout())
        {
            $this->flash(1, array('forward' => '/master', 'message' => '成功退出登录'));
        }
        else
        {
            $this->flash(0, array('forward' => '/master', 'message' => '退出登录状态失败，请稍后重试'));
        }
    }

    /**
     * 找回密码页
     */
    public function passwordRecoveryAction()
    {
    }

    /**
     * 执行发送找回密码邮件操作
     */
    public function doPasswordRecoveryAction()
    {
        $captcha = new Geek_Captcha_Image('/master/index/do-password-recovery');
        if (!$captcha->isValid($this->input('captcha')))
        {
            $this->flash(0, '验证码错误');
        }

        if (($username = $this->input('username')) && ($user = $this->model('user')->getUserByEmail($username)))
        {
            $this->flash($this->sendPasswordRecoveryMail($user, $info), $info);
        }

        $this->flash(0, '请填写正确的邮箱地址');
    }

    /**
     * 重置密码页
     */
    public function resetPasswordAction()
    {
        $reqtime = (int)$this->input('t');
        if ($reqtime >= time() - 86400*3) // 重置密码邮件在72小时内有效
        {
            $account = $this->input('a');
            $oencode = $this->input('o');

            if (!empty($account) && !empty($oencode) && ($user = $this->model('user')->getUserByEmail($account)))
            {
                if ($oencode === $this->model('user')->getEncPwd(
                    $this->model('user')->getEncPwd($user['u_email'], $user['u_pswd']), $reqtime
                ))
                {
                    $reqtime = time() - 86400*3 + 600; // 保证重置操作在打开页面后的10分钟内有效
                    $forward = '/master/index/do-reset-password/'
                        . '?a=' . rawurlencode($user['u_email'])
                        . '&t=' . rawurlencode($reqtime)
                        . '&o=' . rawurlencode($this->model('user')->getEncPwd(
                            $this->model('user')->getEncPwd($user['u_email'], $user['u_pswd']), $reqtime
                        ));

                    $this->view->user    = $user;
                    $this->view->forward = $forward;
                    return;
                }
            }
        }

        $this->flash(0, array('forward' => '/master'));
    }

    /**
     * 执行重置密码操作
     */
    public function doResetPasswordAction()
    {
        $reqtime = (int)$this->input('t');
        if ($reqtime >= time() - 86400*3) // 重置密码邮件在72小时内有效
        {
            $account = $this->input('a');
            $oencode = $this->input('o');

            if (!empty($account) && !empty($oencode) && ($user = $this->model('user')->getUserByEmail($account)))
            {
                if ($oencode === $this->model('user')->getEncPwd(
                    $this->model('user')->getEncPwd($user['u_email'], $user['u_pswd']), $reqtime
                ))
                {
                    $password = $this->input('password');
                    if (empty($password))
                    {
                        $this->flash(0, '密码不能为空');
                    }

                    if ($this->model('user')->modUserPassword($user['u_id'], $password))
                    {
                        $this->model('log.user')->addLog(
                            $this->model('log.user')->getNewUpdatePasswdLog(
                                $user,
                                $user,
                                $user = $this->model('user')->getUser($user['u_id'])
                            )
                        );

                        $this->model('user')->getAuth()->logout();
                        $this->flash(1, array('message' => '成功重置密码，请使用新的帐号密码登录', 'forward' => '/master'));
                    }
                }
            }
        }
        else
        {
            $this->flash(0, '操作超时');
        }

        $this->flash(0);
    }

    /**
     * getTargetHostel
     * 
     * @return array
     */
    public function getTargetHostel()
    {
        if ($this->input('i'))
        {
            return $this->model('hotel')->getHotel($this->input('i'));
        }
        else if ($this->input('in'))
        {
            return $this->model('hotel')->getHotelByIname($this->input('in'));
        }
    }

    /**
     * sendPasswordRecoveryMail
     * 
     * @param array  $user
     * @param string $info 
     * @return bool
     */
    public function sendPasswordRecoveryMail($user, &$info = null)
    {
        $info = '发送重置密码邮件失败';
        if ($user)
        {
            $to_name   = $user['u_realname'];
            $to_email  = $user['u_email'];
            $from_name = __('房态通系统邮件');
            $title     = __('重新设置用户密码');

            $reqtime = time();
            $forward = URL_FTT . '/master/index/reset-password/'
                . '?i=' . rawurlencode($user['u_hid'])
                . '&a=' . rawurlencode($user['u_email'])
                . '&t=' . rawurlencode($reqtime)
                . '&o=' . rawurlencode($this->model('user')->getEncPwd(
                    $this->model('user')->getEncPwd($user['u_email'], $user['u_pswd']), $reqtime
                ));
            $content = $this->model('mail')->getTpl('password-recovery', array(
                'user' => $user,
                'goto' => $forward,
                'time' => $reqtime,
            ));

            $content = trim($content);
            $mailJob = $this->model('mail.job');
            if ($mailJob->checkTimesLimit($to_email, $title, $from_name))
            {
                if ($mailJob->addJob($mailJob->getNewJob($to_name, $to_email, $title, $content, $from_name)))
                {
                    //$info = '发送重置密码邮件成功';
                    $info = array('forward' => '/master', 'message' => '发送重置密码邮件成功，请及时查收邮件。');
                    return true;
                }

                return false;
            }

            $info = '发送邮件次数太频繁，请稍后再试';
        }

        return false;
    }
}
// End of file : IndexController.php
