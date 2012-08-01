<?php
/**
 * @version    $Id$
 */
class Zyon_Acl_Resource_Comparer_Location implements Zyon_Acl_Resource_Comparer
{
    /**
     * match
     * 
     * @param mixed $pattern
     * @param mixed $subject
     * @return bool
     */
    public function match($pattern, $subject)
    {
        $pattern = (string)$pattern;
        $subject = (string)$subject;

        return (bool)preg_match('~^' . trim($pattern, '/') . '/~i', trim($subject, '/') . '/');
    }
}
// End of file : Location.php
