<?php

// Redirect old "auth/" routes to new clean routes for SEO and user experience
$requestUriPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$basePath = trim(parse_url(BASE_URL, PHP_URL_PATH), '/');

$cleanUri = $requestUriPath;
if (!empty($basePath) && strpos($requestUriPath, $basePath) === 0) {
    // Remove the base path (like 'taxi') from the start of the URI
    $cleanUri = trim(substr($requestUriPath, strlen($basePath)), '/');
}

if (strpos($cleanUri, 'auth/') === 0) {
    $newUri = str_replace('auth/', '', $cleanUri);
    // Redirect to the new clean URI
    header('Location: ' . BASE_URL . '/' . $newUri, true, 301);
    exit;
}

// Note: The $router variable is now initialized in App.php and passed here.

// This file will now act as the main entry point for all web routes.

// Include other route files
// require_once 'calls.php';
// If you have other route files like api.php, include them here as well.
// require_once 'api.php';

/**
 * --------------------------------------------------------------------------
 * Web Routes
 * --------------------------------------------------------------------------
 *
 * Here is where you can register web routes for your application. These
 * routes are loaded by the App class.
 *
 * Example:
 * $router->get('users', 'UsersController@index');
 * $router->post('users', 'UsersController@store');
 * $router->get('users/{id}', 'UsersController@show');
 *
 */

$router->get('', 'HomeController@index');

// Authentication Routes
$router->get('login', 'Auth/AuthController@login');
$router->post('login', 'Auth/AuthController@login');
$router->get('register', 'Auth/AuthController@register');
$router->post('register', 'Auth/AuthController@register');
$router->get('logout', 'Auth/AuthController@logout');

// Password Reset Routes
$router->get('forgot-password', 'Password/PasswordResetController@showRequestForm');
$router->post('forgot-password', 'Password/PasswordResetController@handleRequestForm');
$router->get('reset-password/{token}', 'Password/PasswordResetController@showResetForm');
$router->post('reset-password', 'Password/PasswordResetController@handleReset');

// Profile routes
$router->get('profile', 'Auth/AuthController@profile')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('profile/update', 'Auth/AuthController@updateProfile')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

$router->get('dashboard', 'Dashboard/DashboardController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('dashboard/{action}', 'Dashboard/DashboardController@{action}')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

// Documentation
$router->get('documentation', 'Documentation/DocumentationController@index');

// Knowledge Base
$router->get('knowledge_base', 'knowledge_base/KnowledgeBaseController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('knowledge_base/create', 'knowledge_base/KnowledgeBaseController@create')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('knowledge_base/store', 'knowledge_base/KnowledgeBaseController@store')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('knowledge_base/show/{id}', 'knowledge_base/KnowledgeBaseController@show')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('knowledge_base/edit/{id}', 'knowledge_base/KnowledgeBaseController@edit')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('knowledge_base/update/{id}', 'knowledge_base/KnowledgeBaseController@update')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('knowledge_base/destroy', 'knowledge_base/KnowledgeBaseController@destroy')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('knowledge_base/findByCode/{ticketCodeId}', 'knowledge_base/KnowledgeBaseController@findByCode');
$router->get('knowledge_base/search', 'knowledge_base/KnowledgeBaseController@searchApi');

// Admin Routes
$adminControllers = [
    'UsersController' => 'users'
];

// Custom routes for BonusController
$router->get("admin/bonus", "Admin/BonusController@index");
$router->get("admin/bonus/settings", "Admin/BonusController@settings");
$router->post("admin/bonus/updateSettings", "Admin/BonusController@updateSettings");
$router->post("admin/bonus/grant", "Admin/BonusController@grant");
$router->post("admin/bonus/delete/{id}", "Admin/BonusController@delete");

// Custom routes for TelegramSettingsController
$router->get("admin/telegram_settings", "Admin/TelegramSettingsController@index");
$router->post("admin/telegram_settings/add", "Admin/TelegramSettingsController@add");
$router->post("admin/telegram_settings/delete/{id}", "Admin/TelegramSettingsController@delete");

// Custom routes for PlatformsController
$router->get("admin/platforms", "Admin/PlatformsController@index");
$router->post("admin/platforms/store", "Admin/PlatformsController@store");
$router->post("admin/platforms/update", "Admin/PlatformsController@update");
$router->post("admin/platforms/delete/{id}", "Admin/PlatformsController@delete");

// Custom routes for RolesController
$router->get("admin/roles", "Admin/RolesController@index");
$router->post("admin/roles/store", "Admin/RolesController@store");
$router->post("admin/roles/update", "Admin/RolesController@update");
$router->post("admin/roles/delete/{id}", "Admin/RolesController@delete");

// Custom routes for CarTypesController
$router->get("admin/car_types", "Admin/CarTypesController@index");
$router->post("admin/car_types/store", "Admin/CarTypesController@store");
$router->post("admin/car_types/delete/{id}", "Admin/CarTypesController@delete");

// Custom routes for CountriesController
$router->get("admin/countries", "Admin/CountriesController@index");
$router->post("admin/countries/store", "Admin/CountriesController@store");
$router->post("admin/countries/delete/{id}", "Admin/CountriesController@delete");

// Custom routes for DocumentTypesController
$router->get("admin/document_types", "Admin/DocumentTypesController@index");
$router->post("admin/document_types/store", "Admin/DocumentTypesController@store");
$router->post("admin/document_types/update", "Admin/DocumentTypesController@update");
$router->post("admin/document_types/delete/{id}", "Admin/DocumentTypesController@delete");

// Custom routes for PointsController
$router->get("admin/points", "Admin/PointsController@index");
$router->post("admin/points/setTicketPoints", "Admin/PointsController@setTicketPoints");
$router->post("admin/points/setCallPoints", "Admin/PointsController@setCallPoints");

// Custom routes for TeamsController
$router->get("admin/teams", "Admin/TeamsController@index");
$router->get("admin/teams/create", "Admin/TeamsController@create");
$router->post("admin/teams/store", "Admin/TeamsController@store");
$router->get("admin/teams/edit/{id}", "Admin/TeamsController@edit");
$router->post("admin/teams/update", "Admin/TeamsController@update");
$router->post("admin/teams/delete/{id}", "Admin/TeamsController@delete");
$router->post("admin/teams/addMember", "Admin/TeamsController@addMember");
$router->get("admin/teams/removeMember/{teamId}/{userId}", "Admin/TeamsController@removeMember");

// Custom routes for TeamMembersController
$router->get("admin/team_members", "Admin/TeamMembersController@index");
$router->post("admin/team_members/store", "Admin/TeamMembersController@store");
$router->post("admin/team_members/delete/{id}", "Admin/TeamMembersController@delete");

// Custom routes for DelegationTypesController
$router->get("delegation-types", "DelegationTypesController@index");
$router->post("delegation-types/create", "DelegationTypesController@create");
$router->post("delegation-types/update", "DelegationTypesController@update");
$router->post("delegation-types/delete", "DelegationTypesController@delete");

// Custom routes for UserDelegationsController
$router->get("user-delegations", "UserDelegationsController@index");
$router->post("user-delegations/create", "UserDelegationsController@create");
$router->post("user-delegations/delete", "UserDelegationsController@delete");

// Employee Evaluations
$router->get('employee-evaluations', 'EmployeeEvaluationsController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('employee-evaluations/create', 'EmployeeEvaluationsController@create')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('employee-evaluations/delete', 'EmployeeEvaluationsController@delete')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

// Custom routes for TicketCategoriesController
$router->get("admin/ticket_categories", "Admin/TicketCategoriesController@index");
$router->post("admin/ticket_categories/store", "Admin/TicketCategoriesController@store");
$router->post("admin/ticket_categories/delete/{id}", "Admin/TicketCategoriesController@delete");

// Custom routes for TicketSubCategoriesController
$router->get("admin/ticket_subcategories", "Admin/TicketSubCategoriesController@index");
$router->post("admin/ticket_subcategories/store", "Admin/TicketSubCategoriesController@store");
$router->post("admin/ticket_subcategories/delete/{id}", "Admin/TicketSubCategoriesController@delete");

// Custom routes for TicketCodesController
$router->get("admin/ticket_codes", "Admin/TicketCodesController@index");
$router->post("admin/ticket_codes/store", "Admin/TicketCodesController@store");
$router->post("admin/ticket_codes/delete/{id}", "Admin/TicketCodesController@delete");
$router->get("admin/ticket_codes/get_by_subcategory/{id}", "Admin/TicketSubcategoriesController@getByCategory");
$router->get("admin/ticket_subcategories/get_by_category/{id}", "Admin/TicketSubcategoriesController@getByCategory");

// Custom routes for CouponsController
$router->get("admin/coupons", "Admin/CouponsController@index");
$router->post("admin/coupons", "Admin/CouponsController@index");

// Custom routes for PermissionsController
$router->get("admin/permissions", "Admin/PermissionsController@index");
$router->post("admin/permissions/toggle", "Admin/PermissionsController@toggle");
$router->post("admin/permissions/toggleRolePermission", "Admin/PermissionsController@toggleRolePermission");
$router->post("admin/permissions/batchUpdateRolePermissions", "Admin/PermissionsController@batchUpdateRolePermissions");
$router->post("admin/permissions/batchUpdateUserPermissions", "Admin/PermissionsController@batchUpdateUserPermissions");

// Reports
$router->get("reports/myactivity", "Reports/MyActivity/MyActivityController@index");

// Notification Routes
$router->get('notifications', 'Notifications/NotificationsController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('notifications/create', 'Notifications/NotificationsController@create')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('notifications/store', 'Notifications/NotificationsController@store')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('notifications/getNavNotifications', 'Notifications/NotificationsController@getNavNotifications');
$router->post('notifications/markRead', 'Notifications/NotificationsController@markRead')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('notifications/history', 'Notifications/NotificationsController@history')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('notifications/readers/{id}', 'Notifications/NotificationsController@readers')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

// Calls Routes
$router->get('calls', 'Calls/CallsController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('calls/record', 'Calls/CallsController@record')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('calls/skip/{driverId}', 'Calls/CallsController@skip');
$router->get('calls/subcategories/{categoryId}', 'Calls/CallsController@getSubcategories');
$router->get('calls/codes/{subcategoryId}', 'Calls/CallsController@getCodes');
$router->post('calls/updateDocuments', 'Calls/CallsController@updateDocuments');
$router->post('calls/releaseHold', 'Calls/CallsController@releaseHold');

// Driver Routes
$router->get('drivers', 'Driver/DriverController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('drivers/details/{id}', 'Driver/DriverController@details')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('drivers/update', 'Driver/DriverController@update')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('drivers/assign', 'Driver/DriverController@assign')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('drivers/document/manage', 'Driver/DriverController@manageDocument')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('drivers/search', 'Driver/DriverController@search');

// Review Routes
$router->get('review/add/{type}/{id}', 'Review/ReviewController@add')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('review/add/{type}/{id}', 'Review/ReviewController@add')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

// Upload Routes
$router->get('upload', 'Upload/UploadController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('upload/process', 'Upload/UploadController@process')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

// Discussion Routes
$router->get('discussions/add/{type}/{id}', 'Discussions/DiscussionsController@add')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('discussions/add/{type}/{id}', 'Discussions/DiscussionsController@add')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('discussions/close/{id}', 'Discussions/DiscussionsController@close')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('discussions/reopen/{id}', 'Discussions/DiscussionsController@reopen')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('discussions/get', 'Discussions/DiscussionsController@getDiscussionsApi');
$router->post('discussions/{id}/mark-as-read', 'Discussions/DiscussionsController@markAsReadApi');
$router->post('discussions/{id}/replies', 'Discussions/DiscussionsController@addReplyApi');

// Ticket search
$router->get('tickets/ajaxSearch', 'Tickets/TicketController@ajaxSearch');

// Quality Management Routes
$router->get('quality/reviews', 'Quality/QualityController@reviews')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('quality/get_reviews_api', 'Quality/QualityController@get_reviews_api');
$router->get('quality/discussions', 'Quality/QualityController@discussions')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('quality/get_discussions_api', 'Quality/QualityController@get_discussions_api');

// Referral Routes
$router->get('referral/dashboard', 'Referral/ReferralController@dashboard')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('referral/register', 'Referral/ReferralController@index');
$router->post('referral/register', 'Referral/ReferralController@index');
$router->get('referral/marketerDetails/{id}', 'Referral/ReferralController@marketerDetails');
$router->get('referral/editProfile/{id}', 'Referral/ReferralController@editProfile');
$router->post('referral/saveAgentProfile', 'Referral/ReferralController@saveAgentProfile');

// Listings
$router->get('listings/tickets', 'Listings/ListingsController@tickets')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('listings/get_tickets_api', 'Listings/ListingsController@get_tickets_api');
$router->get('listings/drivers', 'Listings/ListingsController@drivers')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('listings/get_drivers_api', 'Listings/ListingsController@get_drivers_api');
$router->post('listings/bulk_update_drivers', 'Listings/ListingsController@bulk_update_drivers')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('listings/calls', 'Listings/ListingsController@calls')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('listings/get_calls_api', 'Listings/ListingsController@get_calls_api');

// API Routes
$router->get('drivers/search', 'Driver/DriverController@search');

foreach ($adminControllers as $controller => $uri) {
    // Apply auth middleware to all admin routes
    $controllerPath = 'Admin/' . $controller;
    $router->get("admin/{$uri}", "{$controllerPath}@index")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->get("admin/{$uri}/create", "{$controllerPath}@create")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->post("admin/{$uri}/store", "{$controllerPath}@store")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->get("admin/{$uri}/edit/{id}", "{$controllerPath}@edit")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->post("admin/{$uri}/update/{id}", "{$controllerPath}@update")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->post("admin/{$uri}/destroy", "{$controllerPath}@destroy")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
}

// Add custom routes that don't fit the standard RESTful pattern
$router->post("admin/users/forceLogout", "Admin/UsersController@forceLogout")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

// Helper function for report routes to avoid repetition
function mapReportRoutes($router, $uri, $controllerPath)
{
    // Apply auth middleware to all report routes within this function
    $controller = 'Reports\\' . str_replace('/', '\\', $controllerPath);

    // Route for index method (e.g., /reports/users)
    $router->get("reports/{$uri}", "{$controller}@index")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->post("reports/{$uri}", "{$controller}@index")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']); // For forms like search/filter

    // Route for methods with parameters (e.g., /reports/users/view/1)
    // The router is designed to handle dynamic methods and parameters
    $router->get("reports/{$uri}/{action}", "{$controller}@{action}")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->get("reports/{$uri}/{action}/{p1}", "{$controller}@{action}")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->get("reports/{$uri}/{action}/{p1}/{p2}", "{$controller}@{action}")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

    $router->post("reports/{$uri}/{action}", "{$controller}@{action}")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->post("reports/{$uri}/{action}/{p1}", "{$controller}@{action}")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->post("reports/{$uri}/{action}/{p1}/{p2}", "{$controller}@{action}")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
}

// Mapping all report routes
mapReportRoutes($router, 'users', 'Users/UsersController');
mapReportRoutes($router, 'drivers', 'Drivers/DriversController');
mapReportRoutes($router, 'assignments', 'Assignments/AssignmentsController');
mapReportRoutes($router, 'analytics', 'Analytics/AnalyticsController');
mapReportRoutes($router, 'documents', 'Documents/DocumentsController');
mapReportRoutes($router, 'coupons', 'Coupons/CouponsController');
mapReportRoutes($router, 'logs', 'Logs/LogsController');
mapReportRoutes($router, 'tickets', 'Tickets/TicketController');
mapReportRoutes($router, 'driver-documents-compliance', 'DriverDocumentsCompliance/DriverDocumentsComplianceController');
mapReportRoutes($router, 'driver-assignments', 'DriverAssignments/DriverAssignmentsController');
mapReportRoutes($router, 'tickets-summary', 'TicketsSummary/TicketsSummaryController');
mapReportRoutes($router, 'ticket-reviews', 'TicketReviews/TicketReviewsController');
mapReportRoutes($router, 'ticket-discussions', 'TicketDiscussions/TicketDiscussionsController');
mapReportRoutes($router, 'ticket-coupons', 'TicketCoupons/TicketCouponsController');
mapReportRoutes($router, 'referral-visits', 'ReferralVisits/ReferralVisitsController');
mapReportRoutes($router, 'marketer-summary', 'MarketerSummary/MarketerSummaryController');
mapReportRoutes($router, 'review-quality', 'ReviewQuality/ReviewQualityController');
mapReportRoutes($router, 'ticket-rework', 'TicketRework/TicketReworkController');
mapReportRoutes($router, 'system-logs', 'SystemLogs/SystemLogsController');
mapReportRoutes($router, 'employee-activity-score', 'EmployeeActivityScore/EmployeeActivityScoreController');
mapReportRoutes($router, 'team-leaderboard', 'TeamLeaderboard/TeamLeaderboardController');
mapReportRoutes($router, 'custom', 'Custom/CustomController');
mapReportRoutes($router, 'trips', 'TripsReport/TripsReportController');
mapReportRoutes($router, 'notifications', 'Notifications/NotificationsController');

// Reports Routes
$reportControllers = [
    'AdminDashboard',
    'Analytics',
    'Assignments',
    'Coupons',
    'Custom',
    'Documents',
    'DriverAssignments',
    'DriverDocumentsCompliance',
    'Drivers',
    'DriversReport',
    'EmployeeActivityScore',
    'MarketerSummary',
    'MyActivity',
    'Notifications',
    'ReferralRegistrations',
    'Referrals',
    'ReferralVisits',
    'ReviewQuality',
    'SystemLogs',
    'TeamLeaderboard',
    'TeamPerformance',
    'Teams',
    'TicketCoupons',
    'TicketDiscussions',
    'TicketReviews',
    'TicketRework',
    'Tickets',
    'TicketsSummary',
    'TripsReport',
    'Users'
];

foreach ($reportControllers as $report) {
    // Convert PascalCase to kebab-case for the URI
    $uri = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $report));
    $controllerClass = str_replace('-', '', ucwords($uri, '-'));

    $controllerPath = "Reports/{$controllerClass}/{$controllerClass}Controller";

    // Some controllers might have a different name than their folder
    // This is a simplification, might need adjustments for specific reports
    if ($report === 'Tickets' || $report === 'TicketReviews' || $report === 'TicketCoupons' || $report === 'TicketDiscussions' || $report === 'TicketRework' || $report === 'TicketsSummary') {
        $controllerPath = "Reports/{$controllerClass}/TicketController";
        if ($report === 'TicketsSummary') {
            $controllerPath = "Reports/{$controllerClass}/TicketsSummaryController";
        }
    }
    if ($report === 'TripsReport') {
        $controllerPath = "Reports/{$report}/{$report}Controller";
    }


    $router->get("reports/{$uri}", "{$controllerPath}@index")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
    $router->post("reports/{$uri}/export", "{$controllerPath}@export")->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
}

// Main Application Routes
$router->get('dashboard', 'Dashboard/DashboardController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('dashboard/{action}', 'Dashboard/DashboardController@{action}')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

$router->get('tickets', 'Tickets/TicketController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('tickets/view/{id}', 'Tickets/TicketController@show')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('tickets/search', 'Tickets/TicketController@search')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

$router->get('create_ticket', 'Create_ticket/CreateTicketController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('create_ticket/subcategories/{categoryId}', 'Create_ticket/CreateTicketController@getSubcategories');
$router->get('create_ticket/codes/{subcategoryId}', 'Create_ticket/CreateTicketController@getCodes');
$router->get('create_ticket/coupons/{countryId}', 'Create_ticket/CreateTicketController@getAvailableCoupons');
$router->get('create_ticket/checkTicketExists/{ticketNumber}', 'Create_ticket/CreateTicketController@checkTicketExists');
$router->post('create_ticket/store', 'Create_ticket/CreateTicketController@store')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('create_ticket/holdCoupon', 'Create_ticket/CreateTicketController@holdCoupon');
$router->post('create_ticket/releaseCoupon', 'Create_ticket/CreateTicketController@releaseCoupon');

$router->get('discussions', 'Discussions/DiscussionsController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('discussions/addReply/{id}', 'Discussions/DiscussionsController@addReply');

$router->get('logs', 'Logs/LogsController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('logs/bulk_export', 'Logs/LogsController@bulk_export')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

$router->get('review', 'Review/ReviewController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('review/store', 'Review/ReviewController@store')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

$router->get('referral', 'Referral/ReferralController@index');
$router->get('referral/dashboard', 'Referral/ReferralController@dashboard')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('referral/saveAgentProfile', 'Referral/ReferralController@saveAgentProfile');

$router->get('trips/upload', 'Trips/TripsController@upload')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('trips/process', 'Trips/TripsController@process')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

$router->get('driver/details/{id}', 'Driver/DriverController@details')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('driver/update', 'Driver/DriverController@update')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

// API Routes for AJAX calls
$router->get('api/drivers/search', 'Driver/DriverController@search');
$router->post('calls/assign', 'Driver/DriverController@assign');
$router->post('drivers/assign', 'Driver/DriverController@assign')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('drivers/document/manage', 'Driver/DriverController@manageDocument')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

// $router->get('', 'PagesController@home');

// Calls Routes (Moved to the end to ensure they are not overwritten)
$router->get('calls', 'Calls/CallsController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('calls/getNextDriver', 'Calls/CallsController@getNextDriver');
$router->post('calls/record', 'Calls/CallsController@record')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('calls/history/{id}', 'Calls/CallsController@getHistory');
$router->post('calls/release-hold', 'Calls/CallsController@releaseHold');
$router->get('calls/documents/{id}', 'Calls/DocumentsController@getDriverDocuments');
$router->post('calls/documents/update', 'Calls/CallsController@updateDocuments');
$router->post('calls/assign', 'Calls/AssignmentsController@assign');
$router->post('calls/mark-seen', 'Calls/AssignmentsController@markAsSeen');
$router->post('calls/updateDriverInfo', 'Calls/CallsController@updateDriverInfo');
$router->get('calls/skip/{id}', 'Calls/CallsController@skip');
$router->post('calls/updateDriverAttribute', 'Calls/CallsController@updateDriverAttribute');

// Auth Routes  
$router->get('auth/logout', 'Auth/AuthController@logout');
$router->post('auth/logout', 'Auth/AuthController@logout');

$router->get('reports/referral-visits', 'Reports/ReferralVisits/ReferralVisitsController@index');
$router->get('reports/referral-visits/export-excel', 'Reports/ReferralVisits/ReferralVisitsController@exportExcel');
$router->get('reports/referral-visits/export-json', 'Reports/ReferralVisits/ReferralVisitsController@exportJson');

// Tickets
$router->get('tickets/create', 'CreateTicket/CreateTicketController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->post('tickets/store', 'CreateTicket/CreateTicketController@store')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('ticket/{id}', 'Tickets/TicketController@show'); // Legacy Support
$router->get('ticket', 'Tickets/TicketController@show'); // Legacy Support without ID
$router->get('tickets/view/{id}', 'Tickets/TicketController@show')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('tickets/view', 'Tickets/TicketController@show')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);

// Discussions
$router->get('discussions', 'Discussions/DiscussionsController@index')->middleware(['admin', 'developer', 'quality_manager', 'team_leader']);
$router->get('api/discussions', 'Discussions/DiscussionsController@index');
