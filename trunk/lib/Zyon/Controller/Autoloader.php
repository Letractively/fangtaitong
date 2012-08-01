<?php
/**
 * @version    $Id$
 */
class Zyon_Controller_Autoloader implements Zend_Loader_Autoloader_Interface
{
    /**
     * _basePath
     * 
     * @var string
     */
    protected $_basePath;

    /**
     * __construct
     * 
     * @param string $basePath 
     * @return void
     */
    public function __construct($basePath = null)
    {
        $basePath === null AND $basePath = APPLICATION_PATH . '/controllers/';
        $this->setBasePath($basePath);
    }

    /**
     * setBasePath
     * 
     * @param mixed $basePath 
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->_basePath = rtrim((string)$basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * getBasepath
     * 
     * @return string
     */
    public function getBasepath()
    {
        return $this->_basePath;
    }

    /**
     * autoload
     * 
     * @param string $class 
     * @return void
     */
    public function autoload($class)
    {
        if (isset($class[10]) && substr($class, -10) === 'Controller')
        {
            Zend_Loader::loadClass($class, $this->_basePath);
        }
    }
}
// End of file : Autoloader.php
