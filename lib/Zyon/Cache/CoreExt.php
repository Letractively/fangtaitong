<?php
/**
 * @version    $Id$
 */
class Zyon_Cache_CoreExt extends Zend_Cache_Core
{
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param  string|array  $ids                    Cache ids
     * @param  boolean       $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @param  boolean       $doNotUnserialize       Do not serialize (even if automatic_serialization is true) => for internal use
     * @return mixed|false Cached datas
     */
    public function load($ids, $doNotTestCacheValidity = false, $doNotUnserialize = false)
    {
        if (!$this->_options['caching']) {
            return false;
        }

        if (is_array($ids))
        {
            if (empty($ids))
            {
                return false;
            }

            foreach ($ids as $key => $val)
            {
                $ids[$key] = $this->_id($val);
                $this->_lastId = $ids[$key];
                self::_validateIdOrTag($ids[$key]);
                $this->_log("Zend_Cache_Core: load item '{$ids[$key]}'", 7);
            }
        }
        else
        {
            $ids = $this->_id($ids);
            $this->_lastId = $ids;
            self::_validateIdOrTag($ids);
            $this->_log("Zend_Cache_Core: load item '{$ids}'", 7);
        }

        $data = $this->_backend->load($ids, $doNotTestCacheValidity);
        if ($data===false)
        {
            // no cache available
            return false;
        }
        if ((!$doNotUnserialize) && $this->_options['automatic_serialization'])
        {
            // we need to unserialize before sending the result
            if (is_array($ids))
            {
                foreach ($data as &$val)
                {
                    $val = unserialize($val);
                }
            }
            else
            {
                return unserialize($data);
            }
        }
        return $data;
    }
}
// End of file : CoreExt.php
