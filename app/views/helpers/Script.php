<?php
/**
 * Helper to fetch pathname of script
 */
class Zend_View_Helper_Script extends Zend_View_Helper_Abstract
{
    /**
     * script
     * 
     * @param string $name 
     * @param string $suffix 
     * @return string
     */
    public function script($name, $suffix = '.phtml')
    {
        return $this->view->getScriptPath($name . $suffix);
    }
}
// End of file : Script.php
