<?php
/**
 * En quote
 */
class Zend_View_Helper_Quote extends Zend_View_Helper_Abstract
{
    /**
     * quote
     *
     * @param string $var
     * @return string
     */
    public function quote($var)
    {
        return '"' . str_replace('"', '""', $var) . '"';
    }
}
// End of file : Quote.php
