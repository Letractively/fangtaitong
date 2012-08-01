<?php
/**
 * Helper to say hello
 */
class Zend_View_Helper_Hello extends Zend_View_Helper_Abstract
{
    public function hello($word = 'world')
    {
        return "Hello {$word}!";
    }
}
