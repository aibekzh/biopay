<?php

namespace Unit;

use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseTransactions;
use TestCase;


class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;
    protected $user;
    public    $data;
    public    $data_for_access;
    public    $data_for_refresh;
    public    $token;
    private   $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker    = Factory::create();
        $this->user     = User::factory()->create();
        $this->data     = [
                              'username'     => $this->user->email,
                              'password'     => 'secret_secret',
                          ];
        $this->post('/api/login', $this->data);
        $this->token = $this->response->original;
        $this->data_for_access = [
            'access_token' => $this->token['data']['access_token'],
        ];
        $this->data_for_refresh = [
            'refresh_token' => $this->token['data']['refresh_token'],
        ];
    }

    public function test_can_register() {
        $user = [
            'name'                  => $this->faker->name,
            'email'                 => $this->faker->unique()->safeEmail,
            'password'              => 'secret_secret',
            'password_confirmation' => 'secret_secret',
        ];

        $this->post('/api/register', $user);
        $this->seeStatusCode(201);
        $this->seeJsonStructure(['success','message']);
    }

    public function test_can_login() {
        $this->post('/api/login', $this->data);
        $this->seeStatusCode(200);
        $this->seeJsonStructure(['success',
            'data'=>[
                'token_type',
                'expires_in',
                'access_token',
                'refresh_token',
            ]]);
    }

    public function test_can_check() {
        $this->get('/api/check',['Authorization' => 'Bearer ' . $this->data_for_access['access_token']]);
        $this->seeStatusCode(200);
        $this->seeJsonStructure(['success',
            'data'=>[
                'id',
                'name',
                'email',
                'email_verified_at',
                'balance',
                'remember_token',
                'created_at',
                'updated_at',
            ]]);
    }

    public function test_can_refresh_token() {
        $this->post('/api/refresh', $this->data_for_refresh);
        $this->seeStatusCode(200);
        $this->seeJsonStructure(['success',
            'data'=>[
                'token_type',
                'expires_in',
                'access_token',
                'refresh_token',
            ]]);
    }

    public function test_can_send_verify_message() {
        $this->get('api/email/resend', ['Authorization' => 'Bearer ' . $this->data_for_access['access_token']]);
        $this->seeStatusCode(200);
        $this->seeJsonStructure(['success','message']);
    }

    public function test_can_send_reset_message() {
        $this->post('api/password/email', ['email' => $this->data['username']]);
        $this->seeStatusCode(200);
        $this->seeJsonStructure(['success','message']);
    }

    public function test_can_logout() {
        $this->get('api/logout', ['Authorization' => 'Bearer ' . $this->data_for_access['access_token']]);
        $this->seeStatusCode(200);
        $this->seeJsonStructure(['success','message']);
    }
}
