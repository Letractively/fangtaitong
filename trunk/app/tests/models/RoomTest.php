<?php
/**
 * @version    $Id$
 */
class Model_RoomTest extends ModelTestCase
{
    public function testAddRoom()
    {
        $room = $this->model('room')->getNewRoom('1', '我家', 50, 30);
        $this->assertSame(true, is_numeric($this->model('room')->addRoom($room)));
    }

    public function testModRoom()
    {
        $ret = $this->model('room')->modRoom('1', array(
            'r_address' => '广州市白云区',
        ));

        $this->assertSame('integer', gettype($ret));


        $ret = $this->model('room')->modRoom('1', array(
            'r_name' => '新名字',
            'r_address' => '广州市白云区',
        ));
        $this->assertSame(false, $ret);
    }

    public function testGetRoomById()
    {
        $room = $this->model('room')->getRoom('1');
        $this->assertSame('array', gettype($room));
        $this->assertSame('1', $room['r_id']);
    }

    public function testGetRoomByRname()
    {
        $room = $this->model('room')->getRoomByName('我家', '1');
        $this->assertSame('array', gettype($room));
        $this->assertSame('我家', $room['r_name']);

        $room = $this->model('room')->getRoomByName('不存在', '1');
        $this->assertSame(true, empty($room));
    }

    public function testGetRoomIdsByHid()
    {
        $rs = $this->model('room')->getRoomIdsByHid('0');
        $this->assertSame(true, empty($rs));
        
        $rs = $this->model('room')->getRoomIdsByHid('1');
        $this->assertSame(true, count($rs)>0);
    }

    public function testGetRoomAryByIds()
    {
        $rs = $this->model('room')->getRoomAryByIds(array('1', '2'));
        $this->assertSame(true, count($rs)>0);
    }
}
