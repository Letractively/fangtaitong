<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_User extends Zyon_Model_Ftt
{
    /**
     * _idtypes
     *
     * @var array
     */
    protected static $_idtypes;

    /**
     * _auth
     * 
     * @var Zyon_Auth
     */
    protected $_auth;

    /**
     * _prepare
     * 
     * @return void
     */
    protected function _prepare()
    {
        if (empty(static::$_idtypes))
        {
            static::$_idtypes = getSysIdTypes();
        }
    }

    /**
     * getAuth
     * 
     * @return Zyon_Auth
     */
    public function getAuth()
    {
        return $this->_auth ?: $this->_auth = Zyon_Auth_Factory::getAuth('user');
    }

    /**
     * buildAuthUqid
     * 
     * @param mixed $id
     * @return string
     */
    public function buildAuthUqid($id)
    {
        if ($user = $this->getUser($id))
        {
            return 'U'
                . str_pad(substr((string)$user['u_pswd'], 0, 16), 16, '*')
                . str_pad(substr((string)$user['u_atime'], 0, 10), 10, '#')
                . $user['u_id'];
        }

        return '';
    }

    /**
     * parseAuthUqid
     * 
     * @param string $is
     * @param bool   $sp
     * @return mixed
     */
    public function parseAuthUqid($is, $sp = true)
    {
        if (is_string($is) && strlen($is) > 27
            && $is[0] == 'U' && Zyon_Util::isUnsignedInt($id = substr($is, 27)))
        {
            if ($user = $this->getUser($id))
            {
                if (str_pad(substr((string)$user['u_pswd'], 0, 16), 16, '*') == substr($is, 1, 16)
                    && (!$sp || str_pad(substr((string)$user['u_atime'], 0, 10), 10, '#') == substr($is, 17, 10))
                )
                {
                    return $id;
                }
            }
        }

        return '';
    }

    /**
     * verify
     * 
     * @param array $record
     * @return bool
     */
    public function verify($record)
    {
        if (empty($record) || !is_array($record))
        {
            return false;
        }

        if (isset($record['u_hid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['u_hid'])
                || empty($record['u_hid'])
                || strlen($record['u_hid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['u_sign']))
        {
            if (!is_string($record['u_sign'])
                || trim($record['u_sign']) == ''
                || mb_strlen($record['u_sign']) > 100
            )
            {
                return false;
            }
        }

        if (isset($record['u_pswd']))
        {
            if (!is_string($record['u_pswd'])
                || trim($record['u_pswd']) == ''
                || mb_strlen($record['u_pswd']) > 32
            )
            {
                return false;
            }
        }

        if (isset($record['u_salt']))
        {
            if (!is_string($record['u_salt'])
                || trim($record['u_salt']) == ''
            )
            {
                return false;
            }
        }

        if (isset($record['u_role']))
        {
            if (!Zyon_Util::isUnsignedInt($record['u_role'])
                || strlen($record['u_role']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['u_email']))
        {
            if (!Zyon_Util::isEmail($record['u_email'])
                || mb_strlen($record['u_email']) > 100
            )
            {
                return false;
            }
        }

        if (isset($record['u_phone']))
        {
            if (!is_string($record['u_phone'])
                || mb_strlen($record['u_phone']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['u_realname']))
        {
            if (!is_string($record['u_realname'])
                || trim($record['u_realname']) == ''
                || mb_strlen($record['u_realname']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['u_nickname']))
        {
            if (!is_string($record['u_nickname'])
                || mb_strlen($record['u_nickname']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['u_rolename']))
        {
            if (!is_string($record['u_rolename'])
                || mb_strlen($record['u_rolename']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['u_permit']))
        {
            if (!Zyon_Util::isUnsignedInt($record['u_permit'])
                || strlen($record['u_permit']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['u_status']))
        {
            if (!Zyon_Util::isUnsignedInt($record['u_status'])
                || strlen($record['u_status']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['u_active']) && !($record['u_active'] instanceof Zend_Db_Expr))
        {
            if (!Zyon_Util::isUnsignedInt($record['u_active'])
                || strlen($record['u_active']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['u_gender']))
        {
            if (!Zyon_Util::isUnsignedInt($record['u_gender'])
                || strlen($record['u_gender']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['u_idtype']))
        {
            if (!Zyon_Util::isUnsignedInt($record['u_idtype'])
                || strlen($record['u_idtype']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['u_idno']))
        {
            if (!is_string($record['u_idno'])
                || mb_strlen($record['u_idno']) > 30
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewUser
     * 
     * @param string $username 
     * @param string $realname
     * @param string $password 
     * @return array
     */
    public function getNewUser($username, $realname, $password = null)
    {
        $user = array();
        $user['u_realname']  = (string)$realname;
        $user['u_salt']  = (string)rand(1000, 9999);
        $user['u_sign']  = (string)$username;
        $user['u_email'] = (string)$username;

        if ($password)
        {
            $user['u_pswd'] = $this->getEncPwd((string)$password, $user['u_salt']);
        }

        return $user;
    }

    /**
     * getUser
     * 
     * @param int $id 
     * @return array
     */
    public function getUser($id)
    {
        if (empty($id) || !is_numeric($id))
        {
            return false;
        }

        if ($ret = $this->cache()->load($key = $this->hash($id)))
        {
            return $ret;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('user'))->where('u_id = ?')->limit(1);
            $ret = $this->dbase()->fetchRow($sql, $id);

            if ($ret)
            {
                $this->cache()->save($ret, $key);
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * addUser
     * 
     * @param array $map
     * @return int 
     */
    public function addUser($map)
    {
        if (empty($map) || !is_array($map))
        {
            return false;
        }

        if (!$this->verify($map)
            || !isset($map['u_hid'])
            || !isset($map['u_sign'])
            || !isset($map['u_salt'][3]) || !is_string($map['u_salt'])
            || !isset($map['u_realname'])
            || !isset($map['u_email'])
        )
        {
            return false;
        }

        if ($this->getUserByEmail($map['u_email']))
        {
            return false;
        }

        if (!isset($map['u_ctime']))
        {
            $map['u_ctime'] = time();
        }

        if (!isset($map['u_mtime']))
        {
            $map['u_mtime'] = $map['u_ctime'];
        }

        if (!isset($map['u_atime']))
        {
            $map['u_atime'] = 0;
        }

        try
        {
            $this->dbase()->insert($this->tname('user'), $map);
            $ret = $this->dbase()->lastInsertId();
            if ($ret && ($this->cache()->load($key = $this->hash('ids.hid' . $map['u_hid']))))
            {
                $this->cache()->remove($key);
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * delUser
     * 
     * @param int $id
     * @return int
     */
    public function delUser($id)
    {
        if (empty($id) || !is_numeric($id))
        {
            return false;
        }

        try
        {
            if ($ret = $this->dbase()->delete($this->tname('user'), 'u_id = ' . $this->quote($id)));
            {
                if ($this->cache()->load($key = $this->hash($id)))
                {
                    $this->cache()->remove($key);
                }

                if ($this->cache()->load($key = $this->hash('ids.hid' . $ret['u_hid'])))
                {
                    $this->cache()->remove($key);
                }
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * modUser
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modUser($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map))
        {
            return false;
        }

        if (!isset($map['u_mtime']))
        {
            $map['u_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('user'), $map, 'u_id = ' . $this->quote($id));
            if ($ret && $this->cache()->load($key = $this->hash($id)))
            {
                $this->cache()->remove($key);
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * delNonactivatedUser
     * 
     * @param int $lifetime Default is 3 days.
     * @return int
     */
    public function delNonactivatedUser($lifetime = 259200)
    {
        if (!is_int($lifetime) || ($ctime = time() - $lifetime) < 0)
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('user'), 'u_id')
                ->where('u_active = 0')->where('u_ctime <= ?');

            $ids = $this->dbase()->fetchCol($sql, $ctime);

            if ($ids)
            {
                $ret = $this->dbase()->delete(
                    $this->tname('user'),
                    "u_active = 0 && u_id IN (" . implode(',', array_map(array($this, 'quote'), $ids)) . ")"
                );

                if ($ret)
                {
                    foreach ($ids as $val)
                    {
                        $this->cache()->remove($this->hash($val));
                    }
                }
            }

            return count($ids);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getUserByEmail
     * 
     * @param string $email 
     * @return array
     */
    public function getUserByEmail($email)
    {
        if (empty($email) || !Zyon_Util::isEmail($email))
        {
            return false;
        }

        try
        {
            if ($id = $this->cache()->load($key = $this->hash($email)))
            {
                if (($ret = $this->getUser($id)) && $ret['u_email'] === $email)
                {
                    return $ret;
                }

                $this->cache()->remove($key);
            }

            $sql = $this->dbase()->select()->from($this->tname('user'))
                ->where('u_email = :email')
                // ->where('u_hid = :hid')
                ->limit(1);
            if ($ret = $this->dbase()->fetchRow($sql, array(
                'email' => $email,
                // 'hid' => $hid,
            )))
            {
                $this->cache()->save($ret['u_id'], $key);
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getUserIdsByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getUserIdsByHid($hid)
    {
        if (empty($hid) || !is_numeric($hid))
        {
            return false;
        }

        if ($ret = $this->cache()->load($key = $this->hash('ids.hid' . $hid)))
        {
            return $ret;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('user'), $this->expr('GROUP_CONCAT(u_id)'))
                ->where('u_hid = ?');

            $ret = $this->dbase()->fetchOne($sql, $hid);
            $ret = empty($ret) ? array() : explode(',', $ret);

            $this->cache()->save($ret, $key);

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getUsableUserIdsByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getUsableUserIdsByHid($hid)
    {
        if (empty($hid) || !is_numeric($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('user'), $this->expr('GROUP_CONCAT(u_id)'))
                ->where('u_status > 0')
                ->where('u_hid = ?');

            $ret = $this->dbase()->fetchOne($sql, $hid);
            return empty($ret) ? array() : explode(',', $ret);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getUserAryByIds
     * 
     * @param array $ids 
     * @return array
     */
    public function getUserAryByIds($ids)
    {
        if (!is_array($ids))
        {
            return false;
        }

        if (empty($ids))
        {
            return array();
        }

        $ids = array_values(array_unique($ids));
        foreach ($ids as $val)
        {
            if (!is_numeric($val))
            {
                return false;
            }
        }
        $ids = array_combine(array_map(array($this, 'hash'), $ids), $ids);

        if ($ret = $this->cache()->load(array_keys($ids)))
        {
            if (count($ret) === count($ids))
            {
                return array_values($ret);
            }
            else
            {
                $ids = array_diff_key($ids, $ret);
                $ret = array_values($ret);
            }
        }
        else
        {
            $ret = array();
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('user'))
                ->where('u_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['u_id']));
            }

            return $ret;
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getUserAryByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getUserAryByHid($hid)
    {
        return $this->getUserAryByIds($this->getUserIdsByHid($hid));
    }

    /**
     * getUsableUserAryByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getUsableUserAryByHid($hid)
    {
        return $this->getUserAryByIds($this->getUsableUserIdsByHid($hid));
    }

    /**
     * modUserPassword
     * 
     * @param int    $id 
     * @param string $password 
     * @return bool
     */
    public function modUserPassword($id, $password)
    {
        $user = $this->getUser($id);
        if (!$user)
        {
            return false;
        }

        return (bool)$this->modUser($id, array('u_pswd' => $this->getEncPwd($password, $user['u_salt'])));
    }

    /**
     * addUserPermit
     * 
     * @param int $id
     * @param int $permitCode
     * @return bool
     */
    public function addUserPermit($id, $permitCode)
    {
        if (empty($id) || !is_numeric($id)
            || empty($permitCode) || !is_numeric($permitCode))
        {
            return false;
        }

        return $this->modUser($id, array('u_permit' => $this->expr('u_permit | ' . $permitCode)));
    }

    /**
     * delUserPermit
     * 
     * @param int $id
     * @param int $permitCode
     * @return bool
     */
    public function delUserPermit($id, $permitCode)
    {
        if (empty($id) || !is_numeric($id)
            || empty($permitCode) || !is_numeric($permitCode))
        {
            return false;
        }

        return $this->modUser($id, array('u_permit' => $this->expr('u_permit & ' . ~$permitCode)));
    }

    /**
     * chkUserPassword
     * 
     * @param int    $id 
     * @param string $password 
     * @return bool 
     */
    public function chkUserPassword($id, $password)
    {
        $user = $this->getUser($id);
        if (!$user)
        {
            return false;
        }

        return $this->getEncPwd($password, $user['u_salt']) === $user['u_pswd'];
    }

    /**
     * getUserEncPwd
     * 
     * @param mixed $id
     * @param mixed $password
     * @return string
     */
    public function getUserEncPwd($id, $password)
    {
        $user = $this->getUser($id);
        if (!$user)
        {
            return;
        }

        return $this->getEncPwd($password, $user['u_salt']);
    }

    /**
     * getEncPwd
     * 
     * @param string $pwd
     * @param string $key
     * @return string
     */
    public function getEncPwd($pwd, $key)
    {
        $pwd = (string)$pwd;
        $key = (string)$key;

        return md5(strlen($pwd) . 'i' . $pwd . $key . 'i' . strlen($key));
    }

    /**
     * isIdType
     * 
     * @param mixed $idtype 
     * @return bool
     */
    public function isIdType($idtype)
    {
        return isset(static::$_idtypes[$idtype]);
    }

    /**
     * getIdTypeAry
     * 
     * @return array
     */
    public function getIdTypes()
    {
        return static::$_idtypes;
    }

    /**
     * getActivatedUserNumByHid
     * 
     * @param int $hid
     * @return int
     */
    public function getActivatedUserNumByHid($hid)
    {
        if (empty($hid) || !is_numeric($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('user'), $this->expr('count(*)'))
                ->where('u_hid = ?')
                ->where('u_active & ' . USER_ACTIVE_DL . ' > 0');
            return (int)$this->dbase()->fetchOne($sql, $hid);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }
}
// End of file : User.php
