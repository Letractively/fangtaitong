<?php
/**
 * Helper to format string
 */
class Zend_View_Helper_Format extends Zend_View_Helper_Abstract
{
    /**
     * format
     * 
     * @param string $val
     * @param string $mod
     * @return string
     */
    public function format($val, $mod)
    {
        switch ($mod)
        {
        case 'man-date':
            if ($val = @strtotime($val))
            {
                $day = strtotime(date('Y-m-d'));
                $dva = $day - $val;

                if ($dva > 0)
                {
                    if ($dva <= 86400)
                    {
                        return __('昨天');
                    }

                    if ($dva <= 86400*2)
                    {
                        return __('前天');
                    }
                }
                else
                {
                    if ($dva > -86400)
                    {
                        return __('今天');
                    }

                    if ($dva > -86400*2)
                    {
                        return __('明天');
                    }

                    if ($dva > -86400*3)
                    {
                        return __('后天');
                    }
                }

                return date('Y-m-d', $val);
            }
            
            break;
        
        default :
            break;
        }

        return func_get_arg(0);
    }
}
// End of file : Script.php
