<?php
/**
 * @version    $Id$
 */
class Geek_Controller_Plugin_Router extends Zyon_Controller_Plugin_Router
{
    const KEY_INN = 'in';

    /**
     * @var array
     */
    protected static $_subDomain2ModuleMap = array('i' => 'master', 'm' => 'member');

    /**
     * routeStartup
     * 在 路由器 完成请求的路由前被调用
     * 
     * @param Zend_Controller_Request_Abstract $request 
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        // Do nothing...
        return;

        $hostname = $request->getHttpHost();
        $pathinfo = $request->getPathInfo();

        /**
         * 根据二级域名检测请求的模块
         */
        if (!empty($hostname))
        {
            $segments = explode('.', $hostname);

            if (isset($segments[2]))
            {
                $segmentNum = count($segments);
                $rootDomain = $segments[$segmentNum-2] . '.' . $segments[$segmentNum-1];
                if ($rootDomain === parse_url(URL_FTT, PHP_URL_HOST))
                {
                    $subDomain2 = $segments[$segmentNum-3];
                    if (array_key_exists($subDomain2, static::$_subDomain2ModuleMap))
                    {
                        $module = static::$_subDomain2ModuleMap[$subDomain2];
                        $hostel = explode('/', trim($pathinfo, '/'));
                        $hostel = trim(array_shift($hostel));

                        if ($hostel != '')
                        {
                            $this->getResponse()->setRedirect(
                                URL_FTT . '/' . $module . '/?' . static::KEY_INN . '=' . $hostel
                            )->sendResponse();
                            exit;
                        }
                    }
                }
            }
        }
    }

    /**
     * routeShutdown
     * 在 路由器 完成请求的路由后被调用
     * @param Zend_Controller_Request_Abstract $request 
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        parent::routeShutdown($request);

        if (strtolower($request->getModuleName()) !== 'default')
        {
            $this->getResponse()
                ->setHeader('Pragma', 'no-cache')
                ->setHeader('Cache-Control', 'no-cache')
                ->setHeader('Expires', '0');
        }
    }
}
// End of file : Router.php
