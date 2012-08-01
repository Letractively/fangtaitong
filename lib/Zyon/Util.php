<?php
/**
 * @version    $Id$
 */
class Zyon_Util
{
	/**
	 * @see http://www.regular-expressions.info/email.html
	 */
    const REGEX_EMAIL  = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
    const REGEX_DOMAIN = '/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
    const REGEX_URL    = '~^
        (?:%s)://                                 # protocol
        (?:
            ([\pL\pN\pS-]+\.)+[\pL]+                   # a domain name
            |                                     #  or
            \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}      # a IP address
            |                                     #  or
            \[
            (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
            \]  # a IPv6 address
        )
        (:[0-9]+)?                              # a port (optional)
        (/?|/\S+)                               # a /, nothing or a / with something
        $~ixu';

    /**
     * guid
     *
     * @return string(int)
     */
    public static function guid()
    {
        static $list = array();

        $time = explode(' ', microtime());
        // 1338438896 => 2012-05-31 12:34:56
        $guid = ($time[1] - 1338438896) . sprintf('%06u', substr($time[0], 2, 6)) . substr(sprintf('%010u', mt_rand()), 0, 4);

        return isset($list[$guid]) ? guid() : ($list[$guid] = $guid);
    }

    /**
     * isDomain
     * 
     * @param mixed $value 
     * @return bool
     */
    public static function isDomain($value)
    {
        return is_string($value)
            && strlen($value) <= 252
            && preg_match(static::REGEX_DOMAIN, $value);
    }

    /**
     * isEmail
     * 
     * @param mixed $value
     * @return bool
     */
    public static function isEmail($value)
    {
        return is_string($value)
            && strlen($value) <= 254
            && preg_match(static::REGEX_EMAIL, $value);
    }

    /**
     * isUrl
     * 
     * @param mixed $value
     * @param string $scheme
     * @return bool
     */
    public static function isUrl($value, $scheme = 'https?')
    {
        return is_string($value)
            && strlen($value) <= 254
            && preg_match(sprintf(static::REGEX_URL, $scheme), $value);
    }

    /**
     * isIP
     * 
     * @param mixed $value 
     * @return bool
     */
    public static function isIP($value)
    {
        return static::isIPv4($value) || static::isIPv6($value);
    }

    /**
     * isIpv4
     * 
     * @param mixed $value 
     * @return bool
     */
    public static function isIPv4($value)
    {
        $ip2long = ip2long($value);
        if($ip2long === false)
        {
            return false;
        }

        return $value == long2ip($ip2long);
    }

    /**
     * isIpv6
     * 
     * @param mixed $value 
     * @return bool
     */
    public static function isIPv6($value)
    {
        if (strlen($value) < 3)
        {
            return $value == '::';
        }

        if (strpos($value, '.'))
        {
            $lastcolon = strrpos($value, ':');
            if (!($lastcolon && $this->isIPv4(substr($value, $lastcolon + 1))))
            {
                return false;
            }

            $value = substr($value, 0, $lastcolon) . ':0:0';
        }

        if (strpos($value, '::') === false)
        {
            return preg_match('/\A(?:[a-f0-9]{1,4}:){7}[a-f0-9]{1,4}\z/i', $value);
        }

        $colonCount = substr_count($value, ':');
        if ($colonCount < 8)
        {
            return preg_match('/\A(?::|(?:[a-f0-9]{1,4}:)+):(?:(?:[a-f0-9]{1,4}:)*[a-f0-9]{1,4})?\z/i', $value);
        }

        // special case with ending or starting double colon
        if ($colonCount == 8)
        {
            return preg_match('/\A(?:::)?(?:[a-f0-9]{1,4}:){6}[a-f0-9]{1,4}(?:::)?\z/i', $value);
        }

        return false;
    }

    /**
     * isBin
     * 
     * @param mixed $value
     * @return bool
     */
    public static function isBin($value)
    {
        return (bool)preg_match('/^[01]+$/', $value);
    }

    /**
     * isDate
     * （yyyy/mm/dd、yyyy-mm-dd）
     *
     * @param mixed $value
     * @return bool
     */
    public static function isDate($value)
    {
        if (strpos($value, '-') !== false)
        {
            $p = '-';
        }
        elseif (strpos($value, '/') !== false)
        {
            $p = '/';
        }
        else
        {
            return false;
        }

        if (preg_match('#^\d{4}' . $p . '\d{1,2}' . $p . '\d{1,2}$#', $value))
        {
            $arr = explode($p, $value);
            if (count($arr) < 3) return false;

            list($year, $month, $day) = $arr;
            return checkdate($month, $day, $year);
        }
        else
        {
            return false;
        }
    }

    /**
     * isTime
     * （hh:mm:ss）
     *
     * @param mixed $value
     * @return bool
     */
    public static function isTime($value)
    {
        $parts = explode(':', $value);
        $count = count($parts);
        if ($count != 2 && $count != 3)
        {
            return false;
        }
        if ($count == 2)
        {
            $parts[2] = '00';
        }
        $test = @strtotime($value = $parts[0] . ':' . $parts[1] . ':' . $parts[2]);
        if ($test === - 1 || $test === false || date('H:i:s', $test) != $value)
        {
            return false;
        }

        return true;
    }

    /**
     * isDatetime
     *
     * @param mixed $value
     * @return bool
     */
    public static function isDatetime($value)
    {
        return @strtotime($value) !== false;
    }

    /**
     * isMoneyFloat
     * 
     * @param mixed $value
     * @return bool
     */
    public static function isMoneyFloat($value)
    {
        return @preg_match('/^-?[1-9][0-9]*$|^-?[1-9][0-9]*\.[0-9]{1,2}$|^0$|^-?0\.[0-9]{1,2}$/', $value);
    }

    /**
     * isInt
     * 
     * @param mixed $value
     * @return bool
     */
    public static function isInt($value)
    {
        return (is_int($value) || @preg_match('/^0$|^-?[1-9][0-9]*$/', $value)) && $value <= 2147483647 && $value >= -2147483648;
    }

    /**
     * isUnsignedInt
     * 
     * @param mixed $value
     * @return bool
     */
    public static function isUnsignedInt($value)
    {
        return @preg_match('/^0$|^[1-9][0-9]*$/', $value) && $value <= 4294967295;
    }

    /**
     * timeToSecs
     * 
     * @param string $time
     * @return int
     */
    public static function timeToSecs($time)
    {
        return strtotime('1980-01-01 ' . $time) - strtotime('1980-01-01');
    }
}
// End of file : Util.php
