<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetCodeMail;
use App\Mail\VerifyEmailMail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_verification()
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/register', [
            'nombre' => 'Juan',
            'apellido' => 'Perez',
            'email' => 'juan@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
        $user = Usuario::where('email', 'juan@example.com')->first();
        $this->assertNotNull($user->email_verification_token);

        Mail::assertSent(VerifyEmailMail::class, function ($mail) use ($user) {
            return $mail->usuario->id_usuario === $user->id_usuario;
        });
    }

    public function test_can_request_and_reset_password_with_code()
    {
        Mail::fake();

        $user = Usuario::create([
            'nombre' => 'Prueba',
            'apellido' => 'Usuario',
            'email' => 'test@example.com',
            'password' => Hash::make('oldpass'),
            'rol' => 'residente',
            'activo' => true,
        ]);

        $this->postJson('/api/auth/request-password-reset', ['email' => 'test@example.com'])
            ->assertStatus(200)->assertJson(['success' => true]);

        $user->refresh();
        $this->assertNotNull($user->password_reset_code);
        Mail::assertSent(PasswordResetCodeMail::class);

        $code = $user->password_reset_code;
        $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'code' => $code,
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertStatus(200)->assertJson(['success' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpass123', $user->password));
        $this->assertNull($user->password_reset_code);
    }
}