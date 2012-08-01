<?php
/**
 * @version    $Id$
 */
class Zyon_Auth_Outcome_Failure extends Zyon_Auth_Outcome
{
    /**
     * __construct
     * 
     * @param Zyon_Auth_Message|string $message
     * @return void
     */
    public function __construct($message = null)
    {
        parent::__construct();

        if ($message !== null)
        {
            $this->_message = $message instanceof Zyon_Auth_Message ? $message : new Zyon_Auth_Message((string)$message, 0);
        }
    }
}
// End of file : Failure.php
