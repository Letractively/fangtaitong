<?php
/**
 * @version    $Id$
 */
class Model_Mail_JobTest extends ModelTestCase
{
    public function testGetUndoJobAll()
    {
        $this->assertSame(gettype($this->model('mail.job')->getUndoJobIds()), 'array');
    }

    public function testModJob()
    {
        //$this->assertSame(1, $this->model('mail.job')->modJob('30', array('mj_ecode' => '999')));
    }
}
// End of file : UserTest.php
