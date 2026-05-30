<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_users_but_cannot_manage_them(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
        ]);

        $record = User::factory()->create([
            'role' => User::ROLE_STAFF,
        ]);

        $this->assertTrue(Gate::forUser($staff)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($staff)->allows('view', $record));
        $this->assertFalse(Gate::forUser($staff)->allows('create', User::class));
        $this->assertFalse(Gate::forUser($staff)->allows('update', $record));
        $this->assertFalse(Gate::forUser($staff)->allows('delete', $record));
        $this->assertFalse(Gate::forUser($staff)->allows('deleteAny', User::class));
    }

    public function test_owners_can_manage_users(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
        ]);

        $record = User::factory()->create([
            'role' => User::ROLE_STAFF,
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($owner)->allows('create', User::class));
        $this->assertTrue(Gate::forUser($owner)->allows('update', $record));
        $this->assertTrue(Gate::forUser($owner)->allows('delete', $record));
        $this->assertTrue(Gate::forUser($owner)->allows('deleteAny', User::class));
    }
}
