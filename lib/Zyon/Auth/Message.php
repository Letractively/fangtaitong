<?php
/**
 * @version    $Id$
 */
class Zyon_Auth_Message
{
    /**
     * _content
     * 
     * @var string
     */
    protected $_content;

    /**
     * _stacode
     * 
     * @var int 
     */
    protected $_stacode;

    /**
     * __construct
     * 
     * @param string $content
     * @param int    $stacode
     * @return void
     */
    public function __construct($content, $stacode = 0)
    {
        $this->_content = (string)$content;
        $this->_stacode = (int)$stacode;
    }

    /**
     * __toString
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }

    /**
     * getContent
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * getStacode
     * 
     * @return int
     */
    public function getStacode()
    {
        return $this->_stacode;
    }
}
// End of file : Message.php
