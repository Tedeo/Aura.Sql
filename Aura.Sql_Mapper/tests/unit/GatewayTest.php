<?php
namespace Aura\Sql_Mapper;

use Aura\Sql\ConnectionFactory;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\Setup\SqliteSetup;
use Aura\Sql_Query\QueryFactory;

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Gateway
     */
    protected $gateway;

    protected $mapper;
    
    protected $connections;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->mapper = new MockMapper;
        
        $setup = new SqliteSetup;
        $connection = $setup->exec(
            $this->mapper->getTable(),
            'aura_test_schema1',
            'aura_test_schema2'
        );
        
        $this->connections = new ConnectionLocator(function () use ($connection) {
            return $connection;
        });
        
        $this->gateway = new Gateway(
            $this->connections,
            new QueryFactory,
            $this->mapper
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testGetConnections()
    {
        $actual = $this->gateway->getConnections();
        $this->assertSame($this->connections, $actual);
    }

    public function testGetMapper()
    {
        $actual = $this->gateway->getMapper();
        $this->assertSame($this->mapper, $actual);
    }

    // when mapping, add an "if isset()" so that the object does not need
    // all the columns?
    public function testInsertAndLastInsertId()
    {
        $object = (object) [
            'identity' => null,
            'firstName' => 'Laura',
            'sizeScale' => 10,
            'defaultNull' => null,
            'defaultString' => null,
            'defaultNumber' => null,
            'defaultIgnore' => null,
        ];
        
        // do the insert and retain last insert id
        $last_insert_id = $this->gateway->insert($object);
        
        // did we get the right last ID?
        $expect = '11';
        $this->assertEquals($expect, $last_insert_id);
        
        // did it insert?
        $select = $this->gateway->select(['id', 'name'])->where('id = ?', 11);
        $actual = $select->fetchOne($select);
        $expect = ['identity' => '11', 'firstName' => 'Laura'];
        $this->assertEquals($actual, $expect);
    }
    
    protected function fetchLastInsertId()
    {
        return $this->connections->getWrite()->lastInsertId();
    }
    
    public function testUpdate()
    {
        // select an object ...
        $select = $this->gateway->select()->where('name = ?', 'Anna');
        $object = (object) $select->fetchOne($select);
        
        // ... then modify and update it.
        $object->firstName = 'Annabelle';
        $this->gateway->update($object);
        
        // did it update?
        $select = $this->gateway->select()->where('name = ?', 'Annabelle');
        $actual = (object) $select->fetchOne($select);
        $this->assertEquals($actual, $object);
        
        // did anything else update?
        $select = $this->gateway->select(['id', 'name'])->where('id = ?', 2);
        $actual = $select->fetchOne($select);
        $expect = ['identity' => '2', 'firstName' => 'Betty'];
        $this->assertEquals($actual, $expect);
    }
    
    public function testDelete()
    {
        // select an object ...
        $select = $this->gateway->select()->where('name = ?', 'Anna');
        $object = (object) $select->fetchOne($select);
        
        // then delete it.
        $this->gateway->delete($object);
        
        // did it delete?
        $select = $this->gateway->select()->where('name = ?', 'Anna');
        $actual = $select->fetchOne($select);
        $this->assertFalse($actual);
        
        // do we still have everything else?
        $select = $this->gateway->select();
        $actual = $select->fetchAll($select);
        $expect = 9;
        $this->assertEquals($expect, count($actual));
    }

    public function testNewSelect()
    {
        $select = $this->gateway->select();
        $connection = $select->getConnection();
        $this->assertSame($this->connections->getRead(), $connection);
        $expect = '
            SELECT
                "aura_test_table"."id" AS "identity",
                "aura_test_table"."name" AS "firstName",
                "aura_test_table"."test_size_scale" AS "sizeScale",
                "aura_test_table"."test_default_null" AS "defaultNull",
                "aura_test_table"."test_default_string" AS "defaultString",
                "aura_test_table"."test_default_number" AS "defaultNumber",
                "aura_test_table"."test_default_ignore" AS "defaultIgnore"
            FROM
                "aura_test_table"
        ';
        $actual = (string) $select;
        $this->assertSameSql($expect, $actual);
    }

    public function testFetchOneBy()
    {
        $actual = $this->gateway->fetchOneBy('id', 1);
        unset($actual['defaultIgnore']); // creation date-time
        $expect = [
            'identity' => '1',
            'firstName' => 'Anna',
            'sizeScale' => null,
            'defaultNull' => null,
            'defaultString' => 'string',
            'defaultNumber' => '12345',
        ];
        $this->assertEquals($expect, $actual);
    }
    
    public function testFetchAllBy()
    {
        $actual = $this->gateway->fetchAllBy('id', [1]);
        unset($actual[0]['defaultIgnore']); // creation date-time
        $expect = [
            [
                'identity' => '1',
                'firstName' => 'Anna',
                'sizeScale' => null,
                'defaultNull' => null,
                'defaultString' => 'string',
                'defaultNumber' => '12345',
            ],
        ];
        $this->assertEquals($expect, $actual);
    }
    
    protected function assertSameSql($expect, $actual)
    {
        $expect = trim($expect);
        $expect = preg_replace('/^\s*/m', '', $expect);
        $expect = preg_replace('/\s*$/m', '', $expect);
        
        $actual = trim($actual);
        $actual = preg_replace('/^\s*/m', '', $actual);
        $actual = preg_replace('/\s*$/m', '', $actual);
        
        $this->assertSame($expect, $actual);
    }
}
