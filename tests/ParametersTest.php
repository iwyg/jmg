<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests;

use Thapp\Jmg\Parameters;

/**
 * @class ParametersTest
 * @see \PHPUnit_Framework_TestCase
 *
 * @package Thapp\Jmg\Tests
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ParametersTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\Parameters', new Parameters);
    }

    /**
     * @test
     * @dataProvider queryParamProvider
     */
    public function itShouldCreateParamsFromQueryParams($q, $expected)
    {
        $params = Parameters::fromQuery($q);
        $this->assertSame($expected, (string)$params);
    }

    /** @test */
    public function itShouldParseQueryParamsToInt()
    {
        $q = ['mode' => '2', 'width' => '220', 'height' => '220', 'gravity' => '5'];

        $params = Parameters::fromQuery($q)->all();
        $this->assertSame(2, $params['mode']);
        $this->assertSame(5, $params['gravity']);
        $this->assertSame(220, $params['width']);
        $this->assertSame(220, $params['height']);
    }

    /**
     * @test
     * @dataProvider paramStringProvider
     */
    public function itShouldParseParamString($str, $expected)
    {
        $params = Parameters::fromString($str);

        $this->assertEquals($expected, $params->all());
    }

    /** @test */
    public function itShouldParseStringFromQueryChain()
    {
        $q = [
            'jmg' => [
                '1:500:0:filter:foo;o=2:bar;c=fff',
                '2:200:200:5'
            ]
        ];

        $params = Parameters::fromChainedQuery($q);

        $this->assertSame(2, count($params));
        $this->assertInstanceOf('Thapp\Jmg\Parameters', $params[0][0]);
        $this->assertInstanceOf('Thapp\Jmg\Parameters', $params[1][0]);
        $this->assertInstanceOf('Thapp\Jmg\FilterExpression', $params[0][1]);
        $this->assertInstanceOf('Thapp\Jmg\FilterExpression', $params[1][1]);
    }

    public function paramStringProvider()
    {
        return [
            [
                '2/300/300/5',
                ['mode' => 2, 'width' => 300, 'height' => 300, 'gravity' => 5, 'background' => null]
            ],
            [
                '0/400/499/4/19',
                ['mode' => 0, 'width' => null, 'height' => null, 'gravity' => null, 'background' => null]
            ],
            [
                '3/300/300/5/fff',
                ['mode' => 3, 'width' => 300, 'height' => 300, 'gravity' => 5, 'background' => hexdec('ffffff')]
            ],
            [
                '3/300/300/5/#fe0',
                ['mode' => 3, 'width' => 300, 'height' => 300, 'gravity' => 5, 'background' => hexdec('ffee00')]
            ],
        ];
    }

    /** @test */
    public function itShouldBeConvertibleToString()
    {
        $params = new Parameters;
        $params->setMode(2);
        $params->setTargetSize(200, 200);

        $this->assertSame('2/200/200/5', $params->asString());
        $this->assertSame('2/200/200/5', (string)$params);
    }

    /** @test */
    public function itShouldBeEmptyWhenCloned()
    {
        $params = new Parameters;
        $params->setMode(2);
        $params->setTargetSize(200, 200);

        $clone = clone($params);

        $this->assertSame([
            'mode'       => 0,
            'width'      => null,
            'height'     => null,
            'gravity'    => null,
            'background' => null,
        ], $clone->all());
    }

    /** @test */
    public function itShouldSetAndSanitizeParams()
    {
        $params = new Parameters;

        $params->setMode(0);
        $params->setTargetSize(200, 400);

        $this->assertSame([
            'mode'       => 0,
            'width'      => null,
            'height'     => null,
            'gravity'    => null,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(1);
        $params->setTargetSize(200, 400);

        $this->assertSame([
            'mode'       => 1,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => null,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(2);
        $params->setTargetSize(200, 400);
        $params->setGravity(2);
        $params->setBackground('fff');

        $this->assertSame([
            'mode'       => 2,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => 2,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(2);
        $params->setTargetSize(200, 400);

        $this->assertSame([
            'mode'       => 2,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => 5,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(3);
        $params->setTargetSize(200, 400);
        $params->setBackground('abcdf');

        $this->assertSame([
            'mode'       => 3,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => 5,
            'background' => null,
            ], $params->all());

        $params = new Parameters;

        $params->setMode(3);
        $params->setTargetSize(200, 400);
        $params->setBackground('fff');

        $this->assertSame([
            'mode'       => 3,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => 5,
            'background' => hexdec('ffffff'),
        ], $params->all());

        $params = new Parameters;

        $params->setMode(4);
        $params->setTargetSize(400, 400);

        $this->assertSame([
            'mode'       => 4,
            'width'      => 400,
            'height'     => 400,
            'gravity'    => null,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(5);
        $params->setTargetSize(400, 400);

        $this->assertSame([
            'mode'       => 5,
            'width'      => (float)400,
            'height'     => null,
            'gravity'    => null,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(6);
        $params->setTargetSize(400, 400);

        $this->assertSame([
            'mode'       => 6,
            'width'      => 400,
            'height'     => null,
            'gravity'    => null,
            'background' => null,
        ], $params->all());
    }

    public function queryParamProvider()
    {
        return [
            [
                [
                    'mode' => '0',
                    'height' => '200'
                ],
                '0'
            ],
            [
                [
                    'mode' => '1',
                    'width' => '200',
                    'height' => '400'
                ],
                '1/200/400'
            ],
            [
                [
                    'mode' => '1',
                    'width' => '200',
                ],
                '1/200/0'
            ],
            [
                [
                    'mode' => '1',
                    'height' => '200',
                ],
                '1/0/200'
            ],
            [
                [
                    'mode' => '2',
                    'height' => '200',
                    'width' => '400'
                ],
                '2/400/200/5'
            ],
            [
                [
                    'mode' => '2',
                    'width' => '200',
                    'height' => '400',
                    'gravity' => '2'
                ],
                '2/200/400/2'
            ],
            [
                [
                    'mode' => '3',
                    'width' => '200',
                    'height' => '400',
                    'gravity' => '2'
                ],
                '3/200/400/2'
            ],
            [
                [
                    'mode' => '3',
                    'width' => '200',
                    'height' => '400'
                ],
                '3/200/400/5'
            ],
            [
                [
                    'mode' => '3',
                    'width' => '200',
                    'height' => '400',
                    'background' => $c = 'f7f7f7'
                ],
                '3/200/400/5/' . hexdec($c)
            ],
            [
                [
                    'mode' => '4',
                    'width' => '200',
                    'height' => '200',
                    'background' => 'f7f7f7'
                ],
                '4/200/200'
            ],
            [
                [
                    'mode' => '5',
                    'width' => '200',
                    'height' => '400',
                ],
                '5/200'
            ],
            [
                [
                    'mode' => '6',
                    'width' => '200',
                    'height' => '400',
                ],
                '6/200'
            ]
        ];
    }
}
