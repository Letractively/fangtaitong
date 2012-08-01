<?php
/**
 * @version    $Id$
 */
class Model
{
    /**
     * @var array
     */
    protected static $_models = array();

    /**
     * factory
     * 
     * @param string $name 
     * @param string $base
     * @return mixed
     */
    public static function factory($name, $base)
    {
        $name = strtolower(strtr($name, '.', '_'));
        $base = strtolower(strtr($base, '.', '_'));

        $class = implode('_', array_map('ucfirst',
            explode('_', 'Model_' . ($base == '' ? '' : $base . '_') . $name)
        ));

        if (!isset(static::$_models[$class]))
        {
            static::$_models[$class] = new $class($name, $base);
        }

        return static::$_models[$class];
    }
}
// End of file : Model.php
