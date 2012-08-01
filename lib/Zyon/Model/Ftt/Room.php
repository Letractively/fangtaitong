<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Room extends Zyon_Model_Ftt
{
    /**
     * _status
     * 
     * @var array
     */
    protected static $_status = array();

    /**
     * _rtstat
     * 
     * @var array
     */
    protected static $_rtstat = array();

    /**
     * _action
     * 
     * @var array
     */
    protected static $_action = array();

    /**
     * _handle
     * 
     * @var array
     */
    protected static $_handle = array(
        ROOM_STATUS_GJKF => array(ROOM_ACTION_KLFJ),
        ROOM_STATUS_RZZ  => array(ROOM_ACTION_KLFJ, ROOM_ACTION_TFJZ),
    );

    /**
     * _RoomAttrs
     * name => code
     * 
     * @var array
     */
    protected static $_roomAttrs = array();

    /**
     * _RoomViews
     * name => code
     * 
     * @var array
     */
    protected static $_roomViews = array();

    /**
     * _sysRoomLayouts
     * name => size
     * 
     * @var array
     */
    protected static $_sysRoomLayouts = array();

    /**
     * _prepare
     * 
     * @return void
     */
    protected function _prepare()
    {
        if (empty(static::$_status))
        {
            static::$_status[getRoomStatusNameByCode(ROOM_STATUS_GJKF)] = ROOM_STATUS_GJKF;
            static::$_status[getRoomStatusNameByCode(ROOM_STATUS_RZZ)] = ROOM_STATUS_RZZ;
        }

        if (empty(static::$_rtstat))
        {
            static::$_rtstat[ROOM_REALTIME_STATUS_TY] = getRoomRealTimeStatusNameByCode(ROOM_REALTIME_STATUS_TY);
            static::$_rtstat[ROOM_REALTIME_STATUS_YC] = getRoomRealTimeStatusNameByCode(ROOM_REALTIME_STATUS_YC);
        }

        if (empty(static::$_action))
        {
            static::$_action[ROOM_ACTION_KLFJ] = array(
                'name' => getRoomActionNameByCode(ROOM_ACTION_KLFJ),
                'code' => ROOM_ACTION_KLFJ,
            );

            static::$_action[ROOM_ACTION_TFJZ] = array(
                'name' => getRoomActionNameByCode(ROOM_ACTION_TFJZ),
                'code' => ROOM_ACTION_TFJZ,
            );
        }

        if (empty(static::$_roomAttrs))
        {
            static::$_roomAttrs[__('ROOM_ATTR_YCFT')] = ROOM_ATTR_YCFT;
        }

        if (empty(static::$_roomViews))
        {
            static::$_roomViews[__('园景')] = 2;
            static::$_roomViews[__('山景')] = 4;
            static::$_roomViews[__('河景')] = 8;
            static::$_roomViews[__('湖景')] = 16;
            static::$_roomViews[__('街景')] = 32;
            static::$_roomViews[__('海景')] = 64;

            static::$_roomViews[__('其它')] = 1;
        }

        if (empty(static::$_sysRoomLayouts))
        {
            static::$_sysRoomLayouts[__('一房')] = 0;
            static::$_sysRoomLayouts[__('一房一厅')] = 0;
            static::$_sysRoomLayouts[__('两房一厅')] = 0;
            static::$_sysRoomLayouts[__('两房两厅')] = 0;
            static::$_sysRoomLayouts[__('三房一厅')] = 0;
            static::$_sysRoomLayouts[__('三房两厅')] = 0;
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

        if (isset($record['r_hid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['r_hid'])
                || strlen($record['r_hid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['r_attr']))
        {
            if (!Zyon_Util::isUnsignedInt($record['r_attr'])
                || strlen($record['r_attr']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['r_name']))
        {
            if (!is_string($record['r_name'])
                || trim($record['r_name']) == ''
                || mb_strlen($record['r_name']) > 15
            )
            {
                return false;
            }
        }

        if (isset($record['r_type']))
        {
            if (!is_string($record['r_type'])
                || trim($record['r_type']) == ''
                || mb_strlen($record['r_type']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['r_zone']))
        {
            if (!is_string($record['r_zone'])
                || mb_strlen($record['r_zone']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['r_area']))
        {
            if (!is_string($record['r_area'])
                || mb_strlen($record['r_area']) > 50
            )
            {
                return false;
            }
        }

        if (isset($record['r_layout']))
        {
            if (!is_string($record['r_layout'])
                || mb_strlen($record['r_layout']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['r_address']))
        {
            if (!is_string($record['r_address'])
                || mb_strlen($record['r_address']) > 200
            )
            {
                return false;
            }
        }

        if (isset($record['r_price']))
        {
            if (!Zyon_Util::isUnsignedInt($record['r_price'])
                || strlen($record['r_price']) > 7
            )
            {
                return false;
            }
        }

        if (isset($record['r_btime']))
        {
            if (!Zyon_Util::isUnsignedInt($record['r_btime'])
                || strlen($record['r_btime']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['r_etime']))
        {
            if (!Zyon_Util::isUnsignedInt($record['r_etime'])
                || strlen($record['r_etime']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['r_otime']))
        {
            if (!Zyon_Util::isUnsignedInt($record['r_otime'])
                || strlen($record['r_otime']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['r_status']))
        {
            if (!$this->getStateNameByCode($record['r_status'])
                || strlen($record['r_status']) > 3
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewRoom
     * 
     * @param int    $hid 
     * @param string $name 
     * @param string $type
     * @param int    $price
     * @return array
     */
    public function getNewRoom($hid, $name, $type, $price)
    {
        return array(
            'r_hid'   => $hid,
            'r_name'  => $name,
            'r_type'  => $type,
            'r_price' => $price,
        );
    }

    /**
     * getStatus
     * 
     * @return array
     */
    public function getStatus()
    {
        return static::$_status;
    }

    /**
     * getStateNameByCode
     * 
     * @param string $code
     * @return string
     */
    public function getStateNameByCode($code)
    {
        return array_search($code, static::$_status, true);
    }

    /**
     * getStateCodeByName
     * 
     * @param string $name
     * @return string
     */
    public function getStateCodeByName($name)
    {
        return isset(static::$_status[$name]) ? static::$_status[$name] : null;
    }

    /**
     * getRealTimeStateCodes
     * 
     * @param array $room
     * @param array $hotel
     * @return array
     */
    public function getRealTimeStateCodes($room, $hotel)
    {
        $ret = array();
        if (is_array($hotel) && !empty($hotel['h_id']) && is_array($room) && !empty($room['r_hid'])
            && $hotel['h_id'] === $room['r_hid'])
        {
            $now = time();

            if ($room['r_btime'] <= $now && $room['r_etime'] >= $now)
            {
                $ret[] = ROOM_REALTIME_STATUS_TY;
            }

            if ($room['r_attr'] & ROOM_ATTR_YCFT)
            {
                $ret[] = ROOM_REALTIME_STATUS_YC;
            }
        }

        return $ret;
    }

    /**
     * getRealTimeStateNames
     * 
     * @param array $room
     * @param array $hotel
     * @return array
     */
    public function getRealTimeStateNames($room, $hotel)
    {
        $names = array();
        $codes = $this->getRealTimeStateCodes($room, $hotel);
        if ($codes)
        {
            foreach ($codes as $code)
            {
                $names[] = static::$_rtstat[$code];
            }
        }

        return $names;
    }

    /**
     * getActions
     * 
     * @return array
     */
    public function getActions()
    {
        return static::$_action;
    }

    /**
     * getUsableActions
     * 
     * @param array $room 
     * @param array $hotel
     * @return array
     */
    public function getUsableActions($room, $hotel)
    {
        $code = $room['r_status'];
        if (empty(static::$_handle[$code]))
        {
            return array();
        }

        $actions = array();
        foreach (static::$_handle[$code] as $idx)
        {
            isset(static::$_action[$idx]) AND $actions[$idx] = static::$_action[$idx];
        }

        return $actions;
    }

    /**
     * addRoom
     * 
     * @param array $map
     * @return string
     */
    public function addRoom($map)
    {
        if (empty($map) || !is_array($map))
        {
            return false;
        }

        if (!$this->verify($map)
            || !isset($map['r_name']) || !isset($map['r_type']) || !isset($map['r_price']) || empty($map['r_hid']))
        {
            return false;
        }

        if (!isset($map['r_ctime']))
        {
            $map['r_ctime'] = time();
        }

        if (!isset($map['r_mtime']))
        {
            $map['r_mtime'] = $map['r_ctime'];
        }

        if ($this->getRoomIdByName($map['r_name'], $map['r_hid']))
        {
            return false;
        }

        try
        {
            $this->dbase()->insert($this->tname('room'), $map);
            $ret = $this->dbase()->lastInsertId();
            if ($ret && ($this->cache()->load($key = $this->hash('ids.hid' . $map['r_hid']))))
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
     * getRoom
     * 
     * @param int $id 
     * @return array
     */
    public function getRoom($id)
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
            $sql = $this->dbase()->select()->from($this->tname('room'))->where('r_id = ?')->limit(1);
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
     * modRoom
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modRoom($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map))
        {
            return false;
        }

        if (isset($map['r_name']) || isset($map['r_hid']))
        {
            return false;
        }

        if (!isset($map['r_mtime']))
        {
            $map['r_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('room'), $map, 'r_id = ' . $this->quote($id));
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
     * modRoomByIds
     * 
     * @param array $ids
     * @param array $map
     * @return int
     */
    public function modRoomByIds($ids, $map)
    {
        if (empty($ids) || !is_array($ids) || !$this->verify($map))
        {
            return false;
        }

        if (isset($map['r_name']) || isset($map['r_hid']))
        {
            return false;
        }

        foreach ($ids as &$val)
        {
            if (!Zyon_Util::isUnsignedInt($val))
            {
                return false;
            }
        }
        unset($val);

        if (!isset($map['r_mtime']))
        {
            $map['r_mtime'] = time();
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('room'), $map, sprintf('r_id IN (%s)', implode(',', $ids)));
            if ($ret)
            {
                foreach ($ids as &$val)
                {
                    if ($this->cache()->load($key = $this->hash($val)))
                    {
                        $this->cache()->remove($key);
                    }
                }
                unset($val);
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
     * getRoomIdByName
     * 
     * @param string $name
     * @param int    $hid
     * @return int
     */
    public function getRoomIdByName($name, $hid)
    {
        if (empty($name) || empty($hid) || !is_numeric($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('room'), 'r_id')
                ->where('r_name = :name')
                ->where('r_hid = :hid')
                ->limit(1);
            return $this->dbase()->fetchOne($sql, array('name' => $name, 'hid' => $hid));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getRoomIdByName
     * 
     * @param string $name
     * @param int    $hid
     * @return array
     */
    public function getRoomByName($name, $hid)
    {
        if ($rid = $this->getRoomIdByName($name, $hid))
        {
            return $this->getRoom($rid);
        }

        return false;
    }

    /**
     * getRoomAryByIds
     * 
     * @param array $ids 
     * @param mixed $hid
     * @return array
     */
    public function getRoomAryByIds($ids, $hid = 0)
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
            $sql = $this->dbase()->select()->from($this->tname('room'))
                ->where('r_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');

            if (func_num_args() > 1)
            {
                $sql->where('r_hid = ' . $this->quote($hid));
            }

            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['r_id']));
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
     * getRoomIdsByHid
     * 
     * @param int $hid 
     * @return array
     */
    public function getRoomIdsByHid($hid)
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
            $sql = $this->dbase()->select()->from($this->tname('room'), $this->expr('GROUP_CONCAT(r_id)'))
                ->where('r_hid = ?');
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
     * getRoomAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getRoomAryByHid($hid)
    {
        $ids = $this->getRoomIdsByHid($hid);
        if (empty($ids))
        {
            return false;
        }

        return $this->getRoomAryByIds($ids);
    }

    /**
     * getActivatedRoomNumByHid
     * 
     * @param int $hid
     * @return int
     */
    public function getActivatedRoomNumByHid($hid)
    {
        if (empty($hid) || !is_numeric($hid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('room'), $this->expr('count(*)'))
                ->where('r_hid = ?');
            return (int)$this->dbase()->fetchOne($sql, $hid);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * chkCanDoLive
     * 
     * @param int $rid
     * @return bool
     */
    public function chkCanDoLiveByRid($rid)
    {
        $room = $this->getRoom($rid);
        return !empty($room['r_status']) && $room['r_status'] === '1';
    }

    /**
     * getViewAryByRoomAry
     * 
     * @param array $rooms
     * @return array
     */
    public function getViewAryByRoomAry($rooms)
    {
        if (!is_array($rooms))
        {
            return false;
        }

        $ret = static::$_roomViews;
        foreach ($ret as &$val)
        {
            $val = 0;
        }
        unset($val);

        foreach ($rooms as $val)
        {
            if (empty($val['r_view']))
            {
                continue;
            }

            $num = (int)$val['r_view'];
            foreach (static::$_roomViews as $key => $val)
            {
                if ($num & $val)
                {
                    $ret[$key] += 1;
                }
            }
        }

        return $ret;
    }

    /**
     * getViewAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getViewAryByHid($hid)
    {
        $rooms = $this->getRoomAryByHid($hid);
        return $this->getViewAryByRooms($rooms ?: array());
    }

    /**
     * getViewAryByCode
     * 
     * @param int $code
     * @return array
     */
    public function getViewAryByCode($code)
    {
        $num = (int)$code;
        $ret = array();
        foreach (static::$_roomViews as $key => $val)
        {
            isset($ret[$key]) OR $ret[$key] = 0;
            if ($val & $num)
            {
                $ret[$key] += 1;
            }
        }

        return $ret;
    }

    /**
     * getViewCodeByList
     * 
     * @param array $list
     * @return int
     */
    public function getViewCodeByList(array $list)
    {
        $code = 0;
        foreach ($list as $name)
        {
            if (array_key_exists($name, static::$_roomViews))
            {
                $code = $code | static::$_roomViews[$name];
            }
        }

        return $code;
    }

    /**
     * getViewListByCode
     * 
     * @param int $code
     * @return array
     */
    public function getViewListByCode($code)
    {
        $num = (int)$code;
        $ret = array();
        foreach (static::$_roomViews as $key => $val)
        {
            if ($num & $val)
            {
                $ret[] = $key; 
            }
        }

        return $ret;
    }

    /**
     * getViewNameByCode
     * 
     * @param int $code
     * @return string
     */
    public function getViewNameByCode($code)
    {
        return implode(',', $this->getViewListByCode($code));
    }

    /**
     * getLayoutAryByRoomAry
     * 
     * @param array $rooms
     * @return array
     */
    public function getLayoutAryByRoomAry($rooms)
    {
        if (empty($rooms) || !is_array($rooms))
        {
            return array();
        }

        $ret = array();
        foreach ($rooms as $val)
        {
            if (empty($val['r_layout']))
            {
                continue;
            }

            $ret[$val['r_layout']] = empty($ret[$val['r_layout']]) ? 1 : $ret[$val['r_layout']]+1;
        }

        return $ret;
    }

    /**
     * getAttrAryByCode
     * 
     * @param int $code
     * @return array
     */
    public function getAttrAryByCode($code)
    {
        $num = (int)$code;
        $ret = array();
        foreach (static::$_roomAttrs as $key => $val)
        {
            isset($ret[$key]) OR $ret[$key] = 0;
            if ($val & $num)
            {
                $ret[$key] += 1;
            }
        }

        return $ret;
    }

    /**
     * getAttrCodeByList
     * 
     * @param array $list
     * @return int
     */
    public function getAttrCodeByList(array $list)
    {
        $code = 0;
        foreach ($list as $name)
        {
            if (array_key_exists($name, static::$_roomAttrs))
            {
                $code = $code | static::$_roomAttrs[$name];
            }
        }

        return $code;
    }

    /**
     * getAttrListByCode
     * 
     * @param int $code
     * @return array
     */
    public function getAttrListByCode($code)
    {
        $num = (int)$code;
        $ret = array();
        foreach (static::$_roomAttrs as $key => $val)
        {
            if ($num & $val)
            {
                $ret[] = $key; 
            }
        }

        return $ret;
    }

    /**
     * getRealTimeStatusAryByRoomAry
     * 
     * @param array $rooms
     * @return array
     */
    public function getRealTimeStatusAryByRoomAry($rooms)
    {
        if (empty($rooms) || !is_array($rooms))
        {
            return array();
        }

        $ret = array();
        $ins = array();
        foreach ($rooms as $val)
        {
            isset($ins[$val['r_hid']]) OR $ins[$val['r_hid']] = Model::factory('hotel', 'ftt')->getHotel($val['r_hid']);

            $keys = $this->getRealTimeStateNames($val, $ins[$val['r_hid']]);
            foreach ($keys as $key)
            {
                $ret[$key] = empty($ret[$key]) ? 1 : $ret[$key]+1;
            }
        }

        return $ret;
    }

    /**
     * getStatusAryByRoomAry
     * 
     * @param array $rooms
     * @return array
     */
    public function getStatusAryByRoomAry($rooms)
    {
        if (empty($rooms) || !is_array($rooms))
        {
            return array();
        }

        $ret = array();
        foreach ($rooms as $val)
        {
            if (!isset($val['r_status']))
            {
                continue;
            }

            $key = $this->getStateNameByCode($val['r_status']);
            $ret[$key] = empty($ret[$key]) ? 1 : $ret[$key]+1;
        }

        return $ret;
    }

    /**
     * getLayoutAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getLayoutAryByHid($hid)
    {
        return $this->getLayoutAryByRooms($this->getRoomAryByHid($hid));
    }

    /**
     * getSysLayoutAry
     * 
     * @return array
     */
    public function getSysLayoutAry()
    {
        return static::$_sysRoomLayouts;
    }

    /**
     * getIndexAryByRoomAry
     * 
     * @param array $rooms
     * @return array
     */
    public function getIndexAryByRoomAry($rooms)
    {
        if (!is_array($rooms))
        {
            return false;
        }

        $index = array(
            'area'    => array(),
            'zone'    => array(),
            'type'    => array(),
            'address' => array(),
            'view'    => $this->getViewAryByRoomAry($rooms),
            'layout'  => array_merge($this->getSysLayoutAry(), $this->getLayoutAryByRoomAry($rooms)),
            'status'  => array_merge(
                array_fill_keys(array_keys(static::$_status), 0),
                $this->getStatusAryByRoomAry($rooms)
            ),
            'rtstat'  => $this->getRealTimeStatusAryByRoomAry($rooms),
        );

        foreach ($rooms as $val)
        {
            foreach (array('area', 'zone', 'type', 'address') as $key)
            {
                if (empty($val['r_'.$key]))
                {
                    continue;
                }

                $index[$key][$val['r_'.$key]] = empty($index[$key][$val['r_'.$key]]) ? 1 : $index[$key][$val['r_'.$key]]+1;
            }
        }

        foreach ($index as &$val)
        {
            arsort($val);
        }

        return $index;
    }

    /**
     * getIndexAryByHid
     * 
     * @param int $hid
     * @return array
     */
    public function getIndexAryByHid($hid)
    {
        return $this->getIndexAryByRoomAry($this->getRoomAryByHid($hid)) ?: array();
    }

    /**
     * getRoomIdsGroupWithTypeByDayAndHid
     * 
     * @param date $day
     * @param int  $hid
     * @return array {$type: $rids, ...}
     */
    public function getRoomIdsGroupWithTypeByDayAndHid($day, $hid)
    {
        if (!Zyon_Util::isDate($day) || !Zyon_Util::isUnsignedInt($hid))
        {
            return false;
        }

        $dtm = strtotime($day)+86399;

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('room'), 'r_type, GROUP_CONCAT(r_id)')
                ->where('r_hid = :hid')
                ->where('r_otime <= :dtm')
                ->group('r_type');

            return $this->dbase()->fetchPairs($sql, array('hid' => $hid, 'dtm' => $dtm));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }

    }

    /**
     * calLiveNumByDay
     * 
     * @param date $date
     * @return int
     */
    public function calLiveNumByDateAndRids($date, $rids)
    {
        if (!Zyon_Util::isDate($date))
        {
            return false;
        }

        $time = strtotime($date)+86399;

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('order'), 'count(o_id)')
                ->where("o_itime <= :dtm AND (o_status = :sta OR o_otime > :dtm)");
            $rids AND $sql->where(sprintf("o_rid IN (%s)", is_string($rids) ? $rids : implode(',', $rids)));

            return $this->dbase()->fetchOne($sql, array('dtm' => $time, 'sta' => ORDER_STATUS_ZZ));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }
}
// End of file : Room.php
