<?php

class IndexController extends Controller
{
    public function indexAction()
    {
       $this->_forward('index', 'index', 'master');
    }

    public function historyAction()
    {
    }
}
// End of file : IndexController.php
