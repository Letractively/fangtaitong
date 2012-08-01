<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Log_User extends Zyon_Model_Ftt_Log
{
    /**
     * _acts
     * 
     * @var array
     */
    protected $_acts = array(
        'signup' => 'USER_DO_SIGNUP',
        'signin' => 'USER_DO_SIGNIN',
        'active' => 'USER_DO_ACTIVE',
        'update_access' => 'USER_DO_UPDATE_ACCESS',
        'update_status' => 'USER_DO_UPDATE_STATUS',
        'update_passwd' => 'USER_DO_UPDATE_PASSWD',
    );

    /**
     * getNewSignupLog
     * 
     * @param array $oper
     * @param array $user
     * @return array
     */
    public function getNewSignupLog(array $oper, array $user = null)
    {
        $user === null AND $user = $oper;
        return $this->getNewLog('signup',
            $user['u_hid'], $user['u_id'], $user['u_realname'], $oper['u_id'], $oper['u_realname'], $user['u_sign'], array($user));
    }

    /**
     * getNewSigninLog
     * 
     * @param array $oper
     * @param array $user
     * @return array
     */
    public function getNewSigninLog(array $oper, array $user = null)
    {
        $user === null AND $user = $oper;
        return $this->getNewLog('signin',
            $user['u_hid'], $user['u_id'], $user['u_realname'], $oper['u_id'], $oper['u_realname'], $user['u_sign'], array($user));
    }

    /**
     * getNewActiveLog
     * 
     * @param array $oper
     * @param array $user_old
     * @param array $user_new
     * @return array
     */
    public function getNewActiveLog(array $oper, array $user_old, array $user_new)
    {
        return $this->getNewLog('active',
            $user_old['u_hid'], $user_old['u_id'], $user_old['u_realname'], $oper['u_id'], $oper['u_realname'], $user_old['u_sign'], array($user_old, $user_new));
    }

    /**
     * getNewUpdateAccessLog
     * 
     * @param array $oper
     * @param array $user_old
     * @param array $user_new
     * @return array
     */
    public function getNewUpdateAccessLog(array $oper, array $user_old, array $user_new)
    {
        return $this->getNewLog('update_access',
            $user_old['u_hid'], $user_old['u_id'], $user_old['u_realname'], $oper['u_id'], $oper['u_realname'], $user_old['u_sign'], array($user_old, $user_new));
    }

    /**
     * getNewUpdateStatusLog
     * 
     * @param array $oper
     * @param array $user_old
     * @param array $user_new
     * @return array
     */
    public function getNewUpdateStatusLog(array $oper, array $user_old, array $user_new)
    {
        return $this->getNewLog('update_status',
            $user_old['u_hid'], $user_old['u_id'], $user_old['u_realname'], $oper['u_id'], $oper['u_realname'], $user_old['u_sign'], array($user_old, $user_new));
    }

    /**
     * getNewUpdatePasswdLog
     * 
     * @param array $oper
     * @param array $user_old
     * @param array $user_new
     * @return array
     */
    public function getNewUpdatePasswdLog(array $oper, array $user_old, array $user_new)
    {
        return $this->getNewLog('update_passwd',
            $user_old['u_hid'], $user_old['u_id'], $user_old['u_realname'], $oper['u_id'], $oper['u_realname'], $user_old['u_sign'], array($user_old, $user_new));
    }

    /**
     * getSigninLogAryByHid
     * 
     * @param int   $hid
     * @param mixed $limit
     * @return array
     */
    public function getSigninLogAryByHid($hid, $limit = '10')
    {
        try
        {
            $sql = $this->dbase()->select()->from($this->tname('log_user'))
                ->where('lu_mid = :hid')
                ->where('lu_act = ?', $this->getAct('signin'))
                ->order('lu_id DESC')
                ->limit($limit);
            return $this->dbase()->fetchAll($sql, array('hid' => $hid));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getSingleSigninLogAryByHid
     * 
     * @param int   $hid
     * @param mixed $limit
     * @return array
     */
    public function getSingleSigninLogAryByHid($hid, $limit = '10')
    {
        try
        {
            $sql = $this->dbase()->select()->from($this->tname('log_user'), $this->expr('max(lu_id)'))
                ->where('lu_mid = :hid')
                ->where('lu_act = ?', $this->getAct('signin'))
                ->where('to_days(now()) - to_days(lu_date) < 4')
                ->group("concat(lu_pid, '@', lu_ip)")
                ->order('max(lu_id) desc')
                ->limit($limit);
            $ids = $this->dbase()->fetchCol($sql, array('hid' => $hid));

            if (!$ids)
            {
                return array();
            }

            $sql = $this->dbase()->select()->from($this->tname('log_user'))
                ->where('lu_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')')
                ->order('lu_pid')
                ->order('lu_id desc');
            return $this->dbase()->fetchAll($sql);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }
}
// End of file : User.php
