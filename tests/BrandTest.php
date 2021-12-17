<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class BrandTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     * @test
     */
    public function a_brand_can_be_create()
    {
        $this->post(
            '/',
            [
                "vehicle_type" => "C",
            ],
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODA4MFwvdjFcL2F1dGhcL2xvZ2luIiwiaWF0IjoxNjIxMzQxMDgzLCJleHAiOjE2MjIyMDUwODMsIm5iZiI6MTYyMTM0MTA4MywianRpIjoibUlVTGxmMUpMVVhGOGlmOSIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.5Cb8BEHGc0OgWhnykdyGrVRvw8Lr6Rcflg2COTn3New'
            ]
        );

        $this->assertEquals(
            $this->app->version(), $this->response->getContent()
        );
    }
}
