<?php

namespace Tests\Feature\Core;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_swagger_page_and_openapi_specification_are_available(): void
    {
        $this->get(route('api.docs'))
            ->assertOk()
            ->assertSee('swagger-ui-bundle.js')
            ->assertSee('supportedSubmitMethods: []', false);

        $this->get(route('api.openapi'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json; charset=utf-8');
    }

    public function test_documented_api_paths_match_registered_api_routes(): void
    {
        $specification = json_decode(file_get_contents(base_path('docs/api/openapi.json')), true, flags: JSON_THROW_ON_ERROR);
        $documented = [];

        foreach ($specification['paths'] as $path => $operations) {
            foreach (array_keys($operations) as $method) {
                $documented[] = strtoupper($method).' '.$path;
            }
        }

        $registered = [];
        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/') || in_array($route->uri(), ['api/docs', 'api/openapi.json'])) {
                continue;
            }

            foreach ($route->methods() as $method) {
                if ($method !== 'HEAD') {
                    $registered[] = $method.' /'.$route->uri();
                }
            }
        }

        sort($documented);
        sort($registered);

        $this->assertSame($registered, $documented);
    }
}
