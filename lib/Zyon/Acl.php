<?php
/**
 * @version    $Id$
 */
class Zyon_Acl
{
    /**
     * _acl
     * 
     * @var array
     */
    protected $_acl = array();

    /**
     * _instanceDiscover
     * 
     * @var Zyon_Acl_Instance_Discover
     */
    protected $_instanceDiscover;

    /**
     * _resourceComparer
     * 
     * @var Zyon_Acl_Resource_Comparer
     */
    protected $_resourceComparer;

    /**
     * setInstanceDiscover
     * 
     * @param Zyon_Acl_Instance_Discover $function 
     * @return void
     */
    public function setInstanceDiscover(Zyon_Acl_Instance_Discover $discover)
    {
        $this->_instanceDiscover = $discover;
    }

    /**
     * getInstanceDiscover
     * 
     * @return Zyon_Acl_Instance_Discover
     */
    public function getInstanceDiscover()
    {
        return $this->_instanceDiscover;
    }

    /**
     * setResourceComparer
     * 
     * @param Zyon_Acl_Resource_Comparer $comparer 
     * @return void
     */
    public function setResourceComparer(Zyon_Acl_Resource_Comparer $comparer)
    {
        $this->_resourceComparer = $comparer;
    }

    /**
     * getResourceComparer
     * 
     * @return Zyon_Acl_Resource_Comparer
     */
    public function getResourceComparer()
    {
        if (!($this->_resourceComparer instanceof Zyon_Acl_Resource_Comparer))
        {
            $this->_resourceComparer = new Zyon_Acl_Resource_Comparer_Location;
        }

        return $this->_resourceComparer;
    }

    /**
     * verify
     * 
     * @param mixed                      $accessor
     * @param mixed                      $resource
     * @param Zyon_Acl_Resource_Comparer $comparer
     * @return bool
     */
    public function verify($accessor, $resource, Zyon_Acl_Resource_Comparer $comparer = null)
    {
        if ($instance = $this->select($accessor))
        {
            if ($comparer === null)
            {
                $comparer = $this->getResourceComparer();
            }

            foreach ($instance->getList() as $ele)
            {
                if ($comparer->match($ele['resource'], $resource))
                {
                    $decision = $ele['decision'];
                    if (is_bool($decision) || is_bool($decision = call_user_func($decision, $accessor, $resource)))
                    {
                        return $decision;
                    }
                }
            }

            foreach ($instance->getExts() as $ext)
            {
                if (is_bool($decision = $this->verify($ext, $resource, $comparer)))
                {
                    return $decision;
                }
            }
        }
    }

    /**
     * select
     * 
     * @param mixed $accessor
     * @return Zyon_Acl_Instance
     */
    public function select($accessor)
    {
        if (isset($this->_acl[(string)$accessor]))
        {
            return $this->_acl[(string)$accessor];
        }

        if (($discover = $this->getInstanceDiscover()) && $discover instanceof Zyon_Acl_Instance_Discover)
        {
            if (($instance = $discover->discover($accessor)) && $instance instanceof Zyon_Acl_Instance)
            {
                return $this->create($accessor, $instance);
            }
        }
    }

    /**
     * create
     * 
     * @param mixed             $accessor 
     * @param Zyon_Acl_Instance $instance 
     * @return Zyon_Acl_Instance
     */
    public function create($accessor, Zyon_Acl_Instance $instance = null)
    {
        return $this->_acl[(string)$accessor] = $instance === null ? new Zyon_Acl_Instance : $instance;
    }

    /**
     * delete
     * 
     * @param mixed $accessor
     * @return Zyon_Acl
     */
    public function delete($accessor)
    {
        unset($this->_acl[(string)$accessor]);
        return $this;
    }
}
// End of file : Acl.php
