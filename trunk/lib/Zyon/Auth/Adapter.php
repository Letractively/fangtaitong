<?php
/**
 * @version    $Id$
 */
interface Zyon_Auth_Adapter
{
    /**
     * authenticate
     * 
     * @return Zyon_Auth_Outcome
     */
    public function authenticate();
}
// End of file : Adapter.php
