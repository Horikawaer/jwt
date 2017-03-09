<?php
/**
 * This file is part of Lcobucci\JWT, a simple library to handle JWT and JWS
 *
 * @license http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace Lcobucci\JWT\Test;

use Lcobucci\JWT\Parsing\Encoder;
use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Token;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 * @since 0.1.0
 *
 * @coversDefaultClass Lcobucci\JWT\Token
 */
class TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Encoder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $encoder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->encoder = $this->getMockBuilder(Encoder::class)
                              ->setMockClassName('EncoderMock')
                              ->getMock();
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function constructMustInitializeAnEmptyPlainTextTokenWhenNoArgumentsArePassed()
    {
        $token = new Token();

        $this->assertAttributeEquals(['alg' => 'none'], 'header', $token);
        $this->assertAttributeEquals([], 'claims', $token);
        $this->assertAttributeEquals(null, 'signature', $token);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::setEncoder
     */
    public function setEncoderMustConfigureTheEncoderAttribute()
    {
        $token = new Token();
        $token->setEncoder($this->encoder);

        $this->assertAttributeSame($this->encoder, 'encoder', $token);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getHeader
     */
    public function getHeaderMustReturnTheConfiguredHeader()
    {
        $token = new Token(['test' => 'testing']);

        $this->assertEquals(['test' => 'testing'], $token->getHeader());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getClaims
     */
    public function getClaimsMustReturnTheConfiguredClaims()
    {
        $token = new Token([], ['test' => 'testing']);

        $this->assertEquals(['test' => 'testing'], $token->getClaims());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getSignature
     */
    public function getSignatureMustReturnTheConfiguredSignature()
    {
        $signature = $this->getMockBuilder(Signature::class)
                          ->setMockClassName('SignatureMock')
                          ->disableOriginalConstructor()
                          ->getMock();

        $token = new Token([], [], $signature);

        $this->assertSame($signature, $token->getSignature());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::verify
     *
     * @expectedException BadMethodCallException
     */
    public function verifyMustRaiseExceptionWhenTokenIsUnsigned()
    {
        $token = new Token();
        $token->verify('test');
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::setEncoder
     * @covers ::getPayload
     * @covers ::verify
     */
    public function verifyMustDelegateTheValidationToSignature()
    {
        $signature = $this->getMockBuilder(Signature::class)
                          ->setMockClassName('SignatureMock')
                          ->disableOriginalConstructor()
                          ->getMock();

        $signature->expects($this->once())
                  ->method('verify')
                  ->willReturn(true);

        $token = new Token([], [], $signature);
        $token->setEncoder($this->encoder);

        $this->assertTrue($token->verify('test'));
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getPayload
     * @covers ::__toString
     */
    public function toStringMustReturnAnEmptyStringWhenEncoderIsNotDefined()
    {
        $token = new Token();

        $this->assertEmpty((string) $token);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::setEncoder
     * @covers ::getPayload
     * @covers ::__toString
     */
    public function toStringMustReturnEncodedDataWithEmptySignature()
    {
        $token = new Token();
        $token->setEncoder($this->encoder);

        $this->createMockExpectations();

        $this->assertEquals('test.test.', (string) $token);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::setEncoder
     * @covers ::getPayload
     * @covers ::__toString
     */
    public function toStringMustReturnEncodedData()
    {
        $signature = $this->getMockBuilder(Signature::class)
                          ->setMockClassName('SignatureMock')
                          ->disableOriginalConstructor()
                          ->getMock();

        $signature->expects($this->any())
                  ->method('__toString')
                  ->willReturn('test');

        $token = new Token(['alg' => 'none'], [], $signature);
        $token->setEncoder($this->encoder);

        $this->createMockExpectations('test');

        $this->assertEquals('test.test.test', (string) $token);
    }

    /**
     * Fill the mock expectations
     */
    protected function createMockExpectations($signature = null)
    {
        $this->encoder->expects($this->at(0))
                      ->method('jsonEncode')
                      ->with(['alg' => 'none'])
                      ->willReturn('test');

        $this->encoder->expects($this->at(1))
                      ->method('base64UrlEncode')
                      ->with('test')
                      ->willReturn('test');

        $this->encoder->expects($this->at(2))
                      ->method('jsonEncode')
                      ->with([])
                      ->willReturn('test');

        $this->encoder->expects($this->at(3))
                      ->method('base64UrlEncode')
                      ->with('test')
                      ->willReturn('test');

        if ($signature) {
            $this->encoder->expects($this->at(4))
                      ->method('base64UrlEncode')
                      ->with('test')
                      ->willReturn('test');
        }
    }
}
