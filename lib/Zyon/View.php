<?php
/**
 * @version    $Id$
 */
class Zyon_View extends Zend_View
{
    /**
     * __invoke
     * 
     * @param mixed $file 
     * @param array $vars 
     * @return void
     */
    public function __invoke($file, array $vars = array())
    {
        $this->_run($file, $vars);
    }

    /**
     * _run
     * 
     * @param mixed $file
     * @param array $vars
     * @return void
     */
    protected function _run()
    {
        $file = func_get_arg(0);
        if (!is_file($file) || !is_readable($file))
        {
            throw new Zend_View_Exception("Failed to read file \"$file\"");
        }
        unset($file);

        func_num_args() > 1 AND is_array(func_get_arg(1)) AND extract(func_get_arg(1), EXTR_SKIP);

        if ($this->_useViewStream && $this->useStreamWrapper())
        {
            include 'zend.view://' . func_get_arg(0);
        }
        else
        {
            include func_get_arg(0);
        }
    }
}
// End of file : View.php
