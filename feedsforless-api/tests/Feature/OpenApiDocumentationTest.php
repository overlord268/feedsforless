<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OpenApiDocumentationTest extends TestCase
{
    public function test_openapi_json_endpoint_returns_valid_spec(): void
    {
        $response = $this->get('/docs/api.json');

        $response->assertOk();
        $response->assertJsonStructure([
            'openapi',
            'info' => ['title', 'version'],
            'paths',
        ]);

        $spec = $response->json();
        $this->assertNotEmpty($spec['paths']);
        $this->assertArrayHasKey('components', $spec);
        $this->assertArrayHasKey('securitySchemes', $spec['components']);
        $this->assertArrayHasKey('sanctum', $spec['components']['securitySchemes']);
        $this->assertArrayHasKey('agentToken', $spec['components']['securitySchemes']);
    }

    public function test_openapi_swagger_ui_is_publicly_accessible(): void
    {
        $response = $this->get('/docs/api');

        $response->assertOk();
    }

    public function test_openapi_export_command_writes_valid_json(): void
    {
        $path = storage_path('api-docs/openapi.json');

        $this->artisan('scramble:export', ['--path' => $path])
            ->assertSuccessful();

        $this->assertFileExists($path);

        $decoded = json_decode(File::get($path), true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('paths', $decoded);
        $this->assertNotEmpty($decoded['paths']);
    }
}
