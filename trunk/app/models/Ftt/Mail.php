<?php
/**
 * @version    $Id$
 */
class Model_Ftt_Mail extends Zyon_Model_Ftt
{
    /**
     * getTpl
     * 
     * @param string $name 
     * @param array  $vars
     * @return string
     */
    public function getTpl($name, array $vars = array())
    {
        try
        {
            $view = new Zyon_View_Script;
            return $view->render(APPLICATION_PATH . '/../var/tpls/mail/' . $name . '.php', $vars);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }
}
// End of file : Mail.php
