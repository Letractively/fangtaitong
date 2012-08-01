<?php
/**
 * @version    $Id$
 */
class Zyon_Auth_Outcome_Success extends Zyon_Auth_Outcome
{
    /**
     * __construct
     * 
     * @param string                   $identity 
     * @param int                      $lifetime 
     * @param Zyon_Auth_Message|string $message
     * @return void
     */
    public function __construct($identity, $lifetime, $message = null)
    {
        parent::__construct();

        $this->_session = new Zyon_Auth_Session($identity, $lifetime);

        if ($message !== null)
        {
            $this->_message = $message instanceof Zyon_Auth_Message ? $message : new Zyon_Auth_Message((string)$message, 1);
        }
    }
}
// End of file : Success.php
