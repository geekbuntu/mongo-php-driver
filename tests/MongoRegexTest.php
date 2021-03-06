<?php
require_once 'PHPUnit/Framework.php';

/**
 * Test class for MongoRegex
 * Generated by PHPUnit on 2009-04-10 at 13:30:28.
 */
class MongoRegexTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException MongoException
     */
    public function testInvalidParam1() {
        $r1 = new MongoRegex("");
    }

    /**
     * @expectedException MongoException
     */
    public function testInvalidParam2() {
        $r1 = new MongoRegex("/");
    }

    /**
     * @expectedException MongoException
     */
    public function testInvalidParam3() {
        $r1 = new MongoRegex("345");
    }

    /**
     * @expectedException MongoException
     */
    public function testInvalidParam4() {
        $r1 = new MongoRegex("b");
    }

    public function testBasic() {
        $r1 = new MongoRegex("//");
        $this->assertEquals($r1->regex, "");
        $this->assertEquals($r1->flags, "");

        $r2 = new MongoRegex("/foo/bar");
        $this->assertEquals($r2->regex, "foo");
        $this->assertEquals($r2->flags, "bar");

        $r3 = new MongoRegex($r2);
        $this->assertEquals($r3->regex, "foo");
        $this->assertEquals($r3->flags, "bar");

        $stupid_str = "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz";
        $rstupid = new MongoRegex("/${stupid_str}/flagflagflagflagflag");
        $this->assertEquals($rstupid->regex, $stupid_str);
        $this->assertEquals($rstupid->flags, "flagflagflagflagflag");

        $m = new Mongo();
        $c = $m->selectCollection('phpunit', 'regex');
        $c->drop();
        $c->insert(array('x' => 0, 'r1' => $r1));
        $c->insert(array('x' => 1, 'r2' => $r2));
        $c->insert(array('x' => 2, 'stupid' => $rstupid));

        $obj = $c->findOne(array('x' => 0));
        $this->assertEquals($obj['r1']->regex, "");
        $this->assertEquals($obj['r1']->flags, "");

        $obj = $c->findOne(array('x' => 1));
        $this->assertEquals($obj['r2']->regex, "foo");
        $this->assertEquals($obj['r2']->flags, "bar");

        $obj = $c->findOne(array('x' => 2));
        $this->assertEquals($obj['stupid']->regex, $stupid_str);
        $this->assertEquals($obj['stupid']->flags, "flagflagflagflagflag");
    }
}

?>
