<?php
/**
 * @version    $Id$
 */
class Zyon_Acl_Instance
{
    /**
     * _exts
     * 
     * @var array
     */
    protected $_exts = array();

    /**
     * _list
     * 
     * @var array
     */
    protected $_list = array();

    /**
     * getExts
     * 
     * @return array
     */
    public function getExts()
    {
        return $this->_exts;
    }

    /**
     * getList
     * 
     * @return array
     */
    public function getList()
    {
        return $this->_list;
    }

    /**
     * extend
     * 
     * @param mixed $accessor
     * @return Zyon_Acl_Instance
     */
    public function extend($accessor)
    {
        array_unshift($this->_exts, $accessor);
        return $this;
    }

    /**
     * handle
     * 
     * @param mixed $resource 
     * @param mixed $decision
     * @return Zyon_Acl_Instance
     */
    public function handle($resource, $decision)
    {
        array_unshift($this->_list, array('resource' => $resource, 'decision' => $decision));
        return $this;
    }

    /**
     * permit
     * 
     * @param mixed $resource 
     * @return Zyon_Acl_Instance
     */
    public function permit($resource)
    {
        return $this->handle($resource, true);
    }

    /**
     * refuse
     * 
     * @param mixed $resource 
     * @return Zyon_Acl_Instance
     */
    public function refuse($resource)
    {
        return $this->handle($resource, false);
    }
}
// End of file : Instance.php
