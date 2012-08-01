<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Mber extends Zyon_Model_Ftt
{
    /**
     * _idtypes
     *
     * @var array
     */
    protected static $_idtypes;

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

        if (isset($record['m_no']))
        {
            if (!is_string($record['m_no'])
                || mb_strlen($record['m_no']) > 30
            )
            {
                return false;
            }
        }

        if (isset($record['m_hid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['m_hid'])
                || empty($record['m_hid'])
                || strlen($record['m_hid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['m_wid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['m_wid'])
                || strlen($record['m_wid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['m_sync']))
        {
            if (!Zyon_Util::isUnsignedInt($record['m_sync'])
                || strlen($record['m_sync']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['m_idno']))
        {
            if (!is_string($record['m_idno'])
                || mb_strlen($record['m_idno']) > 30
            )
            {
                return false;
            }
        }

        if (isset($record['m_type']))
        {
            if (!is_string($record['m_type'])
                || trim($record['m_type']) == ''
                || mb_strlen($record['m_type']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['m_idtype']))
        {
            if (!$this->isIdType($record['m_idtype']))
            {
                return false;
            }
        }

        if (isset($record['m_email']))
        {
            if (!Zyon_Util::isEmail($record['m_email'])
                || trim($record['m_email']) == ''
                || mb_strlen($record['m_email']) > 100
            )
            {
                return false;
            }
        }

        if (isset($record['m_phone']))
        {
            if (!is_string($record['m_phone'])
                || trim($record['m_phone']) == ''
                || mb_strlen($record['m_phone']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['m_name']))
        {
            if (!is_string($record['m_name'])
                || trim($record['m_name']) == ''
                || mb_strlen($record['m_name']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['m_status']))
        {
            if (!Zyon_Util::isUnsignedInt($record['m_status'])
                || strlen($record['m_status']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['m_gender']))
        {
            if (!Zyon_Util::isUnsignedInt($record['m_gender'])
                || strlen($record['m_gender']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['m_idtype']))
        {
            if (!Zyon_Util::isUnsignedInt($record['m_idtype'])
                || strlen($record['m_idtype']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['m_memo']))
        {
            if (!is_string($record['m_memo'])
                || mb_strlen($record['m_memo']) > 500
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewMber
     * 
     * @param int $hid
     * @param string $no
     * @param string $name
     * @param int $status
     * @return array
     */
    public function getNewMber($hid, $no, $name, $status = 1)
    {
        $mber = array(
            'm_no'     => $no,
            'm_hid'    => $hid,
            'm_name'   => $name,
            'm_status' => $status,
        );

        return $mber;
    }

    /**
     * getDupMber
     *
     * @param array $mber
     * @return mixed
     */
    public function getDupMber($mber)
    {
        if (!is_array($mber) || (!isset($mber['m_no']) && !isset($mber['m_phone']) && !isset($mber['m_email'])))
        {
            return false;
        }

        $cond = array();
        if (isset($mber['m_no']))
        {
            $cond[] = 'm_no = ' . $this->quote($mber['m_no']);
        }
        if (isset($mber['m_phone']))
        {
            $cond[] = 'm_phone = ' . $this->quote($mber['m_phone']);
        }
        if (isset($mber['m_email']))
        {
            $cond[] = 'm_email = ' . $this->quote($mber['m_email']);
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('mber'))->where(implode(' OR ', $cond))->limit(1);
            return $this->dbase()->fetchRow($sql);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getMber
     * 
     * @param int $id 
     * @return array
     */
    public function getMber($id)
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
            $sql = $this->dbase()->select()->from($this->tname('mber'))->where('m_id = ?')->limit(1);
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
     * addMber
     * 
     * @param array $map
     * @return int 
     */
    public function addMber($map)
    {
        if (empty($map) || !is_array($map))
        {
            return false;
        }

        if (!$this->verify($map)
            || !isset($map['m_no'])
            || !isset($map['m_hid'])
            || (!isset($map['m_email']) && !isset($map['m_phone']))
        )
        {
            return false;
        }

        if ($this->getDupMber($map))
        {
            return false;
        }

        if (!isset($map['m_ctime']))
        {
            $map['m_ctime'] = time();
        }

        if (!isset($map['m_mtime']))
        {
            $map['m_mtime'] = $map['m_ctime'];
        }

        try
        {
            $this->dbase()->insert($this->tname('mber'), $map);
            return $this->dbase()->lastInsertId();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * delMber
     * 
     * @param int $id
     * @return int
     */
    public function delMber($id)
    {
        if (empty($id) || !is_numeric($id))
        {
            return false;
        }

        try
        {
            if ($ret = $this->dbase()->delete($this->tname('mber'), 'm_id = ' . $this->quote($id)));
            {
                if ($this->cache()->load($key = $this->hash($id)))
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
     * modMber
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modMber($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map))
        {
            return false;
        }

        if ($dup = $this->getDupMber($map))
        {
            if ($dup['m_id'] != $id)
            {
                return false;
            }
        }

        if (!isset($map['m_mtime']))
        {
            $map['m_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('mber'), $map, 'm_id = ' . $this->quote($id));
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
     * getMberByEmail
     * 
     * @param string $email 
     * @param int    $hid 
     * @return array
     */
    public function getMberByEmail($email, $hid)
    {
        if (empty($email) || !Zyon_Util::isEmail($email) || !Zyon_Util::isUnsignedInt($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('mber'))
                ->where('m_email = :email')
                ->where('m_hid = :hid')
                ->limit(1);
            return $this->dbase()->fetchRow($sql, array('email' => $email, 'hid' => $hid));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getMberByNo
     * 
     * @param string $no
     * @param int    $hid 
     * @return array
     */
    public function getMberByNo($no, $hid)
    {
        if (empty($no) || !$this->verify(array(
            'm_no' => $no,
            'm_hid' => $hid,
        )))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('mber'))
                ->where('m_no = :no')
                ->where('m_hid = :hid')
                ->limit(1);
            return $this->dbase()->fetchRow($sql, array('no' => $no, 'hid' => $hid));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getMberAryByIds
     * 
     * @param array $ids 
     * @return array
     */
    public function getMberAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('mber'))
                ->where('m_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['m_id']));
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
     * getMberTypeAryByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getMberTypeAryByHid($hid)
    {
        if (empty($hid) || !is_numeric($hid))
        {
            return false;
        }

        try
        {
            // $sql = $this->dbase()->select()->from($this->tname('mber'), $this->expr('distinct m_type COLLATE utf8_bin'))
            $sql = $this->dbase()->select()->from($this->tname('mber'), $this->expr('distinct m_type'))
                ->where('m_hid = ?');

            return $this->dbase()->fetchCol($sql, $hid);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
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
}
// End of file : Mber.php
