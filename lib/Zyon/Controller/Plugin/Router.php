<?php
/**
 * @version    $Id$
 */
class Zyon_Controller_Plugin_Router extends Zend_Controller_Plugin_Abstract
{
    /**
     * Key name of content-type
     */
    const KEY_EXT = 'ct';

    /**
     * routeShutdown
     * 在 路由器 完成请求的路由后被调用
     * @param Zend_Controller_Request_Abstract $request 
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        /**
         * 检测请求的Content-type类型
         */
        $pathinfo = $request->getPathInfo();
        if (!empty($pathinfo))
        {
            if ($extension = pathinfo($pathinfo, PATHINFO_EXTENSION))
            {
                if (preg_match('/^[-a-z0-9]+$/i', $extension))
                {
                    $request->setParam(static::KEY_EXT, strtolower($extension));
                }
            }
        }

        /**
         * 检测是否支持json响应
         */
        if ($request->getParam(static::KEY_EXT) == '')
        {
            $accept = $request->getServer('HTTP_ACCEPT');
            if (!empty($accept))
            {
                if (strpos($accept, 'json') !== false)
                {
                    $request->setParam(static::KEY_EXT, 'json');
                }
            }
        }

        /**
         * 格式化请求目标信息，不允许[-a-zA-Z0-9]以外的字符
         */
        $pattern = '/[^-a-zA-Z0-9].*/';
        $request->setModuleName(preg_replace($pattern, '', $request->getModuleName()));
        $request->setControllerName(preg_replace($pattern, '', $request->getControllerName()));
        $request->setActionName(preg_replace($pattern, '', $request->getActionName()));
    }
}
// End of file : Router.php
