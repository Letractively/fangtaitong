<?php
/**
 * @version    $Id$
 */
class Zyon_Acl_Accessor_Role extends Zyon_Acl_Accessor
{
    /**
     * __construct
     * 
     * @param string $identity 
     * @param string $basename 
     * @return void
     */
    public function __construct($identity, $basename = __CLASS__)
    {
        parent::__construct($identity, $basename);
    }
}
// End of file : Role.php
