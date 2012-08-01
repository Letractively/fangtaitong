<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initConst()
    {
        if ($this->hasOption('consts') && is_array($consts = $this->getOption('consts')))
        {
            foreach ($consts as $key => $val)
            {
                defined($key) OR define($key, $val);
            }
        }
    }

    protected function _initRegistry()
    {
        if ($this->hasOption('registry') && is_array($registry = $this->getOption('registry')))
        {
            $instance = Zend_Registry::getInstance();
            foreach ($registry as $key => $val)
            {
                $instance->$key = $val;
            }
        }
    }

    /**
     * getDbAdapter
     * 
     * @param string $name 
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter($name = null)
    {
        $name = (string)$name;
        if ($name == '' && $this->hasPluginResource('db'))
        {
            return $this->getPluginResource('db')->getDbAdapter();
        }
        else if ($this->hasPluginResource('multidb'))
        {
            return $this->getPluginResource('multidb')
                ->getDb($name == '' ? null : $name);
        }
    }

    /**
     * getCache
     * 
     * @param string $name 
     * @return Zend_Cache_Core
     */
    public function getCache($name = null)
    {
        if ($this->hasPluginResource('cachemanager'))
        {
            return $this->getPluginResource('cachemanager')->getCacheManager()->getCache($name == '' ? 'memcache' : $name);
        }
    }

    /**
     * getLog
     * 
     * @return Zend_Log
     */
    public function getLog()
    {
        if ($this->hasPluginResource('log'))
        {
            return $this->getPluginResource('log')->getLog();
        }
    }
}
// End of file : Bootstrap.php
