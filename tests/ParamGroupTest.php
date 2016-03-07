<?php

namespace Thapp\Jmg\Tests;

use Thapp\Jmg\ParamGroup;
use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;

class ParamGroupTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceOf('Thapp\Jmg\ParamGroup', new ParamGroup);
    }

    /** @test */
    public function itShouldTranspileFromString()
    {
        $group = ParamGroup::fromString('1/600/0:gray;c=1;v=1|2/400/400/5', '/', '');

        $this->assertSame(2, count($group->all()));

        $group = $group->all();

        $this->assertInstanceOf('Thapp\Jmg\Parameters', $group[0][0]);
        $this->assertInstanceOf('Thapp\Jmg\FilterExpression', $group[0][1]);
        $this->assertInstanceOf('Thapp\Jmg\Parameters', $group[1][0]);
        $this->assertNull($group[1][1]);
    }

    /** @test */
    public function itShouldCreateGroupFromQueryParams()
    {
        $q = ['jmg' => '2:200:200:5'];
        $params = ParamGroup::fromQuery($q);

        $all = $params->all();

        $this->assertSame('2/200/200/5', (string)$all[0][0]);

        $q = ['jmg' => ['2:200:200:5:filter:circle', '1:100:0']];
        $params = ParamGroup::fromQuery($q);

        $all = $params->all();

        $this->assertSame('2/200/200/5', (string)$all[0][0]);
        $this->assertSame('circle', (string)$all[0][1]);
        $this->assertSame('1/100/0', (string)$all[1][0]);
    }

    /** @test */
    public function itShouldTranspileToQuery()
    {
        $group = ParamGroup::fromString('1/600/0:gray;c=1;v=1|2/400/400/5', '/', '');

        $this->assertSame(
            'jmg[0]=1:600:0:gray;c=1;v=1&jmg[1]=2:400:400:5',
            urldecode($group->toQueryString())
        );

        $group = ParamGroup::fromString('1/600/0:filter:gray;c=1;v=1|2/400/400/5');

        $this->assertSame(
            'jmg[0]=1:600:0:filter:gray;c=1;v=1&jmg[1]=2:400:400:5',
            urldecode($group->toQueryString())
        );
    }
}
