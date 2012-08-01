<?php
require_once 'Bootstrap.php';
class BootstrapCli extends Bootstrap
{
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
                'process' => array(
                    'path'      => 'process/',
                    'namespace' => 'Process',
                ),
            ),
        ));

        require_once 'Model.php';
        require_once 'Process.php';
    }
}
// End of file : Bootstrap.cli.php
