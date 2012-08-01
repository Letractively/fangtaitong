<?php
/**
 * @version    $Id$
 */
class Zyon_Auth_Storage_Session implements Zyon_Auth_Storage
{
    const KEY_UID = 'A_AUTH_UID';
    const KEY_LAT = 'A_AUTH_LAT';
    const KEY_LIT = 'A_AUTH_LIT';

    /**
     * _instance
     * 
     * @var string
     */
    protected $_instance;

    /**
     * _lasttime
     * 
     * @var int
     */
    protected $_lasttime;

    /**
     * __construct
     * 
     * @param string $instance
     * @return void
     */
    public function __construct($instance = null)
    {
        if (session_id() == '')
        {
            if (headers_sent($file, $line))
            {
                throw new \LogicException("Headers already sent in \"{$file}\" on line {$line}");
            }

            session_start();
        }

        $this->_instance = (string)$instance;
        if (!isset($_SESSION[$this->_instance]))
        {
            $_SESSION[$this->_instance] = array();
        }

        if (isset($_SESSION[$this->_instance][static::KEY_LAT]))
        {
            $this->_lasttime = $_SESSION[$this->_instance][static::KEY_LAT];
            $_SESSION[$this->_instance][static::KEY_LAT] = time();
        }
        else
        {
            $this->setLasttime(time());
        }
    }

    /**
     * setIdentity
     * 
     * @param string $identity 
     * @return bool
     */
    public function setIdentity($identity)
    {
        if (isset($_SESSION[$this->_instance]) && is_array($_SESSION[$this->_instance]))
        {
            $_SESSION[$this->_instance][static::KEY_UID] = (string)$identity;
            return true;
        }

        return false;
    }

    /**
     * setLifetime
     * 
     * @param int $lifetime
     * @return bool
     */
    public function setLifetime($lifetime)
    {
        if (isset($_SESSION[$this->_instance]) && is_array($_SESSION[$this->_instance]))
        {
            $lifetime = (int)$lifetime;

            if ($lifetime === 0)
            {
                unset($_SESSION[$this->_instance][static::KEY_LIT]);
            }
            else
            {
                $_SESSION[$this->_instance][static::KEY_LIT] = time() + $lifetime;
            }

            $scp = session_get_cookie_params();
            session_set_cookie_params($lifetime, $scp['path'], $scp['domain'], $scp['secure'], $scp['httponly']);

            return session_regenerate_id(true);
        }

        return false;
    }

    /**
     * setLasttime
     * 
     * @param int $lasttime 
     * @return bool
     */
    public function setLasttime($lasttime)
    {
        if (isset($_SESSION[$this->_instance]) && is_array($_SESSION[$this->_instance]))
        {
            $_SESSION[$this->_instance][static::KEY_LAT] = $this->_lasttime = (int)$lasttime;
            return true;
        }

        return false;
    }

    /**
     * getIdentity
     * 
     * @return string
     */
    public function getIdentity()
    {
        return isset($_SESSION[$this->_instance][static::KEY_UID]) ? (string)$_SESSION[$this->_instance][static::KEY_UID] : ''; 
    }

    /**
     * getLifetime
     * 
     * @return int
     */
    public function getLifetime()
    {
        return isset($_SESSION[$this->_instance][static::KEY_LIT]) ? (int)$_SESSION[$this->_instance][static::KEY_LIT] - time() : 0;
    }

    /**
     * getLasttime
     * 
     * @return int
     */
    public function getLasttime()
    {
        return $this->_lasttime;
    }
}
// End of file : Session.php
