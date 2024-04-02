<?php

namespace App\Admin\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Platform\Enums\RoleName;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_all_users()
    {
        $this->withoutExceptionHandling();

        $memberUser = User::factory()->create([
            'email' => 'user@learnhub.com',
        ]);
        $memberUser->assignRole(RoleName::ADMIN);
        $memberUser->assignRole(RoleName::MEMBER);


        $adminUser = User::factory()->create();
        $adminUser->assignRole(RoleName::ADMIN);

        Sanctum::actingAs($adminUser);

        $response = $this->getJson('/admin/users')->json();

        $this->assertCount(2, $response['data']);
        $this->assertSame(2, $response['meta']['total']);

        $this->assertSame('user@learnhub.com', $response['data'][0]['email']);
        $this->assertSame(['admin', 'member'], $response['data'][0]['roles']);
    }

    #[Test]
    public function it_can_show_single_user()
    {
        $adminUser = User::factory()->create([
            'email' => 'user@learnhub.com',
        ]);
        $adminUser->assignRole(RoleName::ADMIN);
        $adminUser->assignRole(RoleName::MEMBER);

        Sanctum::actingAs($adminUser);

        $response = $this->getJson('/admin/users/' . $adminUser->id);

        $response->assertExactJson([
            'data' => [
                'id' => $adminUser->id,
                'email' => $adminUser->email,
                'roles' => ['admin', 'member'],
            ]
        ]);
    }

    #[Test]
    public function it_can_not_access_admin_routes_if_not_signed_in_as_admin()
    {
        // Trying to access the route as unauthenticated user
        $this->getJson('/admin/users')->assertUnauthorized();

        // Trying to access the route without any roles
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/admin/users')->assertForbidden();

        // Trying to access the route as a member
        $member = User::factory()->create();
        $member->assignRole(RoleName::MEMBER);
        Sanctum::actingAs($member);
        $this->getJson('/admin/users')->assertForbidden();

        // Trying to access the route as an admin
        $admin = User::factory()->create();
        $admin->assignRole(RoleName::ADMIN);
        Sanctum::actingAs($admin);
        $this->getJson('/admin/users')->assertOk();
    }
}
