<?php
/**
 * @version    $Id$
 */
class Zyon
{
    /**
     * The version of Zyon
     */
    const VERSION = '0.1 Preview';

    /**
     * import
     * 
     * @param string $name 
     * @param string $path
     * @return void
     */
    public static function import($name, $path = null)
    {
        if (class_exists($name, false) || interface_exists($name, false))
        {
            return;
        }

        if (preg_match('/[^a-z0-9_\\\\]/i', $name))
        {
            throw new InvalidArgumentException('Security check: Illegal character in filename');
        }

        static $load;
        $load === null AND $load = function(){include_once func_get_arg(0);};

        $file = ltrim(strtr($name, '_\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR) . '.php';
        $path === null OR $file = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;

        if ($fp = @fopen($file, 'r', ($path === null)))
        {
            @fclose($fp);
            $load($file);
        }

        if (!class_exists($name, false) && !interface_exists($name, false))
        {
            throw new RuntimeException("File \"{$file}\" does not exist or class \"{$name}\" was not found in the file");
        }
    }

    /**
     * launch
     * 
     * @return void
     */
    public static function launch()
    {
        mb_internal_encoding('UTF-8');
        require_once 'Zyon.conf.php';
    }
}
// End of file : Zyon.php
