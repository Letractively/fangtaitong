<?php
/**
 * @version    $Id$
 */
class Process
{
    /**
     * _bootstrap
     * 
     * @var Bootstrap
     */
    protected $_bootstrap;

    /**
     * @var array
     */
    protected $_caches = array();

    /**
     * @var array
     */
    protected $_extras = array();

    /**
     * getBootstrap
     * 
     * @return Bootstrap
     */
    public function getBootstrap()
    {
        if (!$this->_bootstrap)
        {
            $this->_bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        }

        return $this->_bootstrap;
    }

    /**
     * setBootstrap
     * 
     * @param Bootstrap $bootstrap 
     * @return Process
     */
    public function setBootstrap($bootstrap)
    {
        $this->_bootstrap = $bootstrap;
        return $this;
    }

    /**
     * model
     * 
     * @param string $name 
     * @return mixed
     */
    public function model($name, $base = 'ftt')
    {
        return Model::factory($name, $base);
    }

    /**
     * input
     * 
     * @param string $name
     * @return mixed
     */
    public function input($name = null)
    {
        if ($name === null)
        {
            return $_SERVER['argv'];
        }

        $i = array_search($name, $_SERVER['argv'], true);
        if ($i === false || !isset($_SERVER['argv'][$i+1]))
        {
            return null;
        }

        return $_SERVER['argv'][$i+1];
    }

    /**
     * cache
     * 
     * @param string $name 
     * @return mixed
     */
    public function cache($name = null)
    {
        if (!isset($this->_caches[$name]))
        {
            $this->_caches[$name] = $this->getBootstrap()->getCache($name);
        }

        return $this->_caches[$name];
    }

    /**
     * extra
     * 
     * @param string $name 
     * @return mixed
     */
    public function extra($name)
    {
        if (!isset($this->_extras[$name]))
        {
            $this->_extras[$name] = call_user_func(array($this->getBootstrap(), 'get' . ucfirst($name)));
        }

        return $this->_extras[$name];
    }

    /**
     * error
     * 
     * @param mixed $info 
     * @return void
     */
    public function error($info)
    {
        $this->getBootstrap()->getLog()->err($info);
    }

    /**
     * debug
     * 
     * @param mixed $info 
     * @return void
     */
    public function debug($info)
    {
        $this->getBootstrap()->getLog()->debug($info);
    }

    /**
     * flash
     * Send flash message to client
     * 
     * @param int          $stat status code, usually 1(success) or 0(failure).
     * @param array|string $vars message or storage of vars.
     * @return int
     */
    public function flash($stat, $vars = null)
    {
        if (!is_array($vars))
        {
            $vars = array('message' => ($vars === null ? null : (string)$vars));
        }

        $vars['stacode'] = (int)$stat;
        $vars['message'] = array_key_exists('message', $vars) ? (string)$vars['message'] : null;

        $res = sprintf('%s %d %s' . PHP_EOL
            , date('c')
            , $vars['stacode']
            , $vars['message']
        );

        echo $res;
        return $vars['stacode'];
    }

    /**
     * __invoke
     * 
     * @return void
     */
    public function __invoke()
    {
        $this->_prepare() AND $this->_achieve($this->_execute());
    }

    /**
     * _prepare
     * 
     * @return bool
     */
    protected function _prepare()
    {
        return true;
    }

    /**
     * _execute
     * 
     * @return mixed
     */
    protected function _execute()
    {
    }

    /**
     * _achieve
     * 
     * @return void
     */
    protected function _achieve($data = null)
    {
        exit($data);
    }
}
// End of file : Process.php
