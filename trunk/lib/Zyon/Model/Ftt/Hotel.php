<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Hotel extends Zyon_Model_Ftt
{
    /**
     * isIname
     * 
     * @param string $iname 
     * @return bool
     */
    public function isIname($iname)
    {
        return is_string($iname) && isset($iname[2]) && !isset($iname[14]) && (bool)preg_match('/^[a-z_0-9]+$/i', $iname);
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

        if (isset($record['h_attr']) && !($record['h_attr'] instanceof Zend_Db_Expr))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_attr']) || strlen($record['h_attr']) > 10)
            {
                return false;
            }
        }

        if (isset($record['h_name']))
        {
            if (!is_string($record['h_name'])
                || trim($record['h_name']) == ''
                || mb_strlen($record['h_name']) > 15
            )
            {
                return false;
            }
        }

        if (isset($record['h_note']))
        {
            if (!is_string($record['h_note'])
                || mb_strlen($record['h_note']) > 500
            )
            {
                return false;
            }
        }

        if (isset($record['h_title']))
        {
            if (!is_string($record['h_title'])
                || mb_strlen($record['h_title']) > 50
            )
            {
                return false;
            }
        }

        if (isset($record['h_iname']))
        {
            if (!$this->isIname($record['h_iname']))
            {
                return false;
            }
        }

        if (isset($record['h_email']))
        {
            if (!Zyon_Util::isEmail($record['h_email'])
                || mb_strlen($record['h_email']) > 50
            )
            {
                return false;
            }
        }

        if (isset($record['h_phone']))
        {
            if (!is_string($record['h_phone'])
                || !isset($record['h_phone'][5])
                || mb_strlen($record['h_phone']) > 20
            )
            {
                return false;
            }
        }

        if (isset($record['h_domain']))
        {
            if (!is_string($record['h_domain'])
                || mb_strlen($record['h_domain']) > 50
            )
            {
                return false;
            }
        }

        if (isset($record['h_website']))
        {
            if (!is_string($record['h_website'])
                || mb_strlen($record['h_website']) > 50
            )
            {
                return false;
            }
        }

        if (isset($record['h_address']))
        {
            if (!is_string($record['h_address'])
                || mb_strlen($record['h_address']) > 250
            )
            {
                return false;
            }
        }

        if (isset($record['h_country']))
        {
            if (!is_string($record['h_country'])
                || mb_strlen($record['h_country']) > 50
            )
            {
                return false;
            }
        }

        if (isset($record['h_province']))
        {
            if (!is_string($record['h_province'])
                || mb_strlen($record['h_province']) > 50
            )
            {
                return false;
            }
        }

        if (isset($record['h_city']))
        {
            if (!is_string($record['h_city'])
                || mb_strlen($record['h_city']) > 50
            )
            {
                return false;
            }
        }

        if (isset($record['h_obill_default_settlem']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_obill_default_settlem'])
                || strlen($record['h_obill_default_settlem']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['h_order_default_typedef']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_order_default_typedef'])
                || strlen($record['h_order_default_typedef']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['h_order_default_channel']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_order_default_channel'])
                || strlen($record['h_order_default_channel']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['h_order_default_payment']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_order_default_payment'])
                || strlen($record['h_order_default_payment']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['h_order_default_stacode']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_order_default_stacode'])
                || strlen($record['h_order_default_stacode']) > 3
            )
            {
                return false;
            }
        }

        if (isset($record['h_order_enddays']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_order_enddays'])
                || strlen($record['h_order_enddays']) > 3
                || $record['h_order_enddays'] > 450
            )
            {
                return false;
            }
        }

        if (isset($record['h_order_minlens']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_order_minlens'])
                || $record['h_order_minlens'] < 1
                || strlen($record['h_order_minlens']) > 4
            )
            {
                return false;
            }
        }

        if (isset($record['h_order_maxlens']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_order_maxlens'])
                || $record['h_order_maxlens'] > 31
            )
            {
                return false;
            }
        }

        if (isset($record['h_obill_keptime']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_obill_keptime'])
                || strlen($record['h_obill_keptime']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['h_order_enabled']) && !($record['h_order_enabled'] instanceof Zend_Db_Expr))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_order_enabled'])
                || $record['h_order_enabled'] > 3
            )
            {
                return false;
            }
        }

        if (isset($record['h_rosta_visible']) && !($record['h_rosta_visible'] instanceof Zend_Db_Expr))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_rosta_visible'])
                || $record['h_rosta_visible'] > 3
            )
            {
                return false;
            }
        }

        if (isset($record['h_checkin_time']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_checkin_time'])
                || $record['h_checkin_time'] > 86399)
            {
                return false;
            }
        }

        if (isset($record['h_checkout_time']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_checkout_time'])
                || $record['h_checkout_time'] > 86399)
            {
                return false;
            }
        }

        if (isset($record['h_prompt_checkin']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_prompt_checkin'])
                || strlen($record['h_prompt_checkin']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['h_prompt_checkout']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_prompt_checkout'])
                || strlen($record['h_prompt_checkout']) > 10
            )
            {
                return false;
            }
        }

        if (isset($record['h_status']))
        {
            if (!Zyon_Util::isUnsignedInt($record['h_status'])
                || strlen($record['h_status']) > 3
            )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * getNewHotel
     * 
     * @param string $name
     * @param string $mail
     * @param string $call
     * @return array
     */
    public function getNewHotel($name, $mail, $call)
    {
        return array(
            'h_name'  => $name,
            'h_email' => $mail,
            'h_phone' => $call,
        );
    }

    /**
     * addHotel
     * 
     * @param array $map
     * @return string
     */
    public function addHotel($map)
    {
        if (!$this->verify($map))
        {
            return false;
        }

        if (!isset($map['h_name']) || !isset($map['h_phone']) || !isset($map['h_email'])
            || (isset($map['h_iname']) && $this->getHotelByIname($map['h_iname']))
        )
        {
            return false;
        }

        if (!isset($map['h_ctime']))
        {
            $map['h_ctime'] = time();
        }

        if (!isset($map['h_mtime']))
        {
            $map['h_mtime'] = $map['h_ctime'];
        }

        try
        {
            $this->dbase()->insert($this->tname('hotel'), $map);
            return $this->dbase()->lastInsertId();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * delHotel
     * 
     * @param int $id
     * @return int
     */
    public function delHotel($id)
    {
        if (empty($id) || !is_numeric($id))
        {
            return false;
        }

        try
        {
            $ret = $this->dbase()->delete($this->tname('hotel'), 'h_id = ' . $this->quote($id));
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
     * getHotel
     * 
     * @param int $id 
     * @return array
     */
    public function getHotel($id)
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
            $sql = $this->dbase()->select()->from($this->tname('hotel'))->where('h_id = ?')->limit(1);
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
     * modHotel
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modHotel($id, $map)
    {
        if (empty($id) || !is_numeric($id) || !$this->verify($map))
        {
            return false;
        }

        if (isset($map['h_iname']))
        {
            return false;
        }

        if (!isset($map['h_mtime']))
        {
            $map['h_mtime'] = time();
        }

        $where = 'h_id = ' . $this->quote($id);

        try
        {
            $ret = $this->dbase()->update($this->tname('hotel'), $map, $where);
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
     * getHotelByDomain
     * 
     * @param string $domain
     * @return array
     */
    public function getHotelByDomain($domain)
    {
        if (empty($domain))
        {
            return false;
        }

        try
        {
            if ($id = $this->cache()->load($key = $this->hash($domain)))
            {
                if (($ret = $this->getHotel($id)) && $ret['h_domain'] === $domain)
                {
                    return $ret;
                }

                $this->cache()->remove($key);
            }

            $sql = $this->dbase()->select()->from($this->tname('hotel'))
                ->where('h_domain = ?')
                ->limit(1);
            if ($ret = $this->dbase()->fetchRow($sql, $domain))
            {
                $this->cache()->save($ret['h_id'], $key);
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
     * getHotelByIname
     * 
     * @param string $iname
     * @return array
     */
    public function getHotelByIname($iname)
    {
        if (empty($iname))
        {
            return false;
        }

        try
        {
            if ($id = $this->cache()->load($key = $this->hash($iname)))
            {
                if (($ret = $this->getHotel($id)) && $ret['h_iname'] === $iname)
                {
                    return $ret;
                }

                $this->cache()->remove($key);
            }

            $sql = $this->dbase()->select()->from($this->tname('hotel'))
                ->where('h_iname = ?')
                ->limit(1);
            if ($ret = $this->dbase()->fetchRow($sql, $iname))
            {
                $this->cache()->save($ret['h_id'], $key);
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
     * calLiveNumByDay
     * 
     * @param date $date
     * @return array {$hid: $num, ...}
     */
    public function calLiveNumByDay($date, $hids = null)
    {
        if (!Zyon_Util::isDate($date))
        {
            return false;
        }

        $time = strtotime($date)+86399;

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('order'), 'o_hid, count(o_id)')
                ->where('o_btime <= :dtm')
                ->where('o_etime > :dtm')
                ->where(sprintf('o_status IN (%s, %s)',
                    $this->quote(ORDER_STATUS_ZZ),
                    $this->quote(ORDER_STATUS_YJS)
                ))
                ->group('o_hid');
            $hids AND $sql->where(sprintf("o_hid IN (%s)", is_string($hids) ? $hids : implode(',', $hids)));

            return $this->dbase()->fetchPairs($sql, array('dtm' => $time));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * calRoomNumByDay
     * 
     * @param date $date
     * @return array {$hid: $num, ...}
     */
    public function calRoomNumByDay($date, $hids = null)
    {
        if (!Zyon_Util::isDate($date))
        {
            return false;
        }

        $time = strtotime($date)+86399;

        try
        {
            $sql = $this->dbase()->select()
                ->from($this->tname('hotel'), 'h_id')
                ->joinLeft($this->tname('room'), 'h_id=r_hid AND r_otime <= :dtm', 'count(r_id)')
                ->group('h_id');
            $hids AND $sql->where(sprintf("h_id IN (%s)", is_string($hids) ? $hids : implode(',', $hids)));

            return $this->dbase()->fetchPairs($sql, array('dtm' => $time));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * buildReferByHotel
     * 
     * @param mixed $hotel
     * @return string
     */
    public function buildReferByHotel($hotel)
    {
        if (is_array($hotel) && isset($hotel['h_id']) && isset($hotel['h_ctime']))
        {
            $map = array(
                'A', 'B', 'C', 'D', 'E', 'F', 'G',
                'H', 'I', 'J', 'K', 'L', 'M', 'N',
                'O', 'P', 'Q', 'R', 'S', 'T',
                'U', 'V', 'W', 'X', 'Y', 'Z',
                'a', 'b', 'c', 'd', 'e', 'f', 'g',
                'h', 'i', 'j', 'k', 'l', 'm', 'n',
                'o', 'p', 'q', 'r', 's', 't',
                'u', 'v', 'w', 'x', 'y', 'z',
            );

            $num = $hotel['h_id'] + 2703; // 2703 = zz(52)
            $bas = count($map);
            $sid = '';

            do {
                $sid = $map[$num % $bas] . $sid;
                $num = (int)($num / $bas);
                if ($num < $bas)
                {
                    $sid = $map[$num % $bas] . $sid;
                }
            }
            while ($num >= $bas);

            return 'R'
                . ucfirst(substr(md5($hotel['h_ctime']), 0, 2))
                . $sid;
        }

        return '';
    }

    /**
     * parseReferToHotel
     * 
     * @param string $refer
     * @return mixed
     */
    public function parseReferToHotel($refer)
    {
        if (is_string($refer) && isset($refer[5]) && !isset($refer[9]) && $refer[0] == 'R')
        {
            $map = array_flip(array(
                'A', 'B', 'C', 'D', 'E', 'F', 'G',
                'H', 'I', 'J', 'K', 'L', 'M', 'N',
                'O', 'P', 'Q', 'R', 'S', 'T',
                'U', 'V', 'W', 'X', 'Y', 'Z',
                'a', 'b', 'c', 'd', 'e', 'f', 'g',
                'h', 'i', 'j', 'k', 'l', 'm', 'n',
                'o', 'p', 'q', 'r', 's', 't',
                'u', 'v', 'w', 'x', 'y', 'z',
            ));

            $num = substr($refer, 3);
            $bas = count($map);
            $hid = 0;

            for ($pos = 0, $len = strlen($num); $pos < $len; $pos++)
            {
                if (!isset($map[$num[$pos]]))
                {
                    return false;
                }

                $hid += pow($bas, $len - $pos - 1) * $map[$num[$pos]];
            }

            if ($hotel = $this->getHotel($hid = $hid - 2703)) // 2703 = zz(52)
            {
                if (ucfirst(substr(md5($hotel['h_ctime']), 0, 2)) === substr($refer, 1, 2))
                {
                    return $hotel;
                }
            }
        }

        return false;
    }
}
// End of file : Hotel.php
