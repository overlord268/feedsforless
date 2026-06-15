<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\RfqListController;
use App\Http\Controllers\Api\V1\QuoteRequestController;
use App\Http\Controllers\Api\V1\NewsletterController;
use App\Http\Controllers\Api\V1\Admin\AdminConversationController;
use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\ClientDashboardController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\Admin\AdminQuoteController;
use App\Http\Controllers\Api\V1\Admin\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\AdminProductImportController;
use App\Http\Controllers\Api\V1\Admin\AdminProductDocumentController;
use App\Http\Controllers\Api\V1\Admin\AdminPackagingTypeController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\AdminCompanyController;
use App\Http\Controllers\Api\V1\Admin\AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\AdminProductTechnicalController;
use App\Http\Controllers\Api\V1\Admin\AdminProductPackagingController;
use App\Http\Controllers\Api\V1\Admin\AdminVolumePricingTierController;
use App\Http\Controllers\Api\V1\Admin\AdminProductSpecificationController;
use App\Http\Controllers\Api\V1\Admin\AdminMeasureUnitController;
use App\Http\Controllers\Api\V1\Admin\AdminParameterController;
use App\Http\Controllers\Api\V1\Admin\AdminTestMethodController;
use App\Http\Controllers\Api\V1\Admin\AdminHandlingSpecController;
use App\Http\Controllers\Api\V1\Admin\AdminTypicalApplicationController;
use App\Http\Controllers\Api\V1\Admin\AdminNutritionalAnalysisController;
use App\Http\Controllers\Api\V1\Admin\AdminNutritionalParameterController;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogsController;
use App\Http\Controllers\Api\V1\Admin\AdminRelatedProductController;
use App\Http\Controllers\Api\V1\Admin\AdminAgentApiTokenController;
use App\Http\Controllers\Api\V1\Admin\AdminFflSkuConfigController;
use App\Http\Controllers\Api\V1\MastersController;
use App\Http\Controllers\Api\V1\AiProductController;
use App\Http\Middleware\EnsureUserIsAdmin;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/catalog/categories', [CategoryController::class, 'index']);    
    Route::get('/catalog/products', [CatalogController::class, 'index']);
    Route::get('/catalog/products/by-slug/{slug}', [CatalogController::class, 'showBySlug'])->where('slug', '[a-z0-9\-]+');
    Route::get('/catalog/products/{id}', [CatalogController::class, 'show'])->whereNumber('id');
    Route::get('/catalog/products/{product}/documents/{type}', [CatalogController::class, 'document'])->where('type', 'tds|sds|coa');

    Route::post('/rfq-list/items', [RfqListController::class, 'addItem']);
    Route::get('/rfq-list', [RfqListController::class, 'show']);
    Route::delete('/rfq-list/items/{itemId}', [RfqListController::class, 'removeItem']);

    Route::post('/quote-requests/guest', [QuoteRequestController::class, 'submitGuest']);

    Route::post('/newsletter/market-insights', [NewsletterController::class, 'subscribeMarketInsights']);

    Route::middleware(['auth.agent', 'throttle:60,1'])->group(function () {
        Route::get('/masters', [MastersController::class, 'index']);
        Route::post('/ai/products', [AiProductController::class, 'store']);
    });

    Route::middleware('auth.sanctum.attempt')->group(function () {
        Route::get('/conversations/current', [ConversationController::class, 'current']);
        Route::post('/conversations', [ConversationController::class, 'start']);
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
        Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

        Route::get('/dashboard', [ClientDashboardController::class, 'stats']);

        Route::apiResource('addresses', AddressController::class);
        
        Route::get('/quote-requests', [QuoteRequestController::class, 'index']);
        Route::get('/quote-requests/{quoteRequest}', [QuoteRequestController::class, 'show']);
        Route::post('/quote-requests', [QuoteRequestController::class, 'submit']);
        Route::post('/quote-requests/{quoteRequest}/accept', [QuoteRequestController::class, 'accept']); 
        Route::post('/quote-requests/{quoteRequest}/reject', [QuoteRequestController::class, 'reject']); 

        Route::prefix('admin')->middleware(EnsureUserIsAdmin::class)->group(function () {

            Route::get('dashboard/stats', [App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'stats']);
            Route::get('agent-api-tokens', [AdminAgentApiTokenController::class, 'index']);
            Route::post('agent-api-tokens', [AdminAgentApiTokenController::class, 'store']);
            Route::delete('agent-api-tokens/{agentApiToken}', [AdminAgentApiTokenController::class, 'destroy']);
            Route::post('agent-api-tokens/{agentApiToken}/rotate', [AdminAgentApiTokenController::class, 'rotate']);
            Route::get('catalogs', [AdminCatalogsController::class, 'index']);
            Route::post('products/{product}/handling-specs', [AdminProductTechnicalController::class, 'syncHandling']);
            Route::post('products/{product}/applications', [AdminProductTechnicalController::class, 'syncApplications']);

            Route::get('products/import/template', [AdminProductImportController::class, 'template']);
            Route::post('products/import', [AdminProductImportController::class, 'import']);

            Route::post('products/{product}/categories', [AdminProductController::class, 'syncCategories']);
            Route::apiResource('products.packaging', AdminProductPackagingController::class)->parameters([
                'packaging' => 'packaging'
            ]);
            Route::apiResource('packaging.tiers', AdminVolumePricingTierController::class)->parameters([
                'packaging' => 'packaging',
                'tiers' => 'tier'
            ]);
            
            Route::apiResource('products.specifications', AdminProductSpecificationController::class)->only(['index', 'store', 'destroy']);
            Route::post('products/documents/upload', [AdminProductDocumentController::class, 'upload']);
            Route::get('products/{product}/documents/{type}', [AdminProductDocumentController::class, 'download'])->where('type', 'tds|sds|coa');
            Route::post('products/bulk-action', [AdminProductController::class, 'bulkAction']);
            Route::post('products/{product}/restore', [AdminProductController::class, 'restore'])->whereNumber('product');
            Route::delete('products/{product}/force', [AdminProductController::class, 'forceDestroy'])->whereNumber('product');
            Route::apiResource('categories', AdminCategoryController::class);
            Route::apiResource('products', AdminProductController::class);
            Route::apiResource('packaging-types', AdminPackagingTypeController::class);
            Route::apiResource('users', AdminUserController::class);
            Route::post('users/{user}/roles', [AdminUserController::class, 'assignRole']);
            Route::apiResource('companies', AdminCompanyController::class);
            
            Route::get('/quote-requests', [AdminQuoteController::class, 'index']);
            Route::get('/quote-requests/notifications', [AdminQuoteController::class, 'notifications']);
            Route::get('/quote-requests/{quoteRequest}', [AdminQuoteController::class, 'show']);
            Route::put('/quote-requests/{quoteRequest}/prices', [AdminQuoteController::class, 'updatePrices']);
            Route::put('/quote-requests/{quoteRequest}/status', [AdminQuoteController::class, 'updateStatus']);

            Route::get('/conversations/unread-count', [AdminConversationController::class, 'unreadCount']);
            Route::get('/conversations', [AdminConversationController::class, 'index']);
            Route::get('/conversations/{conversation}', [AdminConversationController::class, 'show']);
            Route::post('/conversations/{conversation}/messages', [AdminConversationController::class, 'sendMessage']);
            Route::patch('/conversations/{conversation}/status', [AdminConversationController::class, 'updateStatus']);
            Route::delete('/conversations/{conversation}', [AdminConversationController::class, 'destroy']);

            Route::apiResource('measure-units', AdminMeasureUnitController::class);
            Route::apiResource('parameters', AdminParameterController::class);
            Route::apiResource('test-methods', AdminTestMethodController::class);
            Route::apiResource('handling-specs', AdminHandlingSpecController::class);
            Route::apiResource('typical-applications', AdminTypicalApplicationController::class);
            Route::apiResource('nutritional-parameters', AdminNutritionalParameterController::class);

            Route::apiResource('products.nutritional-analysis', AdminNutritionalAnalysisController::class)->only(['index', 'store', 'destroy']);
            Route::apiResource('products.related-products', AdminRelatedProductController::class)->only(['index', 'store', 'destroy']);

            Route::prefix('settings/ffl-sku')->group(function () {
                Route::get('/', [AdminFflSkuConfigController::class, 'show']);
                Route::get('/audits', [AdminFflSkuConfigController::class, 'audits']);
                Route::post('/grades', [AdminFflSkuConfigController::class, 'storeGrade']);
                Route::put('/grades/{grade}', [AdminFflSkuConfigController::class, 'updateGrade']);
                Route::delete('/grades/{grade}', [AdminFflSkuConfigController::class, 'destroyGrade']);
            });
        });
    });
});