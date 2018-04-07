<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

class ClientTest extends PHPUnit_Framework_TestCase
{
    public function testClientEndpoint()
    {
        $client = new RevolutPHP\Client('foo');

        $this->assertEquals(
            $client::REVOLUT_PRODUCTION_ENDPOINT,
            PHPUnit_Framework_Assert::readAttribute($client, "baseUrl")
        );

        $client = new RevolutPHP\Client('foo', 'sandbox');

        $this->assertEquals(
            $client::REVOLUT_SANDBOX_ENDPOINT,
            PHPUnit_Framework_Assert::readAttribute($client, "baseUrl")
        );

    }

    public function testHeaders()
    {
        $mock = new MockHandler([new Response(200, ['X-Foo' => 'Bar'], "{\"foo\":\"bar\"}")]);
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $http_client = new Client(['handler' => $stack]);
        $client = new RevolutPHP\Client('foo' , 'production');
        $client->setClient($http_client);
        $client->accounts->all();
        foreach ($container as $transaction) {
           $this->assertEquals('GET', $transaction['request']->getMethod());
           $this->assertEquals('b2b.revolut.com', $transaction['request']->getHeaders()['Host'][0]);
        }
    }
}