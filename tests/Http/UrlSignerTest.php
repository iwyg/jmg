<?php

namespace Thapp\Jmg\Tests\Http;

use Thapp\Jmg\ParamGroup;
use Thapp\Jmg\Http\UrlSigner;
use Thapp\Jmg\Http\UrlBuilder;
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
        $params = ParamGroup::fromString('2/100/100/5/filter:circle;o=12;c=#f00');

        $signer = new UrlSigner('secretkey', 'token');
        $signed = $signer->sign($uri = $url->asQuery('image.jpg', 'images', $params), $params);

        $parts = parse_url($signed);

        $this->assertArrayHasKey('query', $parts);

        parse_str($parts['query'], $res);

        $this->assertArrayHasKey('token', $res);

        try {
            $this->assertTrue($signer->validate($signed, $params));
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
        $mock = $this->getMockBuilder('Thapp\Jmg\ParamGroup')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('__toString')->willReturn($str);

        return $mock;
    }
}
