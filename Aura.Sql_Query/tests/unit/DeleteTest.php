<?php
namespace Aura\Sql_Query;

class DeleteTest extends AbstractQueryTest
{
    protected $query_type = 'Delete';
    
    public function test()
    {
        $this->query->from('t1')
                    ->where('foo = ?', 'bar')
                    ->where('baz = ?', 'dib')
                    ->orWhere('zim = gir');
                    
        $actual = $this->query->__toString();
        $expect = "
            DELETE FROM \"t1\"
            WHERE
                foo = 'bar'
                AND baz = 'dib'
                OR zim = gir
        ";
        
        $this->assertSameSql($expect, $actual);
    }
}
