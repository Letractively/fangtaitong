<?php
/**
 * Helper to load view
 */
class Zend_View_Helper_View extends Zend_View_Helper_Abstract
{
    /**
     * view
     * 
     * @param mixed $name 
     * @param array $vars 
     * @return string
     */
    public function view($name, array $vars = array())
    {
        $file = $this->view->getScriptPath($name . '.phtml');
        if (!is_file($file) || !is_readable($file))
        {
            throw new Zend_View_Exception("Failed to load view \"$name\"");
        }

        $view = $this->view;
        $view($file, $vars);
    }
}
// End of file : View.php
