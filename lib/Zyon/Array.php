<?php
/**
 * @version    $Id$
 */
class Zyon_Array
{
    /**
     * field
     * 
     * @param array  $ary 
     * @param string $key 
     * @return array
     */
    public static function field($ary, $key)
    {
        if (empty($ary))
        {
            return array();
        }

        $ret = array();
        foreach ($ary as $idx => $val)
        {
            isset($val[$key]) AND $ret[$idx] = $val[$key];
        }

        return $ret;
    }

    /**
     * keyto
     * 
     * @param array  $ary 
     * @param string $key 
     * @return array
     */
    public static function keyto($ary, $key)
    {
        if (empty($ary))
        {
            return array();
        }

        $ret = array();
        foreach ($ary as $val)
        {
            isset($val[$key]) AND $ret[$val[$key]] = $val;
        }

        return $ret;
    }

    /**
     * group
     * 
     * @param array  $ary 
     * @param string $key 
     * @return array
     */
    public static function group($ary, $key)
    {
        if (empty($ary))
        {
            return array();
        }

        $ret = array();
        foreach ($ary as $idx => $val)
        {
            isset($ret[$val[$key]]) OR $ret[$val[$key]] = array();
            $ret[$val[$key]][$idx] = $val;
        }

        return $ret;
    }
}
// End of file : Zyon_Model.php
