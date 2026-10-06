<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private function court(): Court
    {
        return Court::create(['name' => 'Arena Test', 'type' => 'Futsal', 'price_per_hour' => 150000, 'description' => 'Lapangan indoor']);
    }

    private function data(Court $court, array $overrides = []): array
    {
        return array_replace(['court_id' => $court->id, 'customer_name' => 'Budi', 'customer_phone' => '081234567890', 'booking_date' => today()->addDay()->format('Y-m-d'), 'start_time' => '10:00', 'duration_hours' => 2], $overrides);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        return $admin;
    }

    private function image(string $name): UploadedFile
    {
        // A real PNG fixture keeps upload coverage independent of PHP's optional GD extension.
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWo0AAAAASUVORK5CYII='));
    }

    public function test_public_catalog_and_server_calculated_booking(): void
    {
        $court = $this->court();
        $this->get('/')->assertOk()->assertSee('Arena Test')->assertSee('Konfirmasi reservasi');
        $this->post('/checkout', $this->data($court, ['total_price' => 1, 'status' => 'Lunas']))->assertSessionHasNoErrors()->assertSessionHas('receipt');
        $this->assertDatabaseHas('bookings', ['total_price' => 300000, 'status' => 'Pending']);
        $this->assertDatabaseHas('booking_details', ['court_id' => $court->id, 'duration_hours' => 2, 'subtotal' => 300000]);
        $this->get('/')->assertOk()->assertSee('RESERVASI #');
    }

    public function test_overlap_rejected_but_adjacent_and_other_dates_allowed(): void
    {
        $court = $this->court();
        $this->post('/checkout', $this->data($court))->assertSessionHasNoErrors();
        $this->post('/checkout', $this->data($court, ['start_time' => '11:00']))->assertSessionHasErrors('schedule');
        $this->assertDatabaseCount('bookings', 1);
        $this->post('/checkout', $this->data($court, ['start_time' => '12:00']))->assertSessionHasNoErrors();
        $this->post('/checkout', $this->data($court, ['booking_date' => today()->addDays(2)->format('Y-m-d')]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('bookings', 3);
    }

    public function test_cancellation_releases_slot_and_conflicting_reactivation_is_rejected(): void
    {
        $court = $this->court();
        $this->post('/checkout', $this->data($court));
        $first = Booking::first();
        $this->actingAs($this->admin());
        $this->patch(route('admin.bookings.status', $first), ['status' => 'Batal'])->assertSessionHasNoErrors();
        $this->post('/checkout', $this->data($court))->assertSessionHasNoErrors();
        $this->patch(route('admin.bookings.status', $first), ['status' => 'Lunas'])->assertSessionHasErrors('schedule');
        $this->assertSame('Batal', $first->fresh()->status);
    }

    public function test_invalid_hours_dates_and_duration_do_not_create_bookings(): void
    {
        $court = $this->court();
        foreach ([['start_time' => '21:00', 'duration_hours' => 2], ['start_time' => '10:30'], ['duration_hours' => 0], ['booking_date' => today()->subDay()->format('Y-m-d')], ['customer_phone' => 'abc']] as $invalid) {
            $this->post('/checkout', $this->data($court, $invalid))->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_admin_routes_require_admin_and_status_is_validated(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/dashboard')->assertOk();
        $court = $this->court();
        $this->post('/checkout', $this->data($court));
        $booking = Booking::first();
        $this->patch(route('admin.bookings.status', $booking), ['status' => 'invalid'])->assertSessionHasErrors('status');
        $this->patch(route('admin.bookings.status', $booking), ['status' => 'Lunas'])->assertSessionHasNoErrors();
        $this->get('/admin/dashboard')->assertOk()->assertSee('Budi')->assertSee('Lunas');
    }

    public function test_admin_login_and_logout(): void
    {
        $admin = $this->admin();
        $this->get('/login')->assertOk();
        $this->post('/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_court_crud_upload_and_history_protection(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $this->get('/admin/courts/create')->assertOk();
        $payload = ['name' => 'New Court', 'type' => 'Basket', 'price_per_hour' => 100000, 'description' => 'Test court'];
        $this->post('/admin/courts', [...$payload, 'image' => $this->image('court.png')])->assertSessionHasNoErrors();
        $court = Court::first();
        Storage::disk('public')->assertExists($court->image);
        $oldImage = $court->image;
        $this->get(route('courts.edit', $court))->assertOk();
        $this->put(route('courts.update', $court), [...$payload, 'name' => 'Updated Court', 'image' => $this->image('new.png')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($oldImage);
        $this->get('/admin/courts')->assertOk()->assertSee('Updated Court');
        $this->post('/checkout', $this->data($court));
        $this->delete(route('courts.destroy', $court))->assertSessionHasErrors('court');
        $this->assertDatabaseHas('courts', ['id' => $court->id]);
        $unused = $this->court();
        $this->delete(route('courts.destroy', $unused))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('courts', ['id' => $unused->id]);
    }
}
