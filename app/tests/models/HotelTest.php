<?php
/**
 * @version    $Id$
 */
class Model_HotelTest extends ModelTestCase
{
    public function testAddHotel()
    {
        // $hotel = $this->model('hotel')->getNewHotel('shell2', '螺壳', '13800138000');
        // $this->assertSame(true, is_numeric($this->model('hotel')->addHotel($hotel)));
    }

    public function testModHotel()
    {
        // $ret = $this->model('hotel')->modHotel('1', array(
            // 'h_iname' => 'shell', 'h_domain' => 'shell.ftt.com', 'h_uid' => '1'
        // ));

        // $this->assertSame('integer', gettype($ret));
    }

    public function testGetHotelById()
    {
        $hotel = $this->model('hotel')->getHotel('1');
        $this->assertSame('array', gettype($hotel));
        $this->assertSame('1', $hotel['h_id']);
    }

    public function testGetHotelByIname()
    {
        $hotel = $this->model('hotel')->getHotelByIname('shell');
        $this->assertSame('array', gettype($hotel));
        $this->assertSame('shell', $hotel['h_iname']);
    }

    public function testGetHotelByDomain()
    {
        $hotel = $this->model('hotel')->getHotelByDomain('shell.ftt.com');
        $this->assertSame('array', gettype($hotel));
        $this->assertSame('shell.ftt.com', $hotel['h_domain']);
    }

    public function testDelHotelByIname()
    {
        $hotel = $this->model('hotel')->getHotelByIname('shell2');
        $this->assertSame(1, $this->model('hotel')->delHotel($hotel['h_id']));
    }
}
