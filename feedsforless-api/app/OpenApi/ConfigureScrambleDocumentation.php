<?php

namespace App\OpenApi;

use App\Http\Middleware\EnsureUserIsAdmin;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ConfigureScrambleDocumentation
{
    public static function register(): void
    {
        Gate::define('viewApiDocs', fn () => true);

        Scramble::configure()
            ->expose(
                ui: '/docs/api',
                document: '/docs/api.json',
            )
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                self::transformDocument($openApi);
            })
            ->withOperationTransformers(function (Operation $operation, RouteInfo $routeInfo): void {
                self::transformOperation($operation, $routeInfo);
            });
    }

    public static function transformDocument(OpenApi $openApi): void
    {
        $openApi->security = null;

        $openApi->components->addSecurityScheme(
            'sanctum',
            SecurityScheme::http('bearer', 'Sanctum')
                ->setDescription('Laravel Sanctum user API token from login or register.')
        );

        $openApi->components->addSecurityScheme(
            'agentToken',
            SecurityScheme::http('bearer', 'Agent API Token')
                ->setDescription('Machine-to-machine agent token (admin-generated). Separate from Sanctum.')
        );

        $errorResponse = Schema::fromType(
            (new ObjectType)
                ->addProperty('message', new StringType)
                ->setRequired(['message'])
        );

        $validationError = Schema::fromType(
            (new ObjectType)
                ->addProperty('message', new StringType)
                ->addProperty(
                    'errors',
                    (new ObjectType)->additionalProperties(
                        (new ArrayType)->setItems(new StringType)
                    )
                )
                ->setRequired(['message', 'errors'])
        );

        $openApi->components->addSchema('ErrorResponse', $errorResponse);
        $openApi->components->addSchema('ValidationError', $validationError);
    }

    public static function transformOperation(Operation $operation, RouteInfo $routeInfo): void
    {
        $middleware = $routeInfo->route->gatherMiddleware();

        $hasAgentAuth = collect($middleware)->contains('auth.agent');
        $hasSanctumAuth = collect($middleware)->contains(
            fn (string $middlewareName) => $middlewareName === 'auth:sanctum'
                || Str::startsWith($middlewareName, 'auth:sanctum')
        );
        $hasOptionalSanctum = collect($middleware)->contains('auth.sanctum.attempt');
        $hasAdmin = collect($middleware)->contains(
            fn (string $middlewareName) => $middlewareName === EnsureUserIsAdmin::class
                || str_contains($middlewareName, 'EnsureUserIsAdmin')
        );

        $operation->security = [];

        if ($hasAgentAuth) {
            $operation->security = [new SecurityRequirement(['agentToken' => []])];
        } elseif ($hasSanctumAuth) {
            $operation->security = [new SecurityRequirement(['sanctum' => []])];
        } elseif ($hasOptionalSanctum) {
            self::appendDescription(
                $operation,
                'Authentication is optional. Send `Authorization: Bearer {sanctum_token}` when logged in; guests may use conversation session headers instead.'
            );
        }

        if ($hasAdmin) {
            self::appendDescription(
                $operation,
                'Requires Sanctum authentication and an admin role (`admin`, `Admin`, or `Super Admin`).'
            );
        }
    }

    private static function appendDescription(Operation $operation, string $note): void
    {
        $operation->description = trim($operation->description)
            ? trim($operation->description)."\n\n".$note
            : $note;
    }
}
