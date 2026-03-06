<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\RfqListController;
use App\Http\Controllers\Api\V1\QuoteRequestController;
use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Admin\AdminQuoteController;
use App\Http\Controllers\Api\V1\Admin\AdminProductController;
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
use App\Http\Middleware\EnsureUserIsAdmin;

Route::prefix('v1')->group(function () {
    Route::get('/admin/dashboard/stats', [App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'stats']);
    
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/catalog/categories', [CategoryController::class, 'index']);    
    Route::get('/catalog/products', [CatalogController::class, 'index']);
    Route::get('/catalog/products/{product}', [CatalogController::class, 'show']);

    Route::post('/rfq-list/items', [RfqListController::class, 'addItem']);
    Route::get('/rfq-list', [RfqListController::class, 'show']);
    Route::delete('/rfq-list/items/{itemId}', [RfqListController::class, 'removeItem']);

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        
        Route::apiResource('addresses', AddressController::class);
        
        Route::get('/quote-requests', [QuoteRequestController::class, 'index']);
        Route::post('/quote-requests', [QuoteRequestController::class, 'submit']);
        Route::post('/quote-requests/{quoteRequest}/accept', [QuoteRequestController::class, 'accept']); 
        Route::post('/quote-requests/{quoteRequest}/reject', [QuoteRequestController::class, 'reject']); 

        Route::prefix('admin')->middleware(EnsureUserIsAdmin::class)->group(function () {

            Route::get('catalogs', [AdminCatalogsController::class, 'index']);
            Route::post('products/{product}/handling-specs', [AdminProductTechnicalController::class, 'syncHandling']);
            Route::post('products/{product}/applications', [AdminProductTechnicalController::class, 'syncApplications']);

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
            Route::apiResource('categories', AdminCategoryController::class);
            Route::apiResource('products', AdminProductController::class);
            Route::apiResource('packaging-types', AdminPackagingTypeController::class);
            Route::apiResource('users', AdminUserController::class);
            Route::post('users/{user}/roles', [AdminUserController::class, 'assignRole']);
            Route::apiResource('companies', AdminCompanyController::class);
            
            Route::get('/quote-requests', [AdminQuoteController::class, 'index']);
            Route::get('/quote-requests/{quoteRequest}', [AdminQuoteController::class, 'show']);
            Route::put('/quote-requests/{quoteRequest}/prices', [AdminQuoteController::class, 'updatePrices']);
            Route::put('/quote-requests/{quoteRequest}/status', [AdminQuoteController::class, 'updateStatus']);

            Route::apiResource('measure-units', AdminMeasureUnitController::class);
            Route::apiResource('parameters', AdminParameterController::class);
            Route::apiResource('test-methods', AdminTestMethodController::class);
            Route::apiResource('handling-specs', AdminHandlingSpecController::class);
            Route::apiResource('typical-applications', AdminTypicalApplicationController::class);
            Route::apiResource('nutritional-parameters', AdminNutritionalParameterController::class);

            Route::apiResource('products.nutritional-analysis', AdminNutritionalAnalysisController::class)->only(['index', 'store', 'destroy']);
            Route::apiResource('products.related-products', AdminRelatedProductController::class)->only(['index', 'store', 'destroy']);
        });
    });
});