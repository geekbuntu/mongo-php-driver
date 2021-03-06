<?php
require_once 'PHPUnit/Framework.php';

/**
 * Test class for Mongo.
 * Generated by PHPUnit on 2009-04-09 at 18:09:02.
 */
class MongoDBTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    MongoDB
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $m = new Mongo();
        $this->object = new MongoDB($m, "phpunit");
        $this->object->start = memory_get_usage(true);
    }

    protected function tearDown() {
      //        $this->assertEquals($this->object->start, memory_get_usage(true));
    }

    /**
     * @expectedException Exception 
     */
    public function testDumbDBName3() {
      $db = new MongoDB(new Mongo(), "\\");
    }

    /**
     * @expectedException Exception 
     */
    public function testDumbDBName4() {
      $db = new MongoDB(new Mongo(), "\$");
    }

    /**
     * @expectedException Exception
     */
    public function testDumbDBName5() {
      $db = new MongoDB(new Mongo(), "/");
    }

    public function test__toString() {
        if (preg_match("/5\.1\../", phpversion())) {
            $this->markTestSkipped("No implicit __toString in 5.1");
            return;
        }

        $this->assertEquals((string)$this->object, "phpunit");
    }

    public function testGetGridFS() {
        if (preg_match("/5\.1\../", phpversion())) {
            $this->markTestSkipped("No implicit __toString in 5.1");
            return;
        }

        $grid = $this->object->getGridFS();

        $this->assertTrue($grid instanceof MongoGridFS);
        $this->assertTrue($grid instanceof MongoCollection);

        $this->assertEquals((string)$grid, "phpunit.fs.files");
        $this->assertEquals((string)$grid->chunks, "phpunit.fs.chunks");

        $grid = $this->object->getGridFS("foo");
        $this->assertEquals((string)$grid, "phpunit.foo.files");
        $this->assertEquals((string)$grid->chunks, "phpunit.foo.chunks");

        $grid = $this->object->getGridFS("foo", "bar");
        $this->assertEquals((string)$grid, "phpunit.foo.files");
        $this->assertEquals((string)$grid->chunks, "phpunit.foo.chunks");
    }

    public function testGetSetProfilingLevel() {
        $created = $this->object->createCollection("system.profile", true, 5000);

        $prev = $this->object->setProfilingLevel(MongoDB::PROFILING_ON);
        $level = $this->object->getProfilingLevel();
        $this->assertEquals($level, MongoDB::PROFILING_ON);

        $prev = $this->object->setProfilingLevel(MongoDB::PROFILING_SLOW);
        $level = $this->object->getProfilingLevel();
        $this->assertEquals($level, MongoDB::PROFILING_SLOW);
        $this->assertEquals($prev, MongoDB::PROFILING_ON);

        $prev = $this->object->setProfilingLevel(MongoDB::PROFILING_OFF);
        $level = $this->object->getProfilingLevel();
        $this->assertEquals($level, MongoDB::PROFILING_OFF);
        $this->assertEquals($prev, MongoDB::PROFILING_SLOW);

        $prev = $this->object->setProfilingLevel(MongoDB::PROFILING_OFF);
        $this->assertEquals($prev, MongoDB::PROFILING_OFF);
    }


    public function testDrop() {
      $r = $this->object->drop();
      $this->assertEquals(true, (bool)$r['ok'], json_encode($r));
    }

    public function testRepair() {
      $r = $this->object->repair();
      $this->assertEquals(true, (bool)$r['ok'], json_encode($r));
      $r = $this->object->repair(true);
      $this->assertEquals(true, (bool)$r['ok'], json_encode($r));
      $r = $this->object->repair(true, true);
      $this->assertEquals(true, (bool)$r['ok'], json_encode($r));
    }

    public function testSelectCollection() {
        if (preg_match("/5\.1\../", phpversion())) {
            $this->markTestSkipped("No implicit __toString in 5.1");
            return;
        }

        $this->assertEquals((string)$this->object->selectCollection('x'), 'phpunit.x');
        $this->assertEquals((string)$this->object->selectCollection('..'), 'phpunit...');
        $this->assertEquals((string)$this->object->selectCollection('a b c'), 'phpunit.a b c');
    }

    public function testCreateCollection() {
        $ns = $this->object->selectCollection('system.namespaces');
        $this->object->drop('z');
        $this->object->drop('zz');
        $this->object->drop('zzz');

        $this->object->createCollection('z');
        $obj = $ns->findOne(array('name' => 'phpunit.z'));
        $this->assertNotNull($obj);

        $c = $this->object->createCollection('zz', true, 100);
        $obj = $ns->findOne(array('name' => 'phpunit.zz'));
        $this->assertNotNull($obj);

        for($i=0;$i<10;$i++) {
            $c->insert(array('x' => $i));
        }
        $this->assertLessThan(10, $c->count());

        $c = $this->object->createCollection('zzz', true, 1000, 5);
        $obj = $ns->findOne(array('name' => 'phpunit.zzz'));
        $this->assertNotNull($obj);

        for($i=0;$i<10;$i++) {
            $c->insert(array('x' => $i));
        }
        $this->assertEquals(5, $c->count());
    }
    
    public function testDropCollection() {
        $ns = $this->object->selectCollection('system.namespaces');

        $c = $this->object->selectCollection("droopy");
        $c->insert(array('foo' => 'bar'));
        $c->ensureIndex('foo');
        $c->findOne();

        $this->assertNotNull($ns->findOne(array('name'=> new MongoRegex('/droopy/'))));
        $c->drop();
        $this->assertEquals($ns->findOne(array('name'=> new MongoRegex('/droopy/'))), null);
    }

    public function testDropCollection2() {
      $ns = $this->object->selectCollection('system.namespaces');

      $this->object->x->insert(array("foo"=>"bar"));
      $this->assertNotNull($ns->findOne(array('name'=> new MongoRegex('/.x$/'))));

      $this->object->dropCollection('x');
      $this->assertEquals($ns->findOne(array('name'=> new MongoRegex('/.x$/'))), null);

      $this->object->x->insert(array("foo"=>"bar"));
      $this->assertNotNull($ns->findOne(array('name'=> new MongoRegex('/.x$/'))));

      $this->object->dropCollection($this->object->x);
      $this->assertEquals($ns->findOne(array('name'=> new MongoRegex('/.x$/'))), null);

      $mem = memory_get_usage(true);
      for ($i=0; $i<1000; $i++) {
        $this->object->dropCollection("form");
      }
      $this->assertEquals($mem, memory_get_usage(true));

      $mem = memory_get_usage(true);
      for ($i=0; $i<1000; $i++) {
        $this->object->dropCollection($this->object->form);
      }
      $this->assertEquals($mem, memory_get_usage(true));
    }

    public function testListCollections() {
        $ns = $this->object->selectCollection('system.namespaces');

        for($i=0;$i<10;$i++) {
            $c = $this->object->selectCollection("x$i");
            $c->insert(array("foo" => "bar"));
        }

        $list = $this->object->listCollections();
        for($i=0;$i<10;$i++) {
            $this->assertTrue($list[$i] instanceof MongoCollection);
            if (!preg_match("/5\.1\../", phpversion())) {
              $this->assertTrue(in_array("phpunit.x$i", $list));
            }
        }
    }
    
    public function testCreateDBRef() {
        $ref = $this->object->createDBRef('foo.bar', array('foo' => 'bar'));
        $this->assertEquals($ref, NULL);

        $arr = array('_id' => new MongoId());
        $ref = $this->object->createDBRef('foo.bar', $arr);
        $this->assertNotNull($ref);
        $this->assertTrue(is_array($ref));

        $arr = array('_id' => 1);
        $ref = $this->object->createDBRef('foo.bar', $arr);
        $this->assertNotNull($ref);
        $this->assertTrue(is_array($ref));

        $ref = $this->object->createDBRef('foo.bar', new MongoId());
        $this->assertNotNull($ref);
        $this->assertTrue(is_array($ref));

        $id = new MongoId();
        $ref = $this->object->createDBRef('foo.bar', array('_id' => $id, 'y' => 3));
        $this->assertNotNull($ref);
        $this->assertEquals((string)$id, (string)$ref['$id']);
    }

    public function testGetDBRef() {
        $c = $this->object->selectCollection('foo');
        $c->drop();
        for($i=0;$i<50;$i++) {
            $c->insert(array('x' => rand()));
        }
        $obj = $c->findOne();

        $ref = $this->object->createDBRef('foo', $obj);
        $obj2 = $this->object->getDBRef($ref);

        $this->assertNotNull($obj2);
        $this->assertEquals($obj['x'], $obj2['x']);
    }

    public function testExecute() {
        $ret = $this->object->execute('4+3*6');
        $this->assertEquals($ret['retval'], 22, json_encode($ret));

        $ret = $this->object->execute(new MongoCode('function() { return x+y; }', array('x' => 'hi', 'y' => 'bye')));
        $this->assertEquals($ret['retval'], 'hibye', json_encode($ret));

        $ret = $this->object->execute(new MongoCode('function(x) { return x+y; }', array('y' => 'bye')), array('bye'));
        $this->assertEquals($ret['retval'], 'byebye', json_encode($ret));
    }

    public function testDBCommand() {
        $x = $this->object->command(array());
        $this->assertEquals(0, strpos($x['errmsg'], "no such cmd"), json_encode($x));
        $this->assertEquals((bool)$x['ok'], false);

        $created = $this->object->createCollection("system.profile", true, 5000);

        $this->object->command(array('profile' => 0));
        $x = $this->object->command(array('profile' => 1));
        $this->assertEquals($x['was'], 0, json_encode($x));
        $this->assertEquals((bool)$x['ok'], true, json_encode($x));
    }

    public function testCreateRef() {
        $ref = MongoDBRef::create("x", "y");
        $this->assertEquals('x', $ref['$ref']);
        $this->assertEquals('y', $ref['$id']);
    }

    public function testIsRef() {
        $this->assertFalse(MongoDBRef::isRef(array()));
        $this->assertFalse(MongoDBRef::isRef(array('$ns' => 'foo', '$id' => 'bar')));
        $ref = $this->object->createDBRef('foo.bar', array('foo' => 'bar'));
        $this->assertEquals(NULL, $ref);

        $ref = array('$ref' => 'blog.posts', '$id' => new MongoId('cb37544b9dc71e4ac3116c00'));
        $this->assertTrue(MongoDBRef::isRef($ref));
    }

    public function testLastError() {
        $this->object->resetError();
        $err = $this->object->lastError();
        $this->assertEquals(null, $err['err'], json_encode($err));
        $this->assertEquals(0, $err['n'], json_encode($err));
        $this->assertEquals(true, (bool)$err['ok'], json_encode($err));

        $this->object->forceError();
        $err = $this->object->lastError();
        $this->assertNotNull($err['err']);
        $this->assertEquals($err['n'], 0);
        $this->assertEquals((bool)$err['ok'], true);
    }

    public function testResetError() {
        $this->object->resetError();
        $err = $this->object->lastError();
        $this->assertEquals($err['err'], null);
        $this->assertEquals($err['n'], 0);
        $this->assertEquals((bool)$err['ok'], true);
    }

    public function testForceError() {
        $this->object->forceError();
        $err = $this->object->lastError();
        $this->assertNotNull($err['err']);
        $this->assertEquals($err['n'], 0);
        $this->assertEquals((bool)$err['ok'], true);
    }

    public function testW() {
      $this->assertEquals(1, $this->object->w);
      $this->assertEquals(10000, $this->object->wtimeout);

      $this->object->w = 4;
      $this->object->wtimeout = 60;
 
      $this->assertEquals(4, $this->object->w);
      $this->assertEquals(60, $this->object->wtimeout);
   }

    public function testCommandTimeout() {
        $this->object->command(array('serverStatus' => 1), array('timeout' => -1));
    }
}
?>
