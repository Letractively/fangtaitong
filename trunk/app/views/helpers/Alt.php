<?php
/**
 * Return default value if param 1 is empty.
 */
class Zend_View_Helper_Alt extends Zend_View_Helper_Abstract
{
    /**
     * alt
     * 
     * @return bool
     */
    public function alt($val, $def = '-')
    {
        return $val == '' ? $def : $val;
    }
}
// End of file : Alt.php
