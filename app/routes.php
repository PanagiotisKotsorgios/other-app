<?php
// E:\call_center\app\routes.php

use App\Core\Router;

$router = new Router();

// ── Auth ──────────────────────────────────────────────────
$router->get('/auth/login',    'AuthController@loginForm');
$router->post('/auth/login',   'AuthController@login');
$router->get('/auth/logout',   'AuthController@logout');
$router->get('/auth/profile',  'AuthController@profileForm');
$router->post('/auth/profile', 'AuthController@profileUpdate');
$router->post('/auth/password','AuthController@passwordUpdate');

// ── Admin Dashboard ───────────────────────────────────────
$router->get('/admin/dashboard', 'AdminController@dashboard');

// ── Admin: Callers ────────────────────────────────────────
$router->get('/admin/callers',                   'AdminController@callers');
$router->get('/admin/callers/create',            'AdminController@callerCreate');
$router->post('/admin/callers',                  'AdminController@callerStore');
$router->get('/admin/callers/{id}/edit',         'AdminController@callerEdit');
$router->post('/admin/callers/{id}/update',      'AdminController@callerUpdate');
$router->post('/admin/callers/{id}/delete',      'AdminController@callerDelete');
$router->get('/admin/callers/{id}/stats',        'AdminController@callerStats');

// ── Admin: Developers ─────────────────────────────────────
$router->get('/admin/developers',                'AdminController@developers');
$router->get('/admin/developers/create',         'AdminController@developerCreate');
$router->post('/admin/developers',               'AdminController@developerStore');
$router->get('/admin/developers/{id}/edit',      'AdminController@developerEdit');
$router->post('/admin/developers/{id}/update',   'AdminController@developerUpdate');
$router->post('/admin/developers/{id}/delete',   'AdminController@developerDelete');

// ── Admin: Partners ───────────────────────────────────────
$router->get('/admin/partners',                  'AdminController@partners');
$router->get('/admin/partners/create',           'AdminController@partnerCreate');
$router->post('/admin/partners',                 'AdminController@partnerStore');
$router->get('/admin/partners/{id}/edit',        'AdminController@partnerEdit');
$router->post('/admin/partners/{id}/update',     'AdminController@partnerUpdate');
$router->post('/admin/partners/{id}/delete',     'AdminController@partnerDelete');

// ── Admin: Roles ──────────────────────────────────────────
$router->post('/admin/users/{id}/roles',         'AdminController@assignRoles');

// ── Admin: Businesses ─────────────────────────────────────
$router->get('/admin/businesses',                'BusinessController@index');
$router->get('/admin/businesses/create',         'BusinessController@create');
$router->post('/admin/businesses',               'BusinessController@store');
$router->get('/admin/businesses/{id}',           'BusinessController@show');
$router->get('/admin/businesses/{id}/edit',      'BusinessController@edit');
$router->post('/admin/businesses/{id}/update',   'BusinessController@update');
$router->post('/admin/businesses/{id}/delete',   'BusinessController@delete');
$router->post('/admin/businesses/bulk-assign',   'BusinessController@bulkAssign');

// ── Admin: Import ─────────────────────────────────────────
$router->get('/admin/import',          'ImportController@index');
$router->post('/admin/import/preview', 'ImportController@preview');
$router->post('/admin/import/run',     'ImportController@import');

// ── Admin: Deals ──────────────────────────────────────────
$router->get('/admin/deals',                          'DealController@adminIndex');
$router->get('/admin/deals/{id}',                     'DealController@adminShow');
$router->post('/admin/deals/{id}/approve',            'DealController@approve');
$router->post('/admin/deals/{id}/reject',             'DealController@reject');
$router->post('/admin/deals/{id}/in-progress',        'DealController@setInProgress');
$router->post('/admin/deals/{id}/completed',          'DealController@setCompleted');
$router->post('/admin/deals/{id}/sign-contract',      'DealController@signContract');
$router->post('/admin/deals/{id}/assign-developer',   'DealController@assignDeveloper');
$router->post('/admin/deals/{id}/assign-partner',     'DealController@assignPartner');

// ── Admin: Commissions ────────────────────────────────────
$router->get('/admin/commissions',               'CommissionController@adminIndex');
$router->post('/admin/commissions/{id}/paid',    'CommissionController@markPaid');
$router->post('/admin/commissions/{id}/unpaid',  'CommissionController@markUnpaid');

// ── Admin: Projects ───────────────────────────────────────
$router->get('/admin/projects',                             'ProjectController@adminIndex');
$router->get('/admin/projects/create/{dealId}',             'ProjectController@create');
$router->post('/admin/projects',                            'ProjectController@store');
$router->get('/admin/projects/{id}',                        'ProjectController@adminShow');
$router->get('/admin/projects/{id}/edit',                   'ProjectController@edit');
$router->post('/admin/projects/{id}/update',                'ProjectController@update');
$router->post('/admin/projects/{id}/assign-developer',      'ProjectController@assignDeveloper');
$router->post('/admin/projects/phase/add',                  'ProjectController@addPhase');
$router->post('/admin/projects/phase/{id}/update',          'ProjectController@updatePhase');
$router->post('/admin/projects/phase/{id}/delete',          'ProjectController@deletePhase');
$router->post('/admin/projects/note/add',                   'ProjectController@addNote');

// ── Admin: Documents (Contracts & Invoices) ───────────────
$router->post('/admin/documents/contracts/{dealId}/upload', 'DocumentController@uploadContract');
$router->get('/admin/documents/contracts/{id}/download',    'DocumentController@downloadContract');
$router->post('/admin/documents/contracts/{id}/delete',     'DocumentController@deleteContract');
$router->post('/admin/documents/invoices/{dealId}/upload',  'DocumentController@uploadInvoice');
$router->get('/admin/documents/invoices/{id}/download',     'DocumentController@downloadInvoice');
$router->get('/admin/invoices',                             'DocumentController@adminInvoices');
$router->post('/admin/documents/invoices/{id}/mark-paid',   'DocumentController@markInvoicePaid');
$router->post('/admin/documents/invoices/{id}/update',      'DocumentController@updateInvoice');

// ── Admin: Financials ─────────────────────────────────────
$router->get('/admin/financials',                           'FinancialController@dashboard');
$router->get('/admin/financials/expenses',                  'FinancialController@expenses');
$router->post('/admin/financials/expenses',                 'FinancialController@storeExpense');
$router->post('/admin/financials/expenses/{id}/update',     'FinancialController@updateExpense');
$router->post('/admin/financials/expenses/{id}/delete',     'FinancialController@deleteExpense');

// ── Admin: Messages ───────────────────────────────────────
$router->get('/admin/messages',         'MessageController@inbox');
$router->get('/admin/messages/sent',    'MessageController@sent');
$router->get('/admin/messages/compose', 'MessageController@compose');
$router->post('/admin/messages/send',   'MessageController@send');
$router->get('/admin/messages/{id}',    'MessageController@show');

// ── Developer Dashboard ───────────────────────────────────
$router->get('/developer/dashboard',                        'DeveloperController@dashboard');
$router->get('/developer/projects',                         'DeveloperController@projects');
$router->get('/developer/projects/{id}',                    'DeveloperController@show');
$router->post('/developer/projects/{id}/status',            'ProjectController@updateStatus');
$router->post('/developer/projects/phase/{id}/status',      'ProjectController@updatePhaseStatus');
$router->get('/developer/commissions',                      'DeveloperController@commissions');

// ── Partner Dashboard ─────────────────────────────────────
$router->get('/partner/dashboard',                          'PartnerController@dashboard');
$router->get('/partner/referrals',                          'PartnerController@referrals');
$router->post('/partner/referrals/submit',                  'PartnerController@submitReferral');
$router->get('/partner/commissions',                        'PartnerController@commissions');

// ── Caller Dashboard ──────────────────────────────────────
$router->get('/caller/dashboard', 'CallerController@dashboard');

// ── Caller: Businesses ────────────────────────────────────
$router->get('/caller/businesses',       'BusinessController@myBusinesses');
$router->get('/caller/businesses/{id}',  'BusinessController@show');

// ── Caller: Interactions ──────────────────────────────────
$router->post('/caller/interactions',               'InteractionController@store');
$router->post('/caller/interactions/{id}/delete',   'InteractionController@delete');

// ── Caller: Deals ─────────────────────────────────────────
$router->get('/caller/deals',               'DealController@myDeals');
$router->get('/caller/deals/create/{id}',   'DealController@createForm');
$router->post('/caller/deals',              'DealController@store');

// ── Caller: Commissions ───────────────────────────────────
$router->get('/caller/commissions', 'CommissionController@callerIndex');

// ── Caller: Messages ──────────────────────────────────────
$router->get('/caller/messages',          'MessageController@inbox');
$router->get('/caller/messages/sent',     'MessageController@sent');
$router->get('/caller/messages/compose',  'MessageController@compose');
$router->post('/caller/messages/send',    'MessageController@send');
$router->get('/caller/messages/{id}',     'MessageController@show');

// ── API ───────────────────────────────────────────────────
$router->get('/api/messages/unread', 'MessageController@unreadCount');

return $router;
