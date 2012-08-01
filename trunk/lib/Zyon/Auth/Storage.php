<?php
/**
 * @version    $Id$
 */
interface Zyon_Auth_Storage
{
    /**
     * setIdentity
     * 
     * @param string $identity 
     * @return bool
     */
    public function setIdentity($identity);

    /**
     * setLifetime
     * 
     * @param int $lifetime
     * @return bool
     */
    public function setLifetime($lifetime);

    /**
     * getIdentity
     * 
     * @return string
     */
    public function getIdentity();

    /**
     * getLifetime
     * 
     * @return int
     */
    public function getLifetime();
}
// End of file : Storage.php
