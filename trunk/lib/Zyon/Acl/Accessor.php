<?php
/**
 * @version    $Id$
 */
class Zyon_Acl_Accessor
{
    /**
     * _identity
     * 
     * @var string
     */
    protected $_identity;

    /**
     * _basename
     * 
     * @var string
     */
    protected $_basename;

    /**
     * _accessor
     * 
     * @var string
     */
    protected $_accessor;

    /**
     * __construct
     * 
     * @param string $identity 
     * @param string $basename 
     * @return void
     */
    public function __construct($identity, $basename = null)
    {
        $this->_identity = (string)$identity;
        $this->_basename = (string)$basename;
        $this->_accessor = $this->_basename . $this->_identity;
    }

    /**
     * __toString
     * 
     * @return string
     */
    public function __toString()
    {
        return (string)$this->_accessor;
    }

    /**
     * getIdentity
     * 
     * @return string
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * getBasename
     * 
     * @return string
     */
    public function getBasename()
    {
        return $this->_basename;
    }
}
// End of file : Accessor.php
