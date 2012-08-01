<?php
/**
 * @version    $Id$
 */
class Model_Hotel_LabelTest extends ModelTestCase
{
    public function testGetLabel()
    {
        var_dump($this->model('hotel.label')->getLabel('1'));
    }

    public function testGetLabelAryByIds()
    {
        var_dump($this->model('hotel.label')->getLabelAryByIds(array('1', '2')));
    }

    public function testGetLabelIdsByHid()
    {
        var_dump($this->model('hotel.label')->getLabelIdsByHid('0'));
    }

    public function testGetLabelIds()
    {
        var_dump($this->model('hotel.label')->getLabelIds(array('name' => '电磁炉', 'hid' => '1')));
    }
}
// End of file : Label.php
