<?php

namespace Thapp\Jmg\Tests\Http;

use Thapp\Jmg\Http\UrlSigner;
use Thapp\Jmg\Http\UrlBuilder;
use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;
use Thapp\Jmg\Exception\InvalidSignatureException;

class UrlSignerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldSignUrls()
    {
        $url = new UrlBuilder;
        $params = Parameters::fromString('2/100/100/5');
        $filters = new FilterExpression('circle;o=12;c=#f00');

        $signer = new UrlSigner('secretkey', 'token');
        $signed = $signer->sign($uri = $url->getUri('image.jpg', $params, $filters, 'images', true), $params, $filters);

        $parts = parse_url($signed);

        $this->assertArrayHasKey('query', $parts);

        parse_str($parts['query'], $res);

        $this->assertArrayHasKey('token', $res);

        try {
            $this->assertTrue($signer->validate($signed, $params, $filters));
        } catch (InvalidSignatureException $e) {
            $this->fail($e->getMessage());
        }
    }
}
