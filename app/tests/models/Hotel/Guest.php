<?php
/**
 * @version    $Id$
 */
class Model_Hotel_GuestTest extends ModelTestCase
{
    public function testAddGuest()
    {
        $guest = $this->model('guest')->getNewGuest('1', '1', '小螺', '13800138000', '1');
        $this->assertSame(true, (bool)$this->model('guest')->addGuest($guest));

        $guest = $this->model('guest')->getNewGuest('1', '1', '小小', '13700137000');
        $this->assertSame(true, (bool)$this->model('guest')->addGuest($guest));
    }

    public function testGetGuest()
    {
        var_dump($this->model('guest')->getGuest('1'));
    }

    public function testGetGuestAryByOid()
    {
        var_dump($this->model('guest')->getGuestAryByOid('1'));
    }
}
// End of file : Guest.php
