<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Order extends Zyon_Model_Ftt
{
    /**
     * _oattrs
     * 
     * @var array
     */
    protected static $_oattrs = array();

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
        ORDER_STATUS_YD => array(ORDER_ACTION_BLDD, ORDER_ACTION_BLRZ, ORDER_ACTION_QXDD),
        ORDER_STATUS_BL => array(ORDER_ACTION_BLRZ, ORDER_ACTION_QXDD),
        ORDER_STATUS_ZZ => array(ORDER_ACTION_BLTF),
        ORDER_STATUS_YJS => array(),
        ORDER_STATUS_YQX => array(),
    );

    /**
     * _prepare
     * 
     * @return void
     */
    protected function _prepare()
    {
        if (empty(static::$_oattrs))
        {
            static::$_oattrs[getOrderAttrNameByCode(ORDER_ATTR_YXHF)] = ORDER_ATTR_YXHF;
        }

        if (empty(static::$_status))
        {
            static::$_status[getOrderStatusNameByCode(ORDER_STATUS_YD)] = ORDER_STATUS_YD;
            static::$_status[getOrderStatusNameByCode(ORDER_STATUS_BL)] = ORDER_STATUS_BL;
            static::$_status[getOrderStatusNameByCode(ORDER_STATUS_ZZ)] = ORDER_STATUS_ZZ;
            static::$_status[getOrderStatusNameByCode(ORDER_STATUS_YJS)] = ORDER_STATUS_YJS;
            static::$_status[getOrderStatusNameByCode(ORDER_STATUS_YQX)] = ORDER_STATUS_YQX;
        }

        if (empty(static::$_rtstat))
        {
            static::$_rtstat[getOrderRealTimeStatusNameByCode(ORDER_REALTIME_STATUS_YRZ)] = ORDER_REALTIME_STATUS_YRZ;
            static::$_rtstat[getOrderRealTimeStatusNameByCode(ORDER_REALTIME_STATUS_YTF)] = ORDER_REALTIME_STATUS_YTF;
            static::$_rtstat[getOrderRealTimeStatusNameByCode(ORDER_REALTIME_STATUS_YZWZ)] = ORDER_REALTIME_STATUS_YZWZ;
            static::$_rtstat[getOrderRealTimeStatusNameByCode(ORDER_REALTIME_STATUS_YTWT)] = ORDER_REALTIME_STATUS_YTWT;
            static::$_rtstat[getOrderRealTimeStatusNameByCode(ORDER_REALTIME_STATUS_GQWZ)] = ORDER_REALTIME_STATUS_GQWZ;
            static::$_rtstat[getOrderRealTimeStatusNameByCode(ORDER_REALTIME_STATUS_FTCT)] = ORDER_REALTIME_STATUS_FTCT;
        }

        if (empty(static::$_action))
        {
            static::$_action[ORDER_ACTION_BLDD] = array(
                'name' => getOrderActionNameByCode(ORDER_ACTION_BLDD),
                'code' => ORDER_ACTION_BLDD,
            );

            static::$_action[ORDER_ACTION_QXDD] = array(
                'name' => getOrderActionNameByCode(ORDER_ACTION_QXDD),
                'code' => ORDER_ACTION_QXDD,
            );

            static::$_action[ORDER_ACTION_BLRZ] = array(
                'name' => getOrderActionNameByCode(ORDER_ACTION_BLRZ),
                'code' => ORDER_ACTION_BLRZ,
            );

            static::$_action[ORDER_ACTION_BLTF] = array(
                'name' => getOrderActionNameByCode(ORDER_ACTION_BLTF),
                'code' => ORDER_ACTION_BLTF,
            );

            static::$_action[ORDER_ACTION_BLHF] = array(
                'name' => getOrderActionNameByCode(ORDER_ACTION_BLHF),
                'code' => ORDER_ACTION_BLHF,
            );
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

        if (isset($record['o_hid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_hid'])
                || empty($record['o_hid'])
                || strlen($record['o_hid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['o_sid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_sid'])
                || empty($record['o_sid'])
                || strlen($record['o_sid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['o_bid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_bid'])
                || empty($record['o_bid'])
                || strlen($record['o_bid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['o_rid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_rid'])
                || empty($record['o_rid'])
                || strlen($record['o_rid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['o_mid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_mid'])
                || strlen($record['o_mid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['o_mno']))
        {
            if (!is_string($record['o_mno'])
                || mb_strlen($record['o_mno']) > 30
            )
            {
                return false;
            }
        }

        if (isset($record['o_room']))
        {
            if (!is_string($record['o_room'])
                || trim($record['o_room']) == ''
                || mb_strlen($record['o_room']) > 30
            )
            {
                return false;
            }
        }

        if (isset($record['o_price']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_price'])
                || strlen($record['o_price']) > 9
            )
            {
                return false;
            }
        }

        if (isset($record['o_brice']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_brice'])
                || strlen($record['o_brice']) > 9
            )
            {
                return false;
            }
        }

        if (isset($record['o_attr']) && !($record['o_attr'] instanceof Zend_Db_Expr))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_attr'])
                || strlen($record['o_attr']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['o_memo']))
        {
            if (!is_string($record['o_memo'])
                || mb_strlen($record['o_memo']) > 500
            )
            {
                return false;
            }
        }

        if (isset($record['o_btime']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_btime'])
                || strlen($record['o_btime']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['o_etime']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_etime'])
                || strlen($record['o_etime']) > 10
                || (isset($record['o_btime']) && date('Y-m-d', $record['o_etime']) === date('Y-m-d', $record['o_btime']))
            )
            {
                return false;
            }
        }

        if (isset($record['o_bdatm']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_bdatm'])
                || strlen($record['o_bdatm']) > 10
                || (isset($record['o_btime']) && date('Y-m-d', $record['o_btime']) !== date('Y-m-d', $record['o_bdatm']))
            )
            {
                return false;
            }
        }

        if (isset($record['o_edatm']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_edatm'])
                || strlen($record['o_edatm']) > 10
                || (isset($record['o_bdatm']) && $record['o_bdatm'] === $record['o_edatm'])
                || (isset($record['o_etime']) && date('Y-m-d', $record['o_etime']) !== date('Y-m-d', $record['o_edatm']))
            )
            {
                return false;
            }
        }

        if (isset($record['o_cid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_cid'])
                || strlen($record['o_cid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['o_tid']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_tid'])
                || strlen($record['o_tid']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['o_prices']))
        {
            if (!is_string($record['o_prices'])
                || trim($record['o_prices']) == ''
            )
            {
                return false;
            }
        }

        if (isset($record['o_brices']))
        {
            if (!is_string($record['o_brices'])
                || trim($record['o_brices']) == ''
            )
            {
                return false;
            }
        }

        if (isset($record['o_status']))
        {
            if (!$this->getStateNameByCode($record['o_status'])
                || strlen($record['o_status']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['o_gbker_name']))
        {
            if (!is_string($record['o_gbker_name'])
                || trim($record['o_gbker_name']) == ''
                || mb_strlen($record['o_gbker_name']) > 14
            )
            {
                return false;
            }
        }

        if (isset($record['o_gbker_idno']) && $record['o_gbker_idno'] !== '')
        {
            if (!is_string($record['o_gbker_idno'])
                || mb_strlen($record['o_gbker_idno']) > 30
            )
            {
                return false;
            }
        }

        if (isset($record['o_gbker_email']) && $record['o_gbker_email'] !== '')
        {
            if (!Zyon_Util::isEmail($record['o_gbker_email'])
                || mb_strlen($record['o_gbker_email']) > 100
            )
            {
                return false;
            }
        }

        if (isset($record['o_gbker_phone']))
        {
            if (!is_string($record['o_gbker_phone'])
                || mb_strlen($record['o_gbker_phone']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['o_gbker_idtype']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_gbker_idtype'])
                || strlen($record['o_gbker_idtype']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['o_glver_name']))
        {
            if (!is_string($record['o_glver_name'])
                || trim($record['o_glver_name']) == ''
                || mb_strlen($record['o_glver_name']) > 14
            )
            {
                return false;
            }
        }

        if (isset($record['o_glver_idno']) && $record['o_glver_idno'] !== '')
        {
            if (!is_string($record['o_glver_idno'])
                || mb_strlen($record['o_glver_idno']) > 30
            )
            {
                return false;
            }
        }

        if (isset($record['o_glver_email']) && $record['o_glver_email'] !== '')
        {
            if (!Zyon_Util::isEmail($record['o_glver_email'])
                || mb_strlen($record['o_glver_email']) > 100
            )
            {
                return false;
            }
        }

        if (isset($record['o_glver_phone']))
        {
            if (!is_string($record['o_glver_phone'])
                || mb_strlen($record['o_glver_phone']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['o_glver_idtype']))
        {
            if (!Zyon_Util::isUnsignedInt($record['o_glver_idtype'])
                || strlen($record['o_glver_idtype']) > 3
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewOrder
     * 
     * @param array  $room
     * @param int    $btime
     * @param int    $etime
     * @param string $price
     * @param string $brice
     * @param int    $state
     * @return array
     */
    public function getNewOrder(array $room, $btime, $etime, $price, $brice, $state)
    {
        return array(
            'o_hid'    => $room['r_hid'],
            'o_rid'    => $room['r_id'],
            'o_room'   => $room['r_name'],
            'o_btime'  => $btime,
            'o_etime'  => $etime,
            'o_bdatm'  => strtotime(date('Y-m-d', $btime)),
            'o_edatm'  => strtotime(date('Y-m-d', $etime)),
            'o_prices' => $price,
            'o_brices' => $brice,
            'o_status' => $state,
        );
    }

    /**
     * getNewPreOrder
     * 
     * @param array  $room
     * @param int    $btime 
     * @param int    $etime 
     * @param string $price
     * @param string $brice
     * @return array
     */
    public function getNewPreOrder(array $room, $btime, $etime, $price, $brice)
    {
        return array(
            'o_hid'     => $room['r_hid'],
            'o_rid'     => $room['r_id'],
            'o_room'    => $room['r_name'],
            'o_btime'   => $btime,
            'o_etime'   => $etime,
            'o_bdatm'  => strtotime(date('Y-m-d', $btime)),
            'o_edatm'  => strtotime(date('Y-m-d', $etime)),
            'o_prices'  => $price,
            'o_brices'  => $brice,
            'o_status'  => ORDER_STATUS_YD,
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
     * getRtstas
     * 
     * @return array
     */
    public function getRtstas()
    {
        return static::$_rtstat;
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
     * getRealTimeStateConds
     * 
     * @param array $hotel
     * @param array $stats
     * @return array
     */
    public function getRealTimeStateConds($hotel, $stats = null)
    {
        if (!is_array($hotel) || ($stats !== null && !is_array($stats)))
        {
            return false;
        }

        $ctime = time();
        $conds = array();
        $stats === null AND $stats = $this->getRtstas();
        foreach ($stats as $state)
        {
            switch ($state)
            {
            case ORDER_REALTIME_STATUS_YRZ:
                $conds[ORDER_REALTIME_STATUS_YRZ] = sprintf(
                    '((o_status = %d OR o_status = %d) AND o_etime >= %d AND o_btime >= %d AND o_btime < %d)',
                    ORDER_STATUS_YD,
                    ORDER_STATUS_BL,
                    $ctime,
                    $ctime,
                    $hotel['h_prompt_checkin']*60 + $ctime
                );
                break;

            case ORDER_REALTIME_STATUS_YTF:
                $conds[ORDER_REALTIME_STATUS_YTF] = sprintf(
                    '(o_status = %d AND o_etime >= %d AND o_etime < %d)',
                    ORDER_STATUS_ZZ,
                    $ctime,
                    $hotel['h_prompt_checkout']*60 + $ctime
                );
                break;

            case ORDER_REALTIME_STATUS_YZWZ:
                $conds[ORDER_REALTIME_STATUS_YZWZ] = sprintf(
                    '((o_status = %d OR o_status = %d) AND NOT (o_etime < %d) AND (o_btime < %d))',
                    ORDER_STATUS_YD,
                    ORDER_STATUS_BL,
                    $ctime,
                    $ctime
                );
                break;

            case ORDER_REALTIME_STATUS_YTWT:
                $conds[ORDER_REALTIME_STATUS_YTWT] = sprintf(
                    '(o_status = %d AND o_etime < %d)',
                    ORDER_STATUS_ZZ,
                    $ctime
                );
                break;

            case ORDER_REALTIME_STATUS_GQWZ:
                $conds[ORDER_REALTIME_STATUS_GQWZ] = sprintf(
                    '((o_status = %d OR o_status = %d) AND o_etime < %d)',
                    ORDER_STATUS_YD,
                    ORDER_STATUS_BL,
                    $ctime
                );
                break;

            case ORDER_REALTIME_STATUS_FTCT:
                $conds[ORDER_REALTIME_STATUS_FTCT] = sprintf(
                    '(o_status != %d AND o_status != %d AND o_btime <= r_etime AND o_edatm > r_btime)',
                    ORDER_STATUS_YQX,
                    ORDER_STATUS_YJS
                );
                break;

            default :
                break;
            }
        }

        return $conds;
    }

    /**
     * getRealTimeStateCodes
     * 
     * @param array $order
     * @param array $hotel
     * @return array
     */
    public function getRealTimeStateCodes($order, $hotel)
    {
        $ret = array();
        if (is_array($hotel) && !empty($hotel['h_id']) && is_array($order) && !empty($order['o_hid'])
            && $hotel['h_id'] === $order['o_hid'])
        {
            $now = time();

            switch ($order['o_status'])
            {
            case ORDER_STATUS_YD:
            case ORDER_STATUS_BL:
                if ($now >= $order['o_etime'])
                {
                    $ret[] = ORDER_REALTIME_STATUS_GQWZ;
                }
                else if ($now >= $order['o_btime'])
                {
                    $ret[] = ORDER_REALTIME_STATUS_YZWZ;
                }
                else if ($now >= $order['o_btime'] - $hotel['h_prompt_checkin']*60)
                {
                    $ret[] = ORDER_REALTIME_STATUS_YRZ;
                }

                break;
            case ORDER_STATUS_ZZ:
                if ($now >= $order['o_etime'])
                {
                    $ret[] = ORDER_REALTIME_STATUS_YTWT;
                }
                else if ($now >= $order['o_etime'] - $hotel['h_prompt_checkout']*60)
                {
                    $ret[] = ORDER_REALTIME_STATUS_YTF;
                }

                break;

            default :
                break;
            }

            if ($this->chkIsConflictWithRoomStatus($order))
            {
                $ret[] = ORDER_REALTIME_STATUS_FTCT;
            }
        }

        return $ret;
    }

    /**
     * getRealTimeStateNames
     * 
     * @param array $order
     * @param array $hotel
     * @return array
     */
    public function getRealTimeStateNames($order, $hotel)
    {
        $names = array();
        $codes = $this->getRealTimeStateCodes($order, $hotel);
        if ($codes)
        {
            foreach ($codes as $code)
            {
                $names[] = array_search($code, static::$_rtstat, true);
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
     * @param array $order
     * @param array $hotel
     * @param bool  $strict
     * @return array
     */
    public function getUsableActions($order, $hotel, $strict = true)
    {
        $code = $order['o_status'];
        if (empty(static::$_handle[$code]))
        {
            return array();
        }

        $datm = strtotime(date('Y-m-d'));

        $actions = array();
        foreach (static::$_handle[$code] as $idx)
        {
            isset(static::$_action[$idx]) AND $actions[$idx] = static::$_action[$idx];
        }

        if (isset($actions[ORDER_ACTION_BLTF]) && $order['o_edatm'] > $datm)
        {
            unset($actions[ORDER_ACTION_BLTF]);
        }

        if (isset($actions[ORDER_ACTION_BLRZ]) && $order['o_bdatm'] > $datm)
        {
            unset($actions[ORDER_ACTION_BLRZ]);
        }

        if (isset($actions[ORDER_ACTION_BLHF]) && $order['o_edatm'] < $datm)
        {
            unset($actions[ORDER_ACTION_BLHF]);
        }

        if (isset($actions[ORDER_ACTION_BLHF]) && !((int)$order['o_attr'] & (int)ORDER_ATTR_YXHF))
        {
            unset($actions[ORDER_ACTION_BLHF]);
        }

        if ($strict && (isset($actions[ORDER_ACTION_BLDD]) || isset($actions[ORDER_ACTION_BLRZ]))
            && (
                $this->chkIsConflictWithRoomStatus($order)
                || ($order['o_status'] === ORDER_STATUS_YD && $this->chkIsConflictWithOtherOrders($order))
            )
        )
        {
            unset($actions[ORDER_ACTION_BLDD]);
            unset($actions[ORDER_ACTION_BLRZ]);
        }

        return $actions;
    }

    /**
     * chkIsConflictWithOtherOrders
     * 
     * @param array $order
     * @return bool
     */
    public function chkIsConflictWithOtherOrders($order)
    {
        if ($order['o_status'] === ORDER_STATUS_YQX)
        {
            return false;
        }

        $cns = array(
            'o_rid = ' . $this->quote($order['o_rid']),
            'o_id <> ' . $this->quote($order['o_id']),
            'o_status <> ' . ORDER_STATUS_YQX,
            'o_status <> ' . ORDER_STATUS_YD,
            'o_btime < ' . $this->quote($order['o_etime']),
            'o_etime > ' . $this->quote($order['o_btime'])
        );
        $ids = $this->fetchIds($cns, null, 1);

        return !is_array($ids) || !empty($ids);
    }

    /**
     * chkIsConflictWithRoomStatus
     * 
     * @param array $order
     * @return bool
     */
    public function chkIsConflictWithRoomStatus($order)
    {
        if ($order['o_status'] === ORDER_STATUS_YQX || $order['o_status'] === ORDER_STATUS_YJS)
        {
            return false;
        }

        if (!isset($order['r_btime']) || !isset($order['r_etime']))
        {
            $room = Model::factory('room', 'ftt')->getRoom($order['o_rid']);
            $order['r_btime'] = $room['r_btime'];
            $order['r_etime'] = $room['r_etime'];
        }

        if ($order['o_btime'] <= $order['r_etime']
            && strtotime(date('Y-m-d', $order['o_etime'])) > $order['r_btime'])
        {
            return true;
        }

        return false;
    }

    /**
     * addOrder
     * 
     * @param array $map
     * @return string
     */
    public function addOrder($map)
    {
        if (empty($map) || !is_array($map))
        {
            return false;
        }

        if (!$this->verify($map)
            || empty($map['o_hid'])
            || empty($map['o_bid'])
            || empty($map['o_sid'])
            || empty($map['o_rid'])
            || empty($map['o_tid'])
            || !isset($map['o_btime'])
            || !isset($map['o_etime'])
            || !isset($map['o_bdatm'])
            || !isset($map['o_edatm'])
            || !isset($map['o_status'])
            || empty($map['o_prices'])
            || empty($map['o_brices'])
        )
        {
            return false;
        }

        if (!isset($map['o_price']))
        {
            $map['o_price'] = array_sum(json_decode($map['o_prices'], true));
        }

        if (!isset($map['o_brice']))
        {
            $map['o_brice'] = array_sum(json_decode($map['o_brices'], true));
        }

        if (!$this->verify(array('o_price' => $map['o_price'], 'o_brice' => $map['o_brice'])))
        {
            return false;
        }

        if (isset($map['o_btime']) && !isset($map['o_bdatm']))
        {
            $map['o_bdatm'] = strtotime(date('Y-m-d', $map['o_btime']));
        }

        if (isset($map['o_etime']) && !isset($map['o_edatm']))
        {
            $map['o_edatm'] = strtotime(date('Y-m-d', $map['o_etime']));
        }

        if (!isset($map['o_ctime']))
        {
            $map['o_ctime'] = time();
        }

        if (!isset($map['o_mtime']))
        {
            $map['o_mtime'] = $map['o_ctime'];
        }

        try
        {
            $this->dbase()->insert($this->tname('order'), $map);
            return $this->dbase()->lastInsertId();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getOrder
     * 
     * @param int $id 
     * @return array
     */
    public function getOrder($id)
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
            $sql = $this->dbase()->select()->from($this->tname('order'))->where('o_id = ?')->limit(1);
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
     * modOrder
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modOrder($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map))
        {
            return false;
        }

        if (!isset($map['o_mtime']))
        {
            $map['o_mtime'] = time();
        }

        if (isset($map['o_btime']) && !isset($map['o_bdatm']))
        {
            $map['o_bdatm'] = strtotime(date('Y-m-d', $map['o_btime']));
        }

        if (isset($map['o_etime']) && !isset($map['o_edatm']))
        {
            $map['o_edatm'] = strtotime(date('Y-m-d', $map['o_etime']));
        }

        try
        {
            $ret = $this->dbase()->update($this->tname('order'), $map, 'o_id = ' . $this->quote($id));
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
     * getOrderAryByIds
     * 
     * @param array $ids 
     * @return array
     */
    public function getOrderAryByIds($ids)
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
            $sql = $this->dbase()->select()->from($this->tname('order'))
                ->where('o_id IN (' . implode(',', array_map(array($this, 'quote'), $ids)) . ')');
            foreach ($this->dbase()->fetchAll($sql) as $val)
            {
                $ret[] = $val;
                $this->cache()->save($val, $this->hash($val['o_id']));
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
     * getOrderIdsByHid
     * 
     * @param int   $hid
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function getOrderIdsByHid($hid, $order = null, $limit = null)
    {
        return $this->fetchIds(array('o_hid = ' . $this->quote($hid)), $order, $limit);
    }

    /**
     * getOrderAryByHid
     * 
     * @param int   $hid
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function getOrderAryByHid($hid, $order = null, $limit = null)
    {
        return $this->fetchAry(array('o_hid = ' . $this->quote($hid)), $order, $limit);
    }

    /**
     * getOrderIdsByRid
     * 
     * @param int   $rid
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function getOrderIdsByRid($rid, $order = null, $limit = null)
    {
        return $this->fetchIds(array('o_rid = ' . $this->quote($rid)), $order, $limit);
    }

    /**
     * getOrderAryByRid
     * 
     * @param int   $rid
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function getOrderAryByRid($rid, $order = null, $limit = null)
    {
        return $this->fetchAry(array('o_rid = ' . $this->quote($rid)), $order, $limit);
    }

    /**
     * getOrderIdsByBid
     * 
     * @param int   $bid
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function getOrderIdsByBid($bid, $order = null, $limit = null)
    {
        return $this->fetchIds(array('o_bid = ' . $this->quote($bid)), $order, $limit);
    }

    /**
     * getOrderAryByBid
     * 
     * @param int   $bid
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function getOrderAryByBid($bid, $order = null, $limit = null)
    {
        return $this->fetchAry(array('o_bid = ' . $this->quote($bid)), $order, $limit);
    }

    /**
     * getOrderAryWithRoomCond
     * 
     * @param array $where
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function getOrderAryWithRoomCond($where, $order = null, $limit = null)
    {
        if (empty($where) || !is_array($where))
        {
            return false;
        }

        $sql = $this->dbase()->select()->from($this->tname('order'))
            ->joinLeft($this->tname('room'), 'o_rid=r_id', null);

        foreach ($where as $val)
        {
            $sql->where($val);
        }

        $order ANd $sql->order($order);
        if (!empty($limit))
        {
            if (is_numeric($limit))
            {
                $sql->limit($limit);
            }
            else if (is_array($limit))
            {
                isset($limit[1]) ? $sql->limit($limit[0], $limit[1]) : $sql->limit($limit[0]);
            }
        }

        return $this->dbase()->fetchAll($sql);
    }

    /**
     * getIndexOrderAry
     * 
     * @param array $where
     * @param array $guest
     * @param mixed $order
     * @param mixed $limit
     * @return array
     */
    public function getIndexOrderAry($where, $guest = null, $order = null, $limit = null)
    {
        if (empty($where) || !is_array($where) || ($guest !== null AND !is_array($guest)))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('order'), $this->tname('order') . '.*');
            $sql->joinLeft($this->tname('room'), 'o_rid=r_id', array('r_btime', 'r_etime'));

            if (!empty($guest))
            {
                if (isset($guest['type']) && isset($guest['name']))
                {
                    $sql->where('o_g' . ($guest['type'] === HOTEL_GUEST_TYPE_BOOK ? 'bker' : 'lver') . '_name = ' . $this->quote($guest['name']));
                }
                else if (isset($guest['name']))
                {
                    $sql->where('o_gbker_name = ' . $this->quote($guest['name']) . ' OR' . ' o_glver_name = ' . $this->quote($guest['name']));
                }
                else if (isset($guest['mail']))
                {
                    $sql->where('o_gbker_email = ' . $this->quote($guest['mail']) . ' OR' . ' o_glver_email = ' . $this->quote($guest['mail']));
                }
                else if (isset($guest['call']))
                {
                    $sql->where('o_gbker_phone = ' . $this->quote($guest['call']) . ' OR' . ' o_glver_phone = ' . $this->quote($guest['call']));
                }
            }

            foreach ($where as $val)
            {
                $sql->where($val);
            }

            $order ANd $sql->order($order);
            if (!empty($limit))
            {
                if (is_numeric($limit))
                {
                    $sql->limit($limit);
                }
                else if (is_array($limit))
                {
                    isset($limit[1]) ? $sql->limit($limit[0], $limit[1]) : $sql->limit($limit[0]);
                }
            }

            return $this->dbase()->fetchAll($sql);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getRealTimeStateOrderAryByHid
     * 
     * @param int $hid
     * @param int $btime
     * @param int $etime
     * @return array
     */
    public function getRealTimeStateOrderAryByHid($hid, $btime, $etime)
    {
        if (!Zyon_Util::isUnsignedInt($hid)
            || !is_numeric($btime) || !is_numeric($etime)
        )
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('order'), $this->tname('order') . '.*');
            $sql->joinLeft($this->tname('room'), 'o_rid=r_id', array('r_btime', 'r_etime'));
            $sql->where('o_hid = :hid')
                ->where('o_status <> ' . ORDER_STATUS_YJS)
                ->where('o_status <> ' . ORDER_STATUS_YQX)
                ->where('o_btime < :etime')
                ->where('o_etime > :btime');
            $sql->order('o_btime ASC');

            return $this->dbase()->fetchAll($sql, array(
                'hid'   => $hid,
                'btime' => $btime,
                'etime' => $etime,
            ));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * chkCanCreateOrder
     * 
     * @param array $hotel
     * @param array $room
     * @param mixed $btime
     * @param mixed $etime
     * @return bool
     */
    public function chkCanCreateOrder($hotel, $room, $btime, $etime)
    {
        if (!is_array($hotel)
            || !isset($hotel['h_order_enddays'])
            || !isset($hotel['h_order_minlens']) || !isset($hotel['h_order_maxlens'])
            || !is_array($room) || empty($room['r_id']) || !isset($room['r_btime']) || !isset($room['r_etime'])
            || !Zyon_Util::isUnsignedInt($btime) || !Zyon_Util::isUnsignedInt($etime)
            || !($cdatm = strtotime(date('Y-m-d')))
            || !($edatm = strtotime(date('Y-m-d', $etime)))
            || !($bdatm = strtotime(date('Y-m-d', $btime)))
            || $bdatm >= $edatm
            || $edatm < $cdatm
            || $btime < $room['r_otime']
            || ($btime <= $room['r_etime'] && $edatm > $room['r_btime'])
            || ($edatm - $cdatm)/86400 > $hotel['h_order_enddays']
            || ($edatm - $bdatm)/86400 < $hotel['h_order_minlens']
            || ($edatm - $bdatm)/86400 > $hotel['h_order_maxlens']
        )
        {
            return false;
        }

        $cns = array(
            'o_rid = ' . $room['r_id'],
            'o_status <> ' . ORDER_STATUS_YQX,
            'o_status <> ' . ORDER_STATUS_YD,
            'o_btime < ' . $etime,
            'o_etime > ' . $btime,
        );
        $ids = $this->fetchIds($cns, null, 1);

        return is_array($ids) && empty($ids);
    }

    /**
     * chkCanUpdateOrder
     * 
     * @param array $hotel
     * @param array $room
     * @param array $order
     * @param mixed $btime
     * @param mixed $etime
     * @return bool
     */
    public function chkCanUpdateOrder($hotel, $room, $order, $btime, $etime)
    {
        if (!is_array($hotel)
            || !isset($hotel['h_order_enddays'])
            || !isset($hotel['h_order_minlens']) || !isset($hotel['h_order_maxlens'])
            || !is_array($room) || empty($room['r_id'])
            || !isset($room['r_btime']) || !isset($room['r_etime'])
            || !is_array($order) || empty($order['o_id'])
            || !isset($order['o_btime']) || !isset($order['o_btime']) || !isset($order['o_ctime'])
            || !Zyon_Util::isUnsignedInt($btime) || !Zyon_Util::isUnsignedInt($etime)
            || !($cdatm = strtotime(date('Y-m-d', $order['o_ctime'])))
            || !($edatm = strtotime(date('Y-m-d', $etime)))
            || !($bdatm = strtotime(date('Y-m-d', $btime)))
            || $bdatm >= $edatm
            || $edatm < $cdatm
            || $btime < $room['r_otime']
            || ($edatm - $cdatm)/86400 > $hotel['h_order_enddays']
            || ($edatm - $bdatm)/86400 < $hotel['h_order_minlens']
            || ($edatm - $bdatm)/86400 > $hotel['h_order_maxlens']
        )
        {
            return false;
        }

        $cns = array(
            'o_rid = ' . $room['r_id'],
            'o_id <> ' . $order['o_id'],
            'o_status <> ' . ORDER_STATUS_YQX,
            'o_status <> ' . ORDER_STATUS_YD,
            'o_btime < ' . $etime,
            'o_etime > ' . $btime
        );
        $ids = $this->fetchIds($cns, null, 1);

        return is_array($ids) && empty($ids);
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
            if (array_key_exists($name, static::$_oattrs))
            {
                $code = $code | (int)static::$_oattrs[$name];
            }
        }

        return $code;
    }

    /**
     * calNumAryByRidAryAndSta
     * 
     * @param array $ids
     * @param mixed $sta
     * @param int   $dtm
     * @return array
     */
    public function calNumAryByRidAryAndSta($ids, $sta, $dtm = 0)
    {
        if (!$sta || !is_array($ids) || !Zyon_Util::isUnsignedInt($dtm))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('order'), array('o_rid', 'count(o_id)'))
                ->group('o_rid')
                ->where(sprintf('o_rid IN (%s)', implode(',', $ids)))
                ->where('o_status = ' . $this->quote($sta));

            if ($dtm)
            {
                $sql->where('o_ctime >= ' . $dtm);
            }

            return $this->dbase()->fetchPairs($sql);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }
}
// End of file : Order.php
