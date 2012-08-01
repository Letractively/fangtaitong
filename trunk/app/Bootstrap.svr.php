<?php
require_once 'Bootstrap.php';
class BootstrapSvr extends Bootstrap
{
    /**
     * _acls
     * 
     * @var Zyon_Acl
     */
    protected $_acl;

    protected function _initAutoload()
    {
        new Zend_Loader_Autoloader_Resource(array(
            'namespace'     => '',
            'basePath'      => APPLICATION_PATH,
            'resourceTypes' => array(
                'model' => array(
                    'path'      => 'models/',
                    'namespace' => 'Model',
                ),
            ),
        ));

        Zend_Loader_Autoloader::getInstance()->pushAutoloader(
            new Zyon_Controller_Autoloader(APPLICATION_PATH . '/controllers/')
        );

        require_once 'Model.php';
        require_once 'Controller.php';
    }

    protected function _initSession()
    {
        if ($this->hasPluginResource('session') && !Zend_Session::getSaveHandler())
        {
            Zend_Session::setSaveHandler($this->getPluginResource('session')->getSaveHandler());
        }
        Zend_Session::start();
    }

    protected function _initController()
    {
        $this->bootstrap('FrontController');
        $this->frontController->setModuleControllerDirectoryName('')
            ->addModuleDirectory(APPLICATION_PATH . '/controllers');
    }

    protected function _initView()
    {
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')
            ->setView(new Zyon_View)
            ->setViewBasePathSpec(APPLICATION_PATH . '/views/')
            ->setViewScriptPathSpec(':module/:controller/:action.:suffix')
            ->setViewScriptPathNoControllerSpec(':module/:action.:suffix')
            ->setViewSuffix('phtml');
    }

    /**
     * getAcl
     * 
     * @return Zyon_Acl
     */
    public function getAcl()
    {
        if (!($this->_acl instanceof Zyon_Acl))
        {
            /**
             * 创建FTT系统的默认角色权限控制表，
             * 规则基于 $module/$controller/$action 目录表示
             */
            $acl = new Zyon_Acl;
            $acl->setInstanceDiscover(Model::factory('acl', 'ftt'));

            // 系统访客
            $acl->create('guest')
                ->refuse('.*')
                ->permit('[a-zA-Z0-9]+/index');
            $acl->create('')->extend('guest');

            // 旅店管理者
            $acl->create('master')->extend('guest')
                ->permit('master')
                ->refuse('master/account');

            // 旅店根用户
            $acl->create('rooter')->extend('master')
                ->permit('master');

            $this->_acl = $acl;
        }

        return $this->_acl;
    }
}
// End of file : Bootstrap.svr.php
