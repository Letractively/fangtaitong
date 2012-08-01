<?php
/**
 * @version    $Id$
 */
class Zyon_View_Script
{
    /**
     * __construct
     * 
     * @param array $vars
     * @return void
     */
    public function __construct(array $vars = array())
    {
        foreach ($vars as $key => $val)
        {
            $this->$key = $val;
        }
    }

    /**
     * __invoke
     * 
     * @param string $file 
     * @param array  $vars 
     * @return void
     */
    public function __invoke($file, array $vars = array())
    {
        if (!is_file($file) || !is_readable($file))
        {
            throw new \RuntimeException("Failed to read file \"$file\"");
        }

        unset($file, $vars);
        func_num_args() > 1 AND extract(func_get_arg(1), EXTR_SKIP);

        include func_get_arg(0);
    }

    /**
     * __get
     * 
     * @param string $key 
     * @return mixed
     */
    public function __get($key)
    {
    }

    /**
     * render
     * 
     * @param string $file 
     * @param array  $vars 
     * @return string
     */
    public function render($file, array $vars = array())
    {
        ob_start();
        $this($file, $vars);
        return ob_get_clean();
    }
}
// End of file : Script.php
