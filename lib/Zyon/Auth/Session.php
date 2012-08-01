<?php
/**
 * @version    $Id$
 */
class Zyon_Auth_Session
{
    /**
     * _creatime
     * 
     * @var int
     */
    protected $_creatime;

    /**
     * _identity
     * 
     * @var string
     */
    protected $_identity;

    /**
     * _lifetime
     * 
     * @var int
     */
    protected $_lifetime;

    /**
     * __construct
     * 
     * @param string $identity 
     * @param int    $lifetime 
     * @return void
     */
    public function __construct($identity, $lifetime)
    {
        $this->_creatime = time();
        $this->_identity = (string)$identity;
        $this->_lifetime = (int)$lifetime;
    }

    /**
     * getCreatime
     * 
     * @return int
     */
    public function getCreatime()
    {
        return $this->_creatime;
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
     * getLifetime
     * 
     * @return int
     */
    public function getLifetime()
    {
        return $this->_lifetime;
    }
}
// End of file : Session.php
