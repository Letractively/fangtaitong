<?php
/**
 * @version    $Id$
 */
class Model_UserTest extends ModelTestCase
{
    // public function testGetUserById()
    // {
        // $user = $this->model('user')->getUserByEmail('chivan@kuaizubao.com');
        // $this->assertSame('chivan@kuaizubao.com',
            // $user['u_email']
        // );
    // }

    // public function testDelNonactivatedUser()
    // {
        // $this->assertSame('integer', gettype($this->model('user')->delNonactivatedUser(86400)));
    // }

    // public function testDelUser()
    // {
        // $this->assertSame('integer', gettype($this->model('user')->delUser('999999999')));
    // }

    public function testGetActivatedUserNumLimitByHid()
    {
        $num = $this->model('user')->getActivatedUserNumLimitByHid('1');
        var_dump($num);

        $this->assertSame('integer', gettype($num));
    }

    public function testGetActivatedUserNumByHid()
    {
        $num = $this->model('user')->getActivatedUserNumByHid('1');
        var_dump($num);

        $this->assertSame('integer', gettype($num));
    }

    public function testChkCanActivateUserByHid()
    {
        $ret = $this->model('user')->chkCanActivateUserByHid('1');
        var_dump($ret);

        $this->assertSame('boolean', gettype($ret));
    }
}
// End of file : UserTest.php
