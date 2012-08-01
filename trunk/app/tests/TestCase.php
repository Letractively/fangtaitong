<?php
/**
 * @version    $Id$
 */
class TestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * setUp
     * 
     * @return void
     */
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, 
            array(
                'config' => array(
                    APPLICATION_PATH . '/configs/application.ini',
                    APPLICATION_PATH . '/configs/application.cli.ini',
                )
            )
        );
        parent::setUp();

        $this->setupBootstrap();
    }

    /**
     * setupBootstrap
     * 
     * @return void
     */
    public function setupBootstrap()
    {
        $this->frontController->setParam('bootstrap', $this->bootstrap->getBootstrap());
    }
}
// End of file : TestCase.php
