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
    public function itShouldSignUrl()
    {
        $signer = new UrlSigner('my-key', 's');

        $signature = $signer->sign('/image/0/cat.jpg', $this->mockParameters());

        $this->assertTrue(0 === strpos($signature, '/image/0/cat.jpg?s='));
    }

    /** @test */
    public function itShouldTrowOnInvalidToken()
    {
        $signer = new UrlSigner('my-key', 's');
        $uri = '/image/0/cat.jpg';
        $params = $this->mockParameters();

        try {
            $signer->validate($uri, $params);
        } catch (InvalidSignatureException $e) {
            $this->assertSame('Signature is missing.', $e->getMessage());
        }

        try {
            $signer->validate($uri.'?s=invaludtoken', $params);
        } catch (InvalidSignatureException $e) {
            $this->assertSame('Signature is invalid.', $e->getMessage());
        }
    }

    /** @test */
    public function itShouldSignAndValidate()
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

    /**
     * mockParameters
     *
     * @param string $str
     *
     * @return Thapp\Jmg\Parameters;
     */
    protected function mockParameters($str = '0')
    {
        $mock = $this->getMockBuilder('Thapp\Jmg\Parameters')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('asString')->willReturn($str);

        return $mock;
    }
}
