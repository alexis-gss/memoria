<?php

namespace Tests\Back\Models;

use App\Enums\Users\RoleEnum;
use App\Models\StaticPage;
use App\Models\User as AuthModel;
use Illuminate\Support\Facades\Schema;
use LaravelActivityLogs\Enums\ActivityLogsEventEnum;
use LaravelActivityLogs\Models\ActivityLog;
use Tests\TestCase;

class StaticPageTest extends TestCase
{
    /**
     * TESTS GUEST CANNOT ACCESS VIEWS.
     */

    /** @return void */
    public function testGuestCannotAccessStaticPagesIndexView(): void
    {
        $response = $this->get(route(
            config('unit-tests.route.prefix') .
                'static_pages.' .
                config('unit-tests.view.resources-index')
        ));
        $response->assertRedirect(route(config('unit-tests.route.prefix') . 'login'));
    }

    /** @return void */
    public function testGuestCannotAccessStaticPagesReadView(): void
    {
        $model    = StaticPage::query()->inRandomOrder()->first();
        $response = $this->get(route(
            config('unit-tests.route.prefix') .
                'static_pages.' .
                config('unit-tests.view.resources-read'),
            $model->getKey()
        ));
        $response->assertRedirect(route(config('unit-tests.route.prefix') . 'login'));
    }

    /** @return void */
    public function testGuestCannotAccessStaticPagesUpdateView(): void
    {
        $model    = StaticPage::query()->inRandomOrder()->first();
        $response = $this->get(route(
            config('unit-tests.route.prefix') .
                'static_pages.' .
                config('unit-tests.view.resources-update'),
            $model->getKey()
        ));
        $response->assertRedirect(route(config('unit-tests.route.prefix') . 'login'));
    }

    /**
     * TESTS GUEST CANNOT POST MODEL.
     */

    /** @return void */
    public function testGuestCannotUpdateStaticPage(): void
    {
        $model    = StaticPage::query()->inRandomOrder()->first();
        $response = $this->patch(
            route(
                config('unit-tests.route.prefix') . 'static_pages.' . config('unit-tests.route.action-update'),
                $model->getKey()
            ),
            $model->toArray()
        );
        $response->assertRedirect(route(config('unit-tests.route.prefix') . 'login'));
    }

    /**
     * TESTS USER CONCEPTOR ACCESS VIEWS.
     */

    /** @return void */
    public function testUserConceptorCanAccessStaticPagesIndexView(): void
    {
        $authModel = AuthModel::factory()->createOneQuietly();
        $authModel->update(['published' => true, 'role' => RoleEnum::conceptor]);
        $response = $this->actingAs($authModel, 'backend')->get(
            route(config('unit-tests.route.prefix') . 'static_pages.' . config('unit-tests.view.resources-index'))
        );
        $response->assertSuccessful();
        $response->assertViewIs(
            config('unit-tests.view.prefix') .
                'pages.static_pages.' .
                config('unit-tests.view.resources-index')
        );
    }

    /** @return void */
    public function testUserConceptorCanAccessStaticPagesReadView(): void
    {
        $authModel = AuthModel::factory()->createOneQuietly();
        $authModel->update(['published' => true, 'role' => RoleEnum::conceptor]);
        $model    = StaticPage::query()->inRandomOrder()->first();
        $response = $this->actingAs($authModel, 'backend')->get(
            route(config('unit-tests.route.prefix') . 'static_pages.' . config('unit-tests.view.resources-read'), $model)
        );
        $response->assertSuccessful();
        $response->assertViewIs(
            config('unit-tests.view.prefix') .
                'pages.static_pages.' .
                config('unit-tests.view.resources-read')
        );
    }

    /** @return void */
    public function testUserConceptorCanAccessStaticPagesUpdateView(): void
    {
        $authModel = AuthModel::factory()->createOneQuietly();
        $authModel->update(['published' => true, 'role' => RoleEnum::conceptor]);
        $model    = StaticPage::query()->inRandomOrder()->first();
        $response = $this->actingAs($authModel, 'backend')->get(
            route(config('unit-tests.route.prefix') . 'static_pages.' . config('unit-tests.view.resources-update'), $model)
        );
        $response->assertSuccessful();
        $response->assertViewIs(
            config('unit-tests.view.prefix') .
                'pages.static_pages.' .
                config('unit-tests.view.resources-update')
        );
    }

    /**
     * TESTS CAN ACTIONS ON MODEL.
     */

    /** @return void */
    public function testCanUpdateStaticPage(): void
    {
        $model = StaticPage::query()->inRandomOrder()->first();

        $fieldTest = "";
        foreach (config('unit-tests.list-fields') as $field) {
            if (Schema::hasColumn($model->getTable(), $field)) {
                $model->update([$field => "test"]);
                $fieldTest = $field;
                break;
            }
        }

        $this->assertTrue($model->wasChanged());
        $this->assertTrue(array_key_exists($fieldTest, $model->getChanges()));
        $this->assertModelExists($model);
    }

    /**
     * TESTS RELATIONS
     */

    /** @return void */
    public function testRelationActivityLogs(): void
    {
        $staticPage  = StaticPage::query()->inRandomOrder()->first();
        $activityLog = ActivityLog::factory()->createOneQuietly([
            'user_id'      => null,
            'is_anonymous' => true,
            'is_console'   => false,
            'model_class'  => sprintf("\%s", get_class($staticPage)),
            'model_id'     => $staticPage->getKey(),
            'event'        => ActivityLogsEventEnum::created->value(),
            'data'         => [],
            'created_at'   => now(),
        ]);
        $this->assertModelExists($staticPage);
        $this->assertModelExists($activityLog);
        $this->assertIsObject($staticPage->activityLogs);
        $this->assertCount(1, $staticPage->activityLogs);
        $this->assertInstanceOf(ActivityLog::class, $staticPage->activityLogs->first());
    }
}
