<?php

namespace App\Admin\Tests\Feature;

use App\Admin\Models\AdminProfile;
use App\Admin\Models\MemberProfile;
use App\Framework\Enums\RoleName;
use App\Framework\Enums\UserStatusName;
use App\Framework\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_all_users(): void
    {
        $members = User::factory()->times(5)->create();

        $members->each(function (User $user) {
            $user->assignRole(RoleName::MEMBER);

            MemberProfile::factory()->create([
                'user_id' => $user->id,
            ]);
        });

        $admin = User::factory()->create([
            'email' => 'admin@learnhub.mk',
        ]);

        $admin->assignRole(RoleName::ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->getJson(route('users.index'));

        $this->assertCount(5, $response->json()['data']);
        $this->assertSame(5, $response->json()['meta']['total']);
        $response->assertJsonFragment([
            'data' => $this->dataResponse($response->json()['data']),
        ]);
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
        $admin = User::factory()->create([
            'email' => 'admin@learnhub.mk',
        ]);

        AdminProfile::factory()->for($admin)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $admin->assignRole(RoleName::ADMIN);

        Sanctum::actingAs($admin);

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
        collect([
            ['a', 'b'],
            ['c', 'c'],
            ['b', 'a'],
        ])->each(function (array $member) {
            [$first, $last] = $member;

            MemberProfile::factory()->create([
                'first_name' => $first,
                'last_name' => $last,
            ]);
        });

        $admin = User::factory()->create([
            'email' => 'admin@learnhub.mk',
        ]);

        AdminProfile::factory()->for($admin)->create([
            'first_name' => 'd',
            'last_name' => 'd',
        ]);

        $admin->assignRole(RoleName::ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/admin/users?sortBy={$field}&sortDirection={$direction}")->json();

        collect($dataset)->each(function ($name, $index) use ($response, $field) {
            $this->assertSame($name, $response['data'][$index][$field]);
        });
    }

    #[Test]
    public function it_can_show_single_user(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@learnhub.mk',
        ]);

        $admin->assignRole(RoleName::ADMIN);

        AdminProfile::factory()->for($admin)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/admin/users/' . $admin->id);

        $response->assertExactJson([
            'data' => [
                'id' => $admin->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => $admin->email,
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

        $member->fresh();

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

    private function dataResponse(array $data): array
    {
        return collect($data)->map(function ($item) {
            return [
                'id' => Arr::get($item, 'id'),
                'first_name' => Arr::get($item, 'first_name'),
                'last_name' => Arr::get($item, 'last_name'),
                'email' => Arr::get($item, 'email'),
                'role' => Arr::get($item, 'role'),
            ];
        })->toArray();
    }
}
