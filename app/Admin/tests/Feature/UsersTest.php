<?php

namespace App\Admin\Tests\Feature;

use App\Admin\Models\User;
use App\Platform\Enums\RoleName;
use App\Platform\Enums\UserStatusName;
use App\Platform\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_all_users(): void
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

    public static function userDataProvider(): array
    {
        return [
            'wrong_first_name' => ['first_name', 'blablabla', 0],
            'correct_first_name' => ['first_name', 'john', 1],
            'wrong_last_name' => ['last_name', 'blablabla', 0],
            'correct_last_name' => ['last_name', 'doe', 1],
            'wrong_role' => ['role', 'member', 0],
            'correct_role' => ['role', 'admin', 1],
        ];
    }

    #[Test]
    #[DataProvider('userDataProvider')]
    public function it_can_filter_users(string $field, string $value, int $count): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create([
            'email' => 'user@learnhub.com',
        ]);

        Profile::factory()->for($user)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $user->assignRole(RoleName::ADMIN);

        Sanctum::actingAs($user);

        $response = $this->getJson("/admin/users?{$field}={$value}")->json();
        $this->assertCount($count, $response['data']);
    }

    public static function userSortDataProvider(): array
    {
        return [
            // 'Can sort by id in asc dir' => ['id', 'asc', [1, 2, 3, 4]],
            // 'Can sort by id in desc dir' => ['id', 'desc', [8, 7, 6, 5]],
            'Can sort by first name in asc dir' => ['first_name', 'asc', ['a', 'b', 'c', 'd']],
            'Can sort by first name in desc dir' => ['first_name', 'desc', ['d', 'c', 'b', 'a']],
            'Can sort by last name in asc dir' => ['last_name', 'asc', ['a', 'b', 'c', 'd']],
            'Can sort by last name in desc dir' => ['last_name', 'desc', ['d', 'c', 'b', 'a']],
        ];
    }

    #[Test]
    #[DataProvider('userSortDataProvider')]
    public function it_can_sort_the_users(string $field, string $direction, array $dataset): void
    {
        $this->withoutExceptionHandling();

        collect([
            ['a', 'b'],
            ['c', 'c'],
            ['b', 'a'],
        ])->each(function ($user) {
            [$first, $last] = $user;

            Profile::factory()->create([
                'first_name' => $first,
                'last_name' => $last,
            ]);
        });

        $user = User::factory()->create([
            'email' => 'user@learnhub.com',
        ]);

        Profile::factory()->for($user)->create([
            'first_name' => 'd',
            'last_name' => 'd',
        ]);

        $user->assignRole(RoleName::ADMIN);

        Sanctum::actingAs($user);

        $response = $this->getJson("/admin/users?sortBy={$field}&sortDirection={$direction}")->json();

        collect($dataset)->each(function ($name, $index) use ($response, $field) {
            $this->assertSame($name, $response['data'][$index][$field]);
        });
    }

    #[Test]
    public function it_can_show_single_user(): void
    {
        $adminUser = User::factory()->create([
            'email' => 'user@learnhub.com',
        ]);
        $adminUser->assignRole(RoleName::ADMIN);
        $adminUser->assignRole(RoleName::MEMBER);

        Profile::factory()->for($adminUser)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Sanctum::actingAs($adminUser);

        $response = $this->getJson('/admin/users/' . $adminUser->id);

        $response->assertExactJson([
            'data' => [
                'id' => $adminUser->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => $adminUser->email,
                'roles' => ['admin', 'member'],
            ]
        ]);
    }

    #[Test]
    public function it_can_not_access_admin_routes_if_not_signed_in_as_admin(): void
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

    #[Test]
    public function an_admin_can_delete_a_member_from_the_system(): void
    {
        $member = User::factory()->create();
        $member->assignRole(RoleName::MEMBER);

        $admin = User::factory()->create();
        $admin->assignRole(RoleName::ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson(route('users.destroy', ['user' => $member->id]));

        $response->assertNoContent();

        $this->assertDatabaseHas($member->getTable(), [
            'id' => $member->id,
            'status' => UserStatusName::DELETED,
            'deleted_at' => now(),
        ]);
    }

    #[Test]
    public function a_member_cannot_delete_a_member_from_the_system(): void
    {
        $member = User::factory()->create();
        $member->assignRole(RoleName::MEMBER);

        $admin = User::factory()->create();
        $admin->assignRole(RoleName::ADMIN);

        Sanctum::actingAs($member);

        $response = $this->deleteJson(route('users.destroy', ['user' => $member->id]));

        $response->assertForbidden();

        $this->assertDatabaseHas($member->getTable(), [
            'id' => $member->id,
            'status' => UserStatusName::ACTIVE,
        ]);
    }

    #[Test]
    public function a_member_cannot_be_deleted_by_a_unauthenticated_user(): void
    {
        $member = User::factory()->create();
        $member->assignRole(RoleName::MEMBER);

        $admin = User::factory()->create();
        $admin->assignRole(RoleName::ADMIN);

        $response = $this->deleteJson(route('users.destroy', ['user' => $member->id]));

        $response->assertUnauthorized();

        $this->assertDatabaseHas($member->getTable(), [
            'id' => $member->id,
            'status' => UserStatusName::ACTIVE,
        ]);
    }
}
