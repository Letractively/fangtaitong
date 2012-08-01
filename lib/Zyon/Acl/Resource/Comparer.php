<?php
/**
 * @version    $Id$
 */
interface Zyon_Acl_Resource_Comparer
{
    /**
     * match
     * 
     * @param mixed $pattern
     * @param mixed $subject
     * @return bool
     */
    public function match($pattern, $subject);
}
// End of file : Comparer.php
