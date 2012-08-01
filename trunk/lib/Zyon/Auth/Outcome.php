<?php
/**
 * @version    $Id$
 */
class Zyon_Auth_Outcome
{
    /**
     * _message
     * 
     * @var Zyon_Auth_Message
     */
    protected $_message;

    /**
     * _session
     * 
     * @var Zyon_Auth_Session
     */
    protected $_session;

    /**
     * __construct
     * 
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * getMessage
     * 
     * @return Zyon_Auth_Message
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * getSession
     * 
     * @return Zyon_Auth_Session
     */
    public function getSession()
    {
        return $this->_session;
    }
}
// End of file : Outcome.php
