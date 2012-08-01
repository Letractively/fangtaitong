<?php
/**
 * Helper to get static resource
 */
class Zend_View_Helper_Www extends Zend_View_Helper_Abstract
{
    /**
     * _map
     * 
     * @var array
     */
    protected static $_map = array(
        'static' => array(
            // 'script/jquery.min.js' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js',
        ),
    );

    /**
     * www
     * 
     * @param string $url 
     * @param mixed  $mod 
     * @param mixed  $ver
     * @return string
     */
    public function www($url, $mod = '', $ver = VER_TAG)
    {
        $url = ltrim($url, '/');
        $mod = trim($mod);

        if (isset(static::$_map[$mod][$url]))
        {
            return static::$_map[$mod][$url];
        }

        if ($mod == '')
        {
            // if (APPLICATION_ENV === 'production')
            // {
                // $ext = pathinfo($url, PATHINFO_EXTENSION);
                // if (strtolower($ext) === 'js')
                // {
                    // if (substr($url, -strlen('min.js')) !== 'min.js')
                    // {
                        // $url = substr($url, 0, -strlen('js')) . 'min.' . $ext;
                    // }
                // }
            // }

            return URL_STA . ($ver == '' ? '' : '/' . $ver) . '/' . $url;
        }
    }
}
// End of file : Www.php
