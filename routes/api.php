<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\MerchantStatsController;
use App\Http\Controllers\Api\MerchantStoreController;
use App\Http\Controllers\Api\Admin\MerchantController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\Admin\PriceModerationController;
use App\Http\Controllers\Api\Admin\CommentModerationController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\NewsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register-merchant', [AuthController::class, 'registerMerchant']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public data
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/prices/{product}', [PriceController::class, 'index']);
Route::get('/prices/{product}/average', [PriceController::class, 'average']);
Route::get('/prices/{product}/chart', [PriceController::class, 'chart']);
Route::get('/regions', [RegionController::class, 'index']);
Route::get('/regions/{region}/markets', [RegionController::class, 'markets']);
Route::get('/comments/{product}', [CommentController::class, 'index']);
Route::get('/news', [NewsController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/upload-photo', [AuthController::class, 'uploadProfilePhoto']);
    Route::delete('/auth/remove-photo', [AuthController::class, 'removeProfilePhoto']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{product}/toggle', [FavoriteController::class, 'toggle']);

    // Comments
    Route::post('/comments/{product}', [CommentController::class, 'store']);
    Route::post('/comments/{comment}/reply', [CommentController::class, 'reply']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/comments/{comment}/like', [CommentController::class, 'like']);

    // Reports
    Route::post('/reports', [ReportController::class, 'store']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/read', [NotificationController::class, 'clearRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

/*
|--------------------------------------------------------------------------
| Pedagang Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api', 'role:pedagang,admin'])->prefix('merchant')->group(function () {
    Route::post('/prices', [PriceController::class, 'store']);
    Route::put('/prices/{price}', [PriceController::class, 'update']);
    Route::delete('/prices/{price}', [PriceController::class, 'destroy']);
    Route::get('/history', [PriceController::class, 'history']);
    Route::get('/suspicious', [PriceController::class, 'suspicious']);
    Route::get('/stats', [MerchantStatsController::class, 'stats']);

    // Merchant Stores
    Route::get('/stores', [MerchantStoreController::class, 'index']);
    Route::post('/stores', [MerchantStoreController::class, 'store']);
    Route::put('/profile', [AuthController::class, 'updateMerchantProfile']);

    // Add new product
    Route::post('/products', [ProductController::class, 'createProduct']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'stats']);
    Route::get('/analytics', [DashboardController::class, 'analytics']);
    Route::get('/export-prices', [DashboardController::class, 'exportPrices']);
    Route::post('/broadcast', [DashboardController::class, 'broadcast']);

    // Merchant verification
    Route::get('/merchants', [MerchantController::class, 'all']);
    Route::get('/merchants/pending', [MerchantController::class, 'pending']);
    Route::post('/merchants/{user}/approve', [MerchantController::class, 'approve']);
    Route::post('/merchants/{user}/reject', [MerchantController::class, 'reject']);

    // Store requests
    Route::get('/stores/pending', [MerchantController::class, 'pendingStores']);
    Route::post('/stores/{store}/approve', [MerchantController::class, 'approveStore']);
    Route::post('/stores/{store}/reject', [MerchantController::class, 'rejectStore']);

    // User management
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::post('/users/{user}/suspend', [UserManagementController::class, 'suspend']);
    Route::post('/users/{user}/mute', [UserManagementController::class, 'mute']);
    Route::post('/users/{user}/activate', [UserManagementController::class, 'activate']);

    // Price moderation
    Route::get('/prices/suspicious', [PriceModerationController::class, 'suspicious']);
    Route::delete('/prices/{price}', [PriceModerationController::class, 'deletePrice']);
    Route::post('/prices/{price}/suspicious', [PriceModerationController::class, 'markSuspicious']);
    Route::post('/prices/{price}/clear', [PriceModerationController::class, 'clearSuspicious']);

    // Comment moderation
    Route::get('/comments/flagged', [CommentModerationController::class, 'flagged']);
    Route::delete('/comments/{comment}', [CommentModerationController::class, 'deleteComment']);
    Route::post('/comments/{comment}/warn', [CommentModerationController::class, 'warn']);

    // Reports
    Route::get('/reports', [AdminReportController::class, 'index']);
    Route::post('/reports/{report}/resolve', [AdminReportController::class, 'resolve']);
    Route::post('/reports/{report}/review', [AdminReportController::class, 'review']);
});
