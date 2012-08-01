<?php
/**
 * @version    $Id$
 */
class Zyon_Auth_Factory
{
    /**
     * _instances
     * 
     * @var array Zyon_Auth[]
     */
    protected static $_instances = array();

    /**
     * getAuth
     * 
     * @param string $key
     * @param string $cls
     * @return Zyon_Auth
     */
    public static function getAuth($key = null, $cls = 'Zyon_Auth_Storage_Session')
    {
        $key = (string)$key;
        $cls = (string)$cls;

        if (!isset(static::$_instances[$key]))
        {
            static::$_instances[$key] = new Zyon_Auth(new $cls($key));
        }

        return static::$_instances[$key];
    }
}
// End of file : Factory.php
