<?php
/**
 * @version    $Id$
 */
class Zyon_Cache_Backend_MemcachedExt extends Zend_Cache_Backend_Memcached
{
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param  string|array       $ids                    Cache ids
     * @param  boolean            $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|array|false cached datas
     */
    public function load($ids, $doNotTestCacheValidity = false)
    {
        $tmp = $this->_memcache->get($ids);

        if (is_array($ids))
        {
            foreach ($tmp as $key => $val)
            {
                if (is_array($val) && isset($val[0]))
                {
                    $tmp[$key] = $val[0];
                }
                else
                {
                    unset($tmp[$key]);
                }
            }

            if (empty($tmp))
            {
                return false;
            }

            return $tmp;
        }

        if (is_array($tmp) && isset($tmp[0]))
        {
            return $tmp[0];
        }
        return false;
    }
}
// End of file : MemcachedExt.php
