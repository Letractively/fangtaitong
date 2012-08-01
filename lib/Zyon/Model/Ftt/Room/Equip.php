<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Room_Equip extends Zyon_Model_Ftt
{
    /**
     * _types
     * 
     * @var array
     */
    protected static $_types = array();

    /**
     * _prepare
     * 
     * @return void
     */
    protected function _prepare()
    {
        if (empty(static::$_types))
        {
            static::$_types[__('其他')] = '0';
            static::$_types[__('床型')] = '1';
            static::$_types[__('电器')] = '2';
            static::$_types[__('厨具')] = '3';
            static::$_types[__('家具')] = '4';
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

        if (isset($record['re_hid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['re_hid'])
                || empty($record['re_hid'])
                || strlen($record['re_hid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['re_rid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['re_rid'])
                || empty($record['re_rid'])
                || strlen($record['re_rid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['re_type']))
        {
            if (!Zyon_Util::isInt($record['re_type'])
                || !in_array($record['re_type'], static::$_types, true)
                || strlen($record['re_type']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['re_qnty']))
        {
            if (!Zyon_Util::isUnsignedInt($record['re_qnty'])
                || strlen($record['re_qnty']) > 5
            )
            {
                return false;
            }
        }

        if (isset($record['re_name']))
        {
            if (!is_string($record['re_name'])
                || trim($record['re_name']) == ''
                || mb_strlen($record['re_name']) > 30
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewEquip
     * 
     * @param string $name
     * @param int $qnty
     * @param int $type
     * @param int $hid
     * @param int $rid
     * @return array
     */
    public function getNewEquip($name, $qnty = '1', $type = '0', $hid = '0', $rid = '0')
    {
        return array(
            're_name' => $name,
            're_qnty' => $qnty,
            're_type' => $type,
            're_hid'  => $hid,
            're_rid'  => $rid,
        );
    }

    /**
     * getTypes
     * 
     * @return array
     */
    public function getTypes()
    {
        return static::$_types;
    }

    /**
     * getTypeByName
     * 
     * @param string $name
     * @return int
     */
    public function getTypeByName($name)
    {
        return isset(static::$_types[$name]) ? static::$_types[$name] : null;
    }

    /**
     * getNameByType
     * 
     * @param mixed $type
     * @return mixed
     */
    public function getNameByType($type)
    {
        return array_search($type, static::$_types, true);
    }

    /**
     * addEquip
     * 
     * @param array $map
     * @return string
     */
    public function addEquip($map)
    {
        if (!$this->verify($map)
            || !isset($map['re_type'])
            || empty($map['re_name'])
            || !isset($map['re_qnty'])
            || !isset($map['re_hid'])
            || !isset($map['re_rid'])
        )
        {
            return false;
        }

        try
        {
            $this->dbase()->insert($this->tname('room_equip'), $map);
            return $this->dbase()->lastInsertId();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * addEquipAry
     * 
     * @param array $ary
     * @return int
     */
    public function addEquipAry($ary)
    {
        if (empty($ary) || !is_array($ary))
        {
            return false;
        }

        $vas = array();
        foreach ($ary as $map)
        {
            if (!$this->verify($map)
                || !isset($map['re_type'])
                || empty($map['re_name'])
                || !isset($map['re_qnty'])
                || !isset($map['re_hid'])
                || !isset($map['re_rid'])
            )
            {
                return false;
            }

            $vas[] = '('
                . $this->quote($map['re_type']) . ','
                . $this->quote($map['re_name']) . ','
                . $this->quote($map['re_qnty']) . ','
                . $this->quote($map['re_hid']) . ','
                . $this->quote($map['re_rid']) . ')';
        }
        $vas = implode(',', $vas);

        try
        {
            return $this->dbase()->query(sprintf('INSERT INTO %s (re_type, re_name, re_qnty, re_hid, re_rid) VALUES %s',
                $this->tname('room_equip'),
                $vas
            ))->rowCount();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getEquip
     * 
     * @param int $id 
     * @return array
     */
    public function getEquip($id)
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
            $sql = $this->dbase()->select()->from($this->tname('room_equip'))->where('re_id = ?')->limit(1);
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
     * modEquip
     * 
     * @param int $id
     * @param array $map
     * @return int
     */
    public function modEquip($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map))
        {
            return false;
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('room_equip'), $map, 're_id = ' . $this->quote($id));
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
     * modEquipQnty
     *
     * @param int $id
     * @param int $qnty
     * @return int
     */
    public function modEquipQnty($id, $qnty)
    {
        return $this->modEquip($id, array('re_qnty' => $qnty));
    }

    /**
     * getEquipIds
     * 
     * @param array $map
     * @return array
     */
    public function getEquipIds($map)
    {
        if (!is_array($map))
        {
            return false;
        }

        if (empty($map))
        {
            return array();
        }

        if (count(array_intersect_key($map,
            array(
                're_hid' => 1, 're_rid' => 1, 're_type' => 1, 're_name' => 1, 're_qnty' => 1
            ))) !== count($map)
        )
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('room_equip'), $this->expr('GROUP_CONCAT(re_id)'));
            foreach (array_keys($map) as $val)
            {
                $sql->where($val . ' =:' . $val);
            }

            $ret = $this->dbase()->fetchOne($sql, $map);
            return empty($ret) ? array() : explode(',', $ret);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getEquipAryByIds
     * 
     * @param array $ids 
     * @return array
     */
    public function getEquipAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('room_equip'))
                ->where('re_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['re_id']));
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
     * getEquipAry
     * 
     * @param array $map
     * @return array
     */
    public function getEquipAry($map)
    {
        return $this->getEquipAryByIds($this->getEquipIds($map));
    }

    /**
     * getEquipAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getEquipAryByHid($hid)
    {
        return $this->getEquipAry(array('re_hid' => $hid));
    }

    /**
     * getEquipAryByRid
     * 
     * @param int $rid
     * @return array
     */
    public function getEquipAryByRid($rid)
    {
        return $this->getEquipAry(array('re_rid' => $rid));
    }
}
// End of file : Equip.php
