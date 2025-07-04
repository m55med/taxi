<?php

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

$router->get('login', 'Auth/AuthController@login');
$router->get('auth/login', 'Auth/AuthController@login');
$router->post('login', 'Auth/AuthController@login');
$router->post('auth/login', 'Auth/AuthController@login');
$router->get('register', 'Auth/AuthController@register');
$router->post('register', 'Auth/AuthController@register');
$router->get('auth/register', 'Auth/AuthController@register');
$router->post('auth/register', 'Auth/AuthController@register');

$router->get('dashboard', 'Dashboard/DashboardController@index');
$router->get('dashboard/{action}', 'Dashboard/DashboardController@{action}');

// Documentation
$router->get('documentation', 'Documentation/DocumentationController@index');

// Knowledge Base
$router->get('knowledge_base', 'knowledge_base/KnowledgeBaseController@index');
$router->get('knowledge_base/create', 'knowledge_base/KnowledgeBaseController@create');
$router->post('knowledge_base/store', 'knowledge_base/KnowledgeBaseController@store');
$router->get('knowledge_base/{id}/show', 'knowledge_base/KnowledgeBaseController@show');
$router->get('knowledge_base/{id}/edit', 'knowledge_base/KnowledgeBaseController@edit');
$router->post('knowledge_base/{id}/update', 'knowledge_base/KnowledgeBaseController@update');
$router->post('knowledge_base/destroy', 'knowledge_base/KnowledgeBaseController@destroy');
$router->get('knowledge_base/findByCode/{ticketCodeId}', 'knowledge_base/KnowledgeBaseController@findByCode');

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

// Notification Routes
$router->get('notifications/getNavNotifications', 'Notifications/NotificationsController@getNavNotifications');
$router->post('notifications/markRead', 'Notifications/NotificationsController@markRead');
$router->get('notifications/history', 'Notifications/NotificationsController@history');

// Calls Routes
$router->get('calls', 'Calls/CallsController@index');
$router->post('calls/record', 'Calls/CallsController@record');
$router->get('calls/skip/{driverId}', 'Calls/CallsController@skip');
$router->post('calls/updateDocuments', 'Calls/CallsController@updateDocuments');

// Driver Routes
$router->get('drivers', 'Driver/DriverController@index');
$router->get('drivers/details/{id}', 'Driver/DriverController@details');
$router->post('drivers/update', 'Driver/DriverController@update');
$router->post('drivers/assign', 'Driver/DriverController@assign');
$router->post('drivers/document/manage', 'Driver/DriverController@manageDocument');
$router->get('drivers/search', 'Driver/DriverController@search');

// Review Routes
$router->get('review/add/{type}/{id}', 'Review/ReviewController@add');
$router->post('review/add/{type}/{id}', 'Review/ReviewController@add');

// Discussion Routes
$router->get('discussions/add/{type}/{id}', 'Discussions/DiscussionsController@add');
$router->post('discussions/add/{type}/{id}', 'Discussions/DiscussionsController@add');
$router->post('discussions/close/{id}', 'Discussions/DiscussionsController@close');

// API Routes
$router->get('drivers/search', 'Driver/DriverController@search');

foreach ($adminControllers as $controller => $uri) {
    $controllerPath = 'Admin/' . $controller;
    $router->get("admin/{$uri}", "{$controllerPath}@index");
    $router->get("admin/{$uri}/create", "{$controllerPath}@create");
    $router->post("admin/{$uri}/store", "{$controllerPath}@store");
    $router->get("admin/{$uri}/edit/{id}", "{$controllerPath}@edit");
    $router->post("admin/{$uri}/update/{id}", "{$controllerPath}@update");
    $router->post("admin/{$uri}/destroy", "{$controllerPath}@destroy");
}

// Add custom routes that don't fit the standard RESTful pattern
$router->post("admin/users/forceLogout", "Admin/UsersController@forceLogout");

// Helper function for report routes to avoid repetition
function mapReportRoutes($router, $uri, $controllerPath)
{
    $controller = 'Reports\\' . str_replace('/', '\\', $controllerPath);

    // Route for index method (e.g., /reports/users)
    $router->get("reports/{$uri}", "{$controller}@index");
    $router->post("reports/{$uri}", "{$controller}@index"); // For forms like search/filter

    // Route for methods with parameters (e.g., /reports/users/view/1)
    // The router is designed to handle dynamic methods and parameters
    $router->get("reports/{$uri}/{action}", "{$controller}@{action}");
    $router->get("reports/{$uri}/{action}/{p1}", "{$controller}@{action}");
    $router->get("reports/{$uri}/{action}/{p1}/{p2}", "{$controller}@{action}");

    $router->post("reports/{$uri}/{action}", "{$controller}@{action}");
    $router->post("reports/{$uri}/{action}/{p1}", "{$controller}@{action}");
    $router->post("reports/{$uri}/{action}/{p1}/{p2}", "{$controller}@{action}");
}

// Mapping all report routes
mapReportRoutes($router, 'users', 'Users/UsersController');
mapReportRoutes($router, 'drivers', 'Drivers/DriversController');
// mapReportRoutes($router, 'calls', 'Calls/CallsController');
// mapReportRoutes($router, 'driver-calls', 'DriverCalls/DriverCallsController');
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


    $router->get("reports/{$uri}", "{$controllerPath}@index");
    $router->post("reports/{$uri}/export", "{$controllerPath}@export");
}

// Main Application Routes
$router->get('dashboard', 'Dashboard/DashboardController@index');

$router->get('tickets', 'Tickets/TicketController@index');
$router->get('tickets/view/{id}', 'Tickets/TicketController@show');
$router->get('tickets/search', 'Tickets/TicketController@search');

$router->get('create_ticket', 'Create_ticket/CreateTicketController@index');
$router->get('create_ticket/subcategories/{categoryId}', 'Create_ticket/CreateTicketController@getSubcategories');
$router->get('create_ticket/codes/{subcategoryId}', 'Create_ticket/CreateTicketController@getCodes');
$router->get('create_ticket/coupons/{countryId}', 'Create_ticket/CreateTicketController@getAvailableCoupons');
$router->get('create_ticket/checkTicketExists/{ticketNumber}', 'Create_ticket/CreateTicketController@checkTicketExists');
$router->post('create_ticket/store', 'Create_ticket/CreateTicketController@store');
$router->post('create_ticket/holdCoupon', 'Create_ticket/CreateTicketController@holdCoupon');
$router->post('create_ticket/releaseCoupon', 'Create_ticket/CreateTicketController@releaseCoupon');

$router->get('discussions', 'Discussions/DiscussionsController@index');
$router->post('discussions/addReply/{id}', 'Discussions/DiscussionsController@addReply');

$router->get('logs', 'Logs/LogsController@index');
$router->post('logs/bulk_export', 'Logs/LogsController@bulk_export');

$router->get('review', 'Review/ReviewController@index');
$router->post('review/store', 'Review/ReviewController@store');

$router->get('referral', 'Referral/ReferralController@index');
$router->get('referral/dashboard', 'Referral/ReferralController@dashboard');
$router->post('referral/saveAgentProfile', 'Referral/ReferralController@saveAgentProfile');

$router->get('trips/upload', 'Trips/TripsController@upload');
$router->post('trips/process', 'Trips/TripsController@process');

$router->get('driver/details/{id}', 'Driver/DriverController@details');
$router->post('driver/update', 'Driver/DriverController@update');

// API Routes for AJAX calls
$router->get('api/drivers/search', 'Driver/DriverController@search');
$router->post('calls/assign', 'Driver/DriverController@assign');
$router->post('drivers/assign', 'Driver/DriverController@assign');
$router->post('drivers/document/manage', 'Driver/DriverController@manageDocument');

// $router->get('', 'PagesController@home');

// Calls Routes (Moved to the end to ensure they are not overwritten)
$router->get('calls', 'Calls/CallsController@index');
$router->get('calls/getNextDriver', 'Calls/CallsController@getNextDriver');
$router->post('calls/record', 'Calls/CallsController@record');
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
