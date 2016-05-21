<?php
require_once('../axenia/core/util.php');

class UtilTest extends PHPUnit_Framework_TestCase
{
    public function testIsInEnum()
    {
        $this->assertFalse(Util::isInEnum("343434,434234,1", 2));
        $this->assertTrue(Util::isInEnum("343434,434234,1", 434234));
    }

    public function testInsert()
    {
        $temp = Util::insert(':name is :age years old.', array('name' => 'Bob', 'age' => '65'));
        $this->assertTrue($temp == "Bob is 65 years old.");
        $temp = Util::insert(':0 is :1 years old.', array('Bob', '65'));
        $this->assertTrue($temp == "Bob is 65 years old.");
    }

    public function testPosInEnum()
    {
        $this->assertTrue(Util::posInEnum("q,w,e", 'q') == 0);
        $this->assertTrue(Util::posInEnum("q, w,e", 'w') == -1);
        $this->assertTrue(Util::posInEnum("q,w,e", 'k') == -1);
        $this->assertTrue(Util::posInEnum("q,w,e", 'e') == 2);
    }

    public function testGetFullName()
    {
        $this->assertTrue(Util::getFullName("username", "first", "last") == "username (first last)");
        $this->assertTrue(Util::getFullName("", "first", "last") == "first last");
        $this->assertTrue(Util::getFullName("", "first", "") == "first");
        $this->assertTrue(Util::getFullName("", "", "last") == "last");
        $this->assertTrue(Util::getFullName("", "", "") == false);
        $this->assertTrue(Util::getFullName("username", "", "") == "username");
        $this->assertTrue(Util::getFullName("username", "first", "") == "username (first)");
        $this->assertTrue(Util::getFullName("username", "", "last") == "username (last)");
    }

    public function testIsBetween()
    {
        $this->assertTrue(Util::isBetween(200, 200, 500));
        $this->assertFalse(Util::isBetween(0, 200, 500));
        $this->assertFalse(Util::isBetween(500, 200, 500));

        $this->assertTrue(Util::isBetween(0, -0.5, 0.5));
        $this->assertFalse(Util::isBetween(0.5, -0.5, 0.5));
        $this->assertTrue(Util::isBetween(-0.5, -0.5, 0.5));

    }

    public function testSomeTests()
    {
        $this->assertTrue(round(-0.5) == -1);

    }

}



