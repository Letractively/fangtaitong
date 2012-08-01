<?php
/**
 * @version    $Id$
 */
class Zyon_Auth
{
    /**
     * _storage
     * 
     * @var Zyon_Auth_Storage
     */
    protected $_storage;

    /**
     * __construct
     * 
     * @param Zyon_Auth_Storage $storage 
     * @return void
     */
    public function __construct(Zyon_Auth_Storage $storage = null)
    {
        $this->_storage = $storage ?: new Zyon_Auth_Storage_Session;
    }

    /**
     * getStorage
     * 
     * @return Zyon_Auth_Storage
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * setStorage
     * 
     * @param Zyon_Auth_Storage $storage 
     * @return void
     */
    public function setStorage(Zyon_Auth_Storage $storage)
    {
        $this->_storage = $storage;
    }

    /**
     * verify
     * 
     * @param Zyon_Auth_Adapter $adapter 
     * @return Zyon_Auth_Outcome
     */
    public function verify(Zyon_Auth_Adapter $adapter = null)
    {
        if ($adapter instanceof Zyon_Auth_Adapter)
        {
            $outcome = $adapter->authenticate();
            $this->logout();

            $session = $outcome->getSession();
            if ($session instanceof Zyon_Auth_Session)
            {
                $storage = $this->getStorage();
                $storage->setIdentity($session->getIdentity());
                $storage->setLifetime($session->getLifetime());
            }
        }
        else
        {
            $storage = $this->getStorage();
            $outcome = ($identity = $storage->getIdentity()) == '' || ($lifetime = $storage->getLifetime()) < 0
                ? new Zyon_Auth_Outcome_Failure
                : new Zyon_Auth_Outcome_Success($identity, $lifetime);
        }

        return $outcome;
    }

    /**
     * retain
     * 
     * @param int $lifetime
     * @return bool
     */
    public function retain($lifetime = 120)
    {
        return $this->getStorage()->setLifetime($lifetime);
    }

    /**
     * logout
     * 
     * @return bool
     */
    public function logout()
    {
        return $this->getStorage()->setIdentity('') || $this->getStorage()->setLifetime(-1);
    }
}
// End of file : Auth.php
