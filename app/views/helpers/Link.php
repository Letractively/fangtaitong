<?php
/**
 * Helper to Build Url link
 */
class Zend_View_Helper_Link extends Zend_View_Helper_Abstract
{
    /**
     * link
     * 
     * @param string $name 
     * @param string $suffix 
     * @return string
     */
    public function link(array $args = array(), $base = '')
    {
        $base = (string)$base;
        if ($base == '')
        {
            $base = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        }

        if ($base != '')
        {
            $segs = @parse_url($base);
            if (!empty($segs['query']))
            {
                $base = mb_substr($base, 0, strpos($base, '?'));
                foreach (explode('&', $segs['query']) as $seg)
                {
                    list($key, $val) = explode('=', $seg, 2);
                    array_key_exists($key, $args) OR $args[$key] = $val;
                }
            }
        }

        return rtrim(rtrim($base, '?') . (empty($args) ? '' : '?' . http_build_query($args)), '?');
    }
}
// End of file : Script.php
