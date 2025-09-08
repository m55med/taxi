<?php



/** @var \App\Core\Router $router */



// Catch-all route to block direct access to /uploads directory

$router->get('uploads/{any:.*}', 'error/ErrorController@notFound');

$router->post('uploads/{any:.*}', 'error/ErrorController@notFound');



// Redirect old "auth/" routes to new clean routes for SEO and user experience

$requestUriPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$basePath = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');



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

$router->post('logout', 'Auth/AuthController@logout');



// Password Reset Routes

$router->get('forgot-password', 'password/PasswordResetController@showRequestForm');

$router->post('forgot-password', 'password/PasswordResetController@handleRequestForm');

$router->get('reset-password/{token}', 'password/PasswordResetController@showResetForm');

$router->post('reset-password', 'password/PasswordResetController@handleReset');



// Profile routes

$router->get('profile', 'Auth/AuthController@profile');

$router->post('profile/update', 'Auth/AuthController@updateProfile');

// Break Routes
$router->post('breaks/start', 'BreaksController@start');
$router->post('breaks/stop', 'BreaksController@stop');
$router->get('breaks/status', 'BreaksController@status');
$router->get('reports/breaks', 'BreaksController@report')->middleware(['admin', 'developer', 'Quality', 'team_leader']);
$router->get('reports/breaks/user/{id}', 'BreaksController@userReport')->middleware(['admin', 'developer', 'Quality', 'team_leader']);


$router->get('dashboard', 'dashboard/DashboardController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('dashboard/{action}', 'dashboard/DashboardController@{action}')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// Documentation

$router->get('documentation', 'documentation/DocumentationController@index');



// Knowledge Base

$router->get('knowledge_base', 'knowledge_base/KnowledgeBaseController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('knowledge_base/create', 'knowledge_base/KnowledgeBaseController@create')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('knowledge_base/store', 'knowledge_base/KnowledgeBaseController@store')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('knowledge_base/show/{id}', 'knowledge_base/KnowledgeBaseController@show')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('knowledge_base/edit/{id}', 'knowledge_base/KnowledgeBaseController@edit')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('knowledge_base/update/{id}', 'knowledge_base/KnowledgeBaseController@update')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('knowledge_base/destroy', 'knowledge_base/KnowledgeBaseController@destroy')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('knowledge_base/findByCode/{ticketCodeId}', 'knowledge_base/KnowledgeBaseController@findByCode');

$router->get('knowledge_base/search', 'knowledge_base/KnowledgeBaseController@searchApi');



// Help Videos

$router->get("admin/help-videos", "admin/HelpController@index");

$router->post("admin/help-videos/save", "admin/HelpController@save");







// Admin Routes

$adminControllers = [

    'UsersController' => 'users',

];



// Custom routes for BonusController

$router->get("admin/bonus", "admin/BonusController@index")->middleware(['admin', 'developer']);

$router->get("admin/bonus/settings", "admin/BonusController@settings")->middleware(['admin', 'developer']);

$router->post("admin/bonus/updateSettings", "admin/BonusController@updateSettings")->middleware(['admin', 'developer']);

$router->post("admin/bonus/grant", "admin/BonusController@grant")->middleware(['admin', 'developer']);

$router->post("admin/bonus/delete/{id}", "admin/BonusController@delete")->middleware(['admin', 'developer']);



// Custom routes for TelegramSettingsController

$router->get("admin/telegram_settings", "admin/TelegramSettingsController@index");

$router->post("admin/telegram_settings/add", "admin/TelegramSettingsController@add");

$router->post("admin/telegram_settings/delete/{id}", "admin/TelegramSettingsController@delete");



// Custom routes for PlatformsController

$router->get("admin/platforms", "admin/PlatformsController@index");

$router->post("admin/platforms/store", "admin/PlatformsController@store");

$router->post("admin/platforms/update", "admin/PlatformsController@update");

$router->post("admin/platforms/delete/{id}", "admin/PlatformsController@delete");



// Custom routes for RolesController

$router->get("admin/roles", "admin/RolesController@index");

$router->post("admin/roles/store", "admin/RolesController@store");

$router->post("admin/roles/update", "admin/RolesController@update");

$router->post("admin/roles/delete/{id}", "admin/RolesController@delete");



// Custom routes for CarTypesController

$router->get("admin/car_types", "admin/CarTypesController@index");

$router->post("admin/car_types/store", "admin/CarTypesController@store");

$router->post("admin/car_types/delete/{id}", "admin/CarTypesController@delete");



// Custom routes for CountriesController

$router->get("admin/countries", "admin/CountriesController@index");

$router->post("admin/countries/store", "admin/CountriesController@store");

$router->post("admin/countries/delete/{id}", "admin/CountriesController@delete");



// Custom routes for DocumentTypesController

$router->get("admin/document_types", "admin/DocumentTypesController@index");

$router->post("admin/document_types/store", "admin/DocumentTypesController@store");

$router->post("admin/document_types/update", "admin/DocumentTypesController@update");

$router->post("admin/document_types/delete/{id}", "admin/DocumentTypesController@delete");



// Custom routes for PointsController

$router->get("admin/points", "admin/PointsController@index");

$router->post("admin/points/setTicketPoints", "admin/PointsController@setTicketPoints");

$router->post("admin/points/setCallPoints", "admin/PointsController@setCallPoints");



// Routes for UsersController

$router->get("admin/users", "admin/UsersController@index");

$router->get("admin/users/create", "admin/UsersController@create");

$router->post("admin/users/store", "admin/UsersController@store");

$router->get("admin/users/edit/{id}", "admin/UsersController@edit");

$router->post("admin/users/update/{id}", "admin/UsersController@update");

$router->post("admin/users/destroy", "admin/UsersController@destroy");



// VIP Users Routes

$router->get("admin/users/vip", "admin/UsersController@addVip");

$router->post("admin/users/vip/store", "admin/UsersController@storeVip");

$router->post("admin/users/vip/delete/{id}", "admin/UsersController@deleteVip");



// Custom routes for TeamsController

$router->get("admin/teams", "admin/TeamsController@index");

$router->get("admin/teams/create", "admin/TeamsController@create");

$router->post("admin/teams/store", "admin/TeamsController@store");

$router->get("admin/teams/edit/{id}", "admin/TeamsController@edit");

$router->post("admin/teams/update", "admin/TeamsController@update");

$router->post("admin/teams/delete/{id}", "admin/TeamsController@delete");

$router->post("admin/teams/addMember", "admin/TeamsController@addMember");

$router->get("admin/teams/removeMember/{teamId}/{userId}", "admin/TeamsController@removeMember");



$router->get("reports/analytics", "reports/Analytics/AnalyticsController@index");



// Custom routes for TeamMembersController

$router->get("admin/team_members", "admin/TeamMembersController@index");

$router->post("admin/team_members/store", "admin/TeamMembersController@store");

$router->post("admin/team_members/delete/{id}", "admin/TeamMembersController@delete");



// Custom routes for RestaurantsController

$router->get("admin/restaurants", "admin/RestaurantsController@index")->middleware(['admin', 'developer']);

$router->get("admin/restaurants/export/{format}", "admin/RestaurantsController@export")->middleware(['admin', 'developer']);

$router->get("admin/restaurants/delete/{id}", "admin/RestaurantsController@delete")->middleware(['admin', 'developer']);

$router->get("admin/restaurants/edit/{id}", "admin/RestaurantsController@edit")->middleware(['admin', 'developer']);

$router->post("admin/restaurants/update/{id}", "admin/RestaurantsController@update")->middleware(['admin', 'developer']);

$router->get("admin/restaurants/view-pdf/{id}", "admin/RestaurantsController@viewPdf")->middleware(['admin', 'developer']);



// Custom routes for DelegationTypesController

$router->get("delegation-types", "DelegationTypesController@index")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post("delegation-types/create", "DelegationTypesController@create")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post("delegation-types/update", "DelegationTypesController@update")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post("delegation-types/delete", "DelegationTypesController@delete")->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// Custom routes for UserDelegationsController

$router->get("admin/user-delegations", "admin/UserDelegationsController@index")->middleware(['admin', 'developer']);

$router->post("admin/user-delegations/create", "admin/UserDelegationsController@create")->middleware(['admin', 'developer']);

$router->post("admin/user-delegations/delete", "admin/UserDelegationsController@delete")->middleware(['admin', 'developer']);



// Employee Evaluations

$router->get('employee-evaluations', 'EmployeeEvaluationsController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('employee-evaluations/create', 'EmployeeEvaluationsController@create')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('employee-evaluations/delete', 'EmployeeEvaluationsController@delete')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// Custom routes for TicketCategoriesController

$router->get("admin/ticket_categories", "admin/TicketCategoriesController@index");

$router->post("admin/ticket_categories/store", "admin/TicketCategoriesController@store");

$router->post("admin/ticket_categories/update/{id}", "admin/TicketCategoriesController@update");

$router->post("admin/ticket_categories/delete/{id}", "admin/TicketCategoriesController@delete");



// Custom routes for TicketSubCategoriesController

$router->get("admin/ticket_subcategories", "admin/TicketSubCategoriesController@index");

$router->post("admin/ticket_subcategories/store", "admin/TicketSubCategoriesController@store");

$router->post("admin/ticket_subcategories/update/{id}", "admin/TicketSubCategoriesController@update");

$router->post("admin/ticket_subcategories/delete/{id}", "admin/TicketSubCategoriesController@delete");



// Custom routes for TicketCodesController

$router->get("admin/ticket_codes", "admin/TicketCodesController@index");

$router->post("admin/ticket_codes/store", "admin/TicketCodesController@store");

$router->post("admin/ticket_codes/update/{id}", "admin/TicketCodesController@update");

$router->post("admin/ticket_codes/delete/{id}", "admin/TicketCodesController@delete");

$router->get("admin/ticket_codes/get_by_subcategory/{id}", "admin/TicketSubcategoriesController@getByCategory");

$router->get("admin/ticket_subcategories/get_by_category/{id}", "admin/TicketSubcategoriesController@getByCategory");



// Custom routes for CouponsController

$router->get("admin/coupons", "admin/CouponsController@index");

$router->post("admin/coupons", "admin/CouponsController@index");



// Custom routes for PermissionsController

$router->get("admin/permissions", "admin/PermissionsController@index");

$router->post("admin/permissions/toggle", "admin/PermissionsController@toggle");

$router->post("admin/permissions/toggleRolePermission", "admin/PermissionsController@toggleRolePermission");

$router->post("admin/permissions/batchUpdateRolePermissions", "admin/PermissionsController@batchUpdateRolePermissions");

$router->post("admin/permissions/batchUpdateUserPermissions", "admin/PermissionsController@batchUpdateUserPermissions");



// Reports

$router->get("reports/myactivity", "reports/MyActivity/MyActivityController@index");



// Notification Routes

$router->get('notifications', 'notifications/NotificationsController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('notifications/create', 'notifications/NotificationsController@create')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('notifications/store', 'notifications/NotificationsController@store')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('notifications/getNavNotifications', 'notifications/NotificationsController@getNavNotifications');

$router->post('notifications/markRead', 'notifications/NotificationsController@markRead');

$router->get('notifications/history', 'notifications/NotificationsController@history');

$router->get('notifications/readers/{id}', 'notifications/NotificationsController@readers')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// Calls Routes

$router->get('calls', 'calls/CallsController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('calls/record', 'calls/CallsController@record')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('calls/skip/{driverId}', 'calls/CallsController@skip');

$router->get('calls/subcategories/{categoryId}', 'calls/CallsController@getSubcategories');

$router->get('calls/codes/{subcategoryId}', 'calls/CallsController@getCodes');

$router->post('calls/updateDocuments', 'calls/CallsController@updateDocuments');

$router->post('calls/releaseHold', 'calls/CallsController@releaseHold');



// Driver Routes

$router->get('drivers', 'driver/DriverController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('drivers/details/{id}', 'driver/DriverController@details')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('drivers/details', 'driver/DriverController@details')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('drivers/update', 'driver/DriverController@update')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('drivers/assign', 'driver/DriverController@assign')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('drivers/document/manage', 'driver/DriverController@manageDocument')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('drivers/search', 'driver/DriverController@search');

$router->post('drivers/addDocument', 'driver/DriverController@addDocument')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('drivers/updateDocument', 'driver/DriverController@updateDocument')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('drivers/removeDocument', 'driver/DriverController@removeDocument')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// Review Routes

$router->get('review/add/{type}/{id}', 'review/ReviewController@add')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('review/add/{type}/{id}', 'review/ReviewController@add')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// Upload Routes

$router->get('upload', 'upload/UploadController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('upload/process', 'upload/UploadController@process')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// Discussion Routes

$router->get('discussions/add/{type}/{id}', 'discussions/DiscussionsController@add')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('discussions/add/{type}/{id}', 'discussions/DiscussionsController@add')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('discussions/close/{id}', 'discussions/DiscussionsController@close')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('discussions/reopen/{id}', 'discussions/DiscussionsController@reopen')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('discussions/get', 'discussions/DiscussionsController@getDiscussionsApi');

$router->post('discussions/{id}/mark-as-read', 'discussions/DiscussionsController@markAsReadApi');

$router->post('discussions/{id}/replies', 'discussions/DiscussionsController@addReplyApi');



// Ticket search

$router->get('tickets/ajaxSearch', 'tickets/TicketController@ajaxSearch');



// Quality Management Routes

$router->get('quality/reviews', 'quality/QualityController@reviews')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('quality/get_reviews_api', 'quality/QualityController@get_reviews_api');

$router->post('quality/update_review', 'quality/QualityController@update_review');

$router->post('quality/delete_review', 'quality/QualityController@delete_review');

$router->get('quality/discussions', 'quality/QualityController@discussions')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('quality/get_discussions_api', 'quality/QualityController@get_discussions_api');



// Referral Routes

$router->get('referral/dashboard', 'referral/ReferralController@dashboard')->middleware(['admin', 'developer', 'Quality', 'team_leader', 'marketer']);

$router->get('referral/register', 'referral/ReferralController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader', 'marketer']);

$router->post('referral/register', 'referral/ReferralController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader', 'marketer']);

$router->get('referral/marketerDetails/{id}', 'referral/ReferralController@marketerDetails')->middleware(['admin', 'developer', 'Quality', 'team_leader', 'marketer']);

$router->get('referral/editProfile/{id}', 'referral/ReferralController@editProfile')->middleware(['admin', 'developer', 'Quality', 'team_leader', 'marketer']);

$router->post('referral/saveAgentProfile', 'referral/ReferralController@saveAgentProfile')->middleware(['admin', 'developer', 'Quality', 'team_leader', 'marketer']);



// Establishments Routes

$router->get('referral/establishments', 'Establishments/EstablishmentsController@index')->middleware(['admin', 'developer', 'marketer']);

$router->get('referral/establishments/export', 'Establishments/EstablishmentsController@export')->middleware(['admin', 'developer', 'marketer']);

$router->get('referral/establishments/edit/{id}', 'Establishments/EstablishmentsController@edit')->middleware(['admin', 'developer']);

$router->post('referral/establishments/edit/{id}', 'Establishments/EstablishmentsController@edit')->middleware(['admin', 'developer']);

$router->post('referral/establishments/delete/{id}', 'Establishments/EstablishmentsController@delete')->middleware(['admin', 'developer']);



// Establishment Image Routes

$router->get('establishment/image/{imagePath:.*}', 'Establishments/EstablishmentsController@serveImage')->middleware(['admin', 'developer', 'marketer']);



// Listings

$router->get('listings/tickets', 'listings/ListingsController@tickets')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('listings/get_tickets_api', 'listings/ListingsController@get_tickets_api');

$router->get('listings/drivers', 'listings/ListingsController@drivers')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('listings/get_drivers_api', 'listings/ListingsController@get_drivers_api');

$router->post('listings/bulk_update_drivers', 'listings/ListingsController@bulk_update_drivers')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('listings/calls', 'listings/ListingsController@calls')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('listings/get_calls_api', 'listings/ListingsController@get_calls_api');



// API Routes

$router->get('drivers/search', 'driver/DriverController@search');



foreach ($adminControllers as $controller => $uri) {

    // Apply auth middleware to all admin routes

    $controllerPath = 'admin/' . $controller;

    $router->get("admin/{$uri}", "{$controllerPath}@index")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->get("admin/{$uri}/create", "{$controllerPath}@create")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->post("admin/{$uri}/store", "{$controllerPath}@store")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->get("admin/{$uri}/edit/{id}", "{$controllerPath}@edit")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->post("admin/{$uri}/update/{id}", "{$controllerPath}@update")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->post("admin/{$uri}/destroy", "{$controllerPath}@destroy")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

}



// Add custom routes that don't fit the standard RESTful pattern

$router->post("admin/users/forceLogout", "admin/UsersController@forceLogout")->middleware(['admin', 'developer', 'Quality', 'team_leader']);



/*

// Helper function for report routes to avoid repetition

function mapReportRoutes($router, $uri, $controllerPath)

{

    // Apply auth middleware to all report routes within this function

    $controller = 'reports\\' . str_replace('/', '\\', $controllerPath);



    // Route for index method (e.g., /reports/users)

    $router->get("reports/{$uri}", "{$controller}@index")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->post("reports/{$uri}", "{$controller}@index")->middleware(['admin', 'developer', 'Quality', 'team_leader']); // For forms like search/filter



    // Route for methods with parameters (e.g., /reports/users/view/1)

    // The router is designed to handle dynamic methods and parameters

    $router->get("reports/{$uri}/{action}", "{$controller}@{action}")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->get("reports/{$uri}/{action}/{p1}", "{$controller}@{action}")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->get("reports/{$uri}/{action}/{p1}/{p2}", "{$controller}@{action}")->middleware(['admin', 'developer', 'Quality', 'team_leader']);



    $router->post("reports/{$uri}/{action}", "{$controller}@{action}")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->post("reports/{$uri}/{action}/{p1}", "{$controller}@{action}")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->post("reports/{$uri}/{action}/{p1}/{p2}", "{$controller}@{action}")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

}



// Mapping all report routes

mapReportRoutes($router, 'users', 'Users/UsersController');

mapReportRoutes($router, 'drivers', 'Drivers/DriversController');

mapReportRoutes($router, 'assignments', 'Assignments/AssignmentsController');

mapReportRoutes($router, 'analytics', 'Analytics/AnalyticsController');

mapReportRoutes($router, 'documents', 'Documents/DocumentsController');

mapReportRoutes($router, 'coupons', 'Coupons/CouponsController');

mapReportRoutes($router, 'logs', 'Logs/LogsController');

mapReportRoutes($router, 'tickets', 'Tickets/TicketsController');

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

mapReportRoutes($router, 'notifications', 'Notifications/NotificationsController');

*/



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

    'Users'

];



foreach ($reportControllers as $report) {

    // Convert PascalCase to kebab-case for the URI

    $uri = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $report));

    $controllerClass = str_replace('-', '', ucwords($uri, '-'));



    $controllerPath = "reports/{$controllerClass}/{$controllerClass}Controller";



    // Some controllers might have a different name than their folder

    // This is a simplification, might need adjustments for specific reports

           if ($report === 'Tickets' || $report === 'TicketReviews' || $report === 'TicketCoupons' || $report === 'TicketDiscussions' || $report === 'TicketRework' || $report === 'TicketsSummary') {

        $controllerPath = "reports/{$controllerClass}/{$controllerClass}Controller";

        if ($report === 'TicketsSummary') {

            $controllerPath = "reports/{$controllerClass}/TicketsSummaryController";

        }

    }





    $router->get("reports/{$uri}", "{$controllerPath}@index")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->post("reports/{$uri}", "{$controllerPath}@index")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->get("reports/{$uri}/getColumns", "{$controllerPath}@getColumns")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

    $router->post("reports/{$uri}/export", "{$controllerPath}@export")->middleware(['admin', 'developer', 'Quality', 'team_leader']);

}



// Main Application Routes

$router->get('dashboard/{action}', 'dashboard/DashboardController@{action}')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



$router->get('tickets', 'tickets/TicketController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('tickets/view/{id}', 'tickets/TicketController@show')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('tickets/search', 'tickets/TicketController@search')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('tickets/edit/{id}', 'tickets/TicketController@edit')->middleware(['admin', 'developer']);

$router->post('tickets/update/{id}', 'tickets/TicketController@update')->middleware(['admin', 'developer']);



$router->get('create_ticket', 'CreateTicket/CreateTicketController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('create_ticket/subcategories/{categoryId}', 'CreateTicket/CreateTicketController@getSubcategories');

$router->get('create_ticket/codes/{subcategoryId}', 'CreateTicket/CreateTicketController@getCodes');

$router->get('create_ticket/coupons/{countryId}', 'CreateTicket/CreateTicketController@getAvailableCoupons');

$router->get('create_ticket/checkTicketExists/{ticketNumber}', 'CreateTicket/CreateTicketController@checkTicketExists');

$router->post('create_ticket/store', 'CreateTicket/CreateTicketController@store')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('create_ticket/holdCoupon', 'CreateTicket/CreateTicketController@holdCoupon');

$router->post('create_ticket/releaseCoupon', 'CreateTicket/CreateTicketController@releaseCoupon');



$router->get('discussions', 'discussions/DiscussionsController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('discussions/addReply/{id}', 'discussions/DiscussionsController@addReply');



$router->get('logs', 'logs/LogsController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('logs/bulk_export', 'logs/LogsController@bulk_export')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



$router->get('review', 'review/ReviewController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('review/store', 'review/ReviewController@store')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



$router->get('referral', 'referral/ReferralController@index');

$router->get('referral/dashboard', 'referral/ReferralController@dashboard');

$router->post('referral/saveAgentProfile', 'referral/ReferralController@saveAgentProfile');



$router->get('driver/details/{id}', 'driver/DriverController@details')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('driver/update', 'driver/DriverController@update')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// API Routes for AJAX calls

$router->get('api/drivers/search', 'driver/DriverController@search');

$router->post('calls/assign', 'driver/DriverController@assign');

$router->post('drivers/assign', 'driver/DriverController@assign')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('drivers/document/manage', 'driver/DriverController@manageDocument')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// $router->get('', 'PagesController@home');



// Calls Routes (Moved to the end to ensure they are not overwritten)

$router->get('calls', 'calls/CallsController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('calls/getNextDriver', 'calls/CallsController@getNextDriver');

$router->post('calls/record', 'calls/CallsController@record')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('calls/history/{id}', 'calls/CallsController@getHistory');

$router->post('calls/release-hold', 'calls/CallsController@releaseHold');

$router->get('calls/documents/{id}', 'calls/DocumentsController@getDriverDocuments');

$router->post('calls/documents/update', 'calls/CallsController@updateDocuments');

$router->post('calls/assign', 'calls/AssignmentsController@assign');

$router->post('calls/mark-seen', 'calls/AssignmentsController@markAsSeen');

$router->post('calls/updateDriverInfo', 'calls/CallsController@updateDriverInfo');

$router->get('calls/skip/{id}', 'calls/CallsController@skip');

$router->post('calls/updateDriverAttribute', 'calls/CallsController@updateDriverAttribute');



// Auth Routes  

$router->get('auth/logout', 'Auth/AuthController@logout');

$router->post('auth/logout', 'Auth/AuthController@logout');



$router->get('reports/referral-visits', 'reports/ReferralVisits/ReferralVisitsController@index');

$router->get('reports/referral-visits/export-excel', 'reports/ReferralVisits/ReferralVisitsController@exportExcel');

$router->get('reports/referral-visits/export-json', 'reports/ReferralVisits/ReferralVisitsController@exportJson');



// Tickets

$router->get('tickets/create', 'CreateTicket/CreateTicketController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->post('tickets/store', 'CreateTicket/CreateTicketController@store')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('ticket/{id}', 'tickets/TicketController@show'); // Legacy Support

$router->get('ticket', 'tickets/TicketController@show'); // Legacy Support without ID

$router->get('tickets/view/{id}', 'tickets/TicketController@show')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('tickets/view', 'tickets/TicketController@show')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// Create Ticket V2

$router->get('create_ticket/v2', 'CreateTicket/CreateTicketController@v2')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('create_ticket/fetch_trengo_ticket/{ticketId}', 'CreateTicket/CreateTicketController@fetch_trengo_ticket')->middleware(['admin', 'developer', 'Quality', 'team_leader']);



// Discussions

$router->get('discussions', 'discussions/DiscussionsController@index')->middleware(['admin', 'developer', 'Quality', 'team_leader']);

$router->get('api/discussions', 'discussions/DiscussionsController@index');



