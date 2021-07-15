<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    /*
    public function testCreateBook(){
        $client = static ::createClient();
        $client->request('POST', '/api/books');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }
    */
    public function testCreateBookWithInvalidData(){
        $client = static ::createClient();
        $client->request(
            'POST',
            '/api/books',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":""}'
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }


    public function testCreateBookWithEmptyData(){
        $client = static ::createClient();
        $client->request(
            'POST',
            '/api/books',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            ''
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testSuccess(){
        $client = static ::createClient();
        $client->request(
            'POST',
            '/api/books',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"El imperio"}'
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}