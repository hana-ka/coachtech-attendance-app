<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    public function test_verification_notice_page_has_verification_link()
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        $response = $this->get(
            route('verification.notice')
        );

        $response->assertStatus(200);

        $response->assertSee('http://localhost:8025');

        $response->assertSee('認証はこちらから');
    }

    public function test_user_is_verified_and_redirected_to_attendance_page()
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect('/attendance');

        $this->assertNotNull(
            $user->fresh()->email_verified_at
        );
    }
}