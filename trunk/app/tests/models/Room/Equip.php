<?php
/**
 * @version    $Id$
 */
class Model_Room_EquipTest extends ModelTestCase
{
    // public function testAddEquip()
    // {
        // var_dump($this->model('room.equip')->addEquip($this->model('room.equip')->getNewEquip(
            // '电磁炉',
            // '3',
            // $this->model('room.equip')->getTypeByName('厨具'),
            // '0',
            // '0'
        // )));
    // }

    public function testAddEquipAry()
    {
        // var_dump($this->model('room.equip')->addEquipAry(array(
            // $this->model('room.equip')->getNewEquip('电磁炉', '1', $this->model('room.equip')->getTypeByName('厨具'), '0', '0'),
            // $this->model('room.equip')->getNewEquip('电磁炉1', '1', $this->model('room.equip')->getTypeByName('厨具'), '0', '0'),
            // $this->model('room.equip')->getNewEquip('电磁炉2', '1', $this->model('room.equip')->getTypeByName('电器'), '0', '0'),
            // $this->model('room.equip')->getNewEquip('电磁炉', '1', $this->model('room.equip')->getTypeByName('厨具'), '1', '0'),
            // $this->model('room.equip')->getNewEquip('电磁炉', '1', $this->model('room.equip')->getTypeByName('厨具'), '1', '1')
        // )));
    }

    public function testGetEquip()
    {
        var_dump($this->model('room.equip')->getEquip('1'));
    }

    public function testGetEquipAryByIds()
    {
        var_dump($this->model('room.equip')->getEquipAryByIds(array('1')));
    }

    public function testGetEquipIdsByHid()
    {
        var_dump($this->model('room.equip')->getEquipIdsByHid('0'));
    }

    public function testGetEquipIdsByRid()
    {
        var_dump($this->model('room.equip')->getEquipIdsByRid('0'));
    }

    public function testGetEquipIds()
    {
        var_dump($this->model('room.equip')->getEquipIds(array('re_name' => '电磁炉')));
    }

    public function testModEquipQnty()
    {
        var_dump($this->model('room.equip')->modEquipQnty(1, 999));
    }
}
// End of file : Equip.php
