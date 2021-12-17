<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Laravel\Lumen\Routing\Router;

/** @var Router $router */

$router->get('/', function () use ($router) {
    return "Hello &#128515;!";
});


$router->group(['prefix' => 'v1', 'namespace' => 'V1'], function () use ($router) {
    $router->group(['prefix' => 'auth', 'namespace' => 'Auth'], function () use ($router) {
        $router->post('login', 'AuthController@login');
        $router->post('forgot-password', 'PasswordController@forgotPassword');
        $router->post('reset-password', 'PasswordController@resetPassword');
    });

    $router->group(['prefix' => 'site', 'namespace' => 'Site'], function () use ($router) {
        $router->group(['prefix' => 'structure', 'namespace' => 'Structure'], function () use ($router) {
            $router->get('/filters/brands', ['middleware' => 'cache:120', 'uses' => 'FilterController@getBrands']);
            $router->get('/filters/models', ['middleware' => 'cache:120', 'uses' => 'FilterController@getModels']);
            $router->get('/filters/versions', ['middleware' => 'cache:120', 'uses' => 'FilterController@getVersions']);
            $router->get('/filters/fields', ['middleware' => 'cache:120', 'uses' => 'FilterController@getFields']);
            $router->get('/filters/cities', ['middleware' => 'cache:120', 'uses' => 'FilterController@getCities']);
            $router->get('/filters/years', ['middleware' => 'cache:120', 'uses' => 'FilterController@getYears']);
            $router->get('/filters/prices', ['middleware' => 'cache:120', 'uses' => 'FilterController@getPrices']);

            $router->get('/config-site', ['middleware' => 'cache:120', 'uses' => 'ConfigSiteController@get']);
            $router->get('/partners', ['middleware' => 'cache:120', 'uses' => 'PartnerController@get']);

            $router->get('/home', ['middleware' => 'cache:30', 'uses' => 'HomeController@get']);
            $router->get('/vehicle/{vehicle}', ['middleware' => 'cache:30', 'uses' => 'VehicleController@show']);
            $router->post('/search', ['middleware' => 'cache:30', 'uses' => 'SearchController@get']);
        });

        $router->group(['prefix' => 'sign-up', 'namespace' => 'SignUp'], function () use ($router) {
            $router->post('/register', 'RegisterController@store');
        });

        $router->group(['prefix' => 'logs', 'namespace' => 'Logs'], function () use ($router) {
            $router->post('/events', ['uses' => 'EventController@store']);
        });

        $router->group(['prefix' => 'forms', 'namespace' => 'Forms'], function () use ($router) {
            $router->get('/plans', ['middleware' => 'cache:43200', 'uses' => 'PlanController@search']);
            $router->get('/pfPlans', ['middleware' => 'cache:43200', 'uses' => 'PlanController@searchPfPlans']);
            $router->get('/state', ['middleware' => 'cache:43200', 'uses' => 'StateController@search']);
            $router->get('/city', ['middleware' => 'cache:43200', 'uses' => 'CityController@search']);
            $router->get('/viacep', ['middleware' => 'cache:43200', 'uses' => 'ViaCepController@search']);

            $router->get('/brand', ['middleware' => 'cache:120', 'uses' => 'BrandController@search']);
            $router->get('/model', ['middleware' => 'cache:120', 'uses' => 'ModelController@search']);
            $router->get('/version', ['middleware' => 'cache:120', 'uses' => 'VersionController@search']);

            $router->group(['prefix' => 'offers'], function () use ($router) {
                $router->post('/financing', 'OfferController@financing');
                $router->post('/proposal', 'OfferController@proposal');
                $router->post('/seePhone', 'OfferController@seePhone');
                $router->post('/sellVehicle', 'OfferController@sellVehicle');
                $router->post('/banner', 'OfferController@banner');
            });
        });
    });

    $router->group([
        'prefix' => 'publisher',
        'middleware' => ['auth', 'role:store,person'],
        'namespace' => 'Publisher'
    ], function () use ($router) {
        $router->group(['prefix' => 'dashboard'], function () use ($router) {
            $router->get('/', 'DashBoardController@searchByToken');
        });

        $router->group(['prefix' => 'accounts'], function () use ($router) {
            $router->get('/', 'AccountController@showByToken');
            $router->put('/', 'AccountController@updateByToken');
            $router->delete('/', 'AccountController@destroyByToken');
        });

        $router->group(['prefix' => 'myplan'], function () use ($router) {
            $router->get('/', 'AccountController@showPlanByToken');
            $router->put('/', 'AccountController@updatePlanByToken');
        });

        $router->group(['prefix' => 'vehicles'], function () use ($router) {
            $router->group(['prefix' => 'fields'], function () use ($router) {
                $router->get('/', 'VehicleFieldController@search');
            });
            $router->group(['prefix' => 'brands'], function () use ($router) {
                $router->get('/', 'BrandController@search');
            });
            $router->group(['prefix' => 'models'], function () use ($router) {
                $router->get('/', 'ModelController@search');
            });
            $router->group(['prefix' => 'versions'], function () use ($router) {
                $router->get('/', 'VersionController@search');
            });

            $router->group(['prefix' => 'files'], function () use ($router) {
                $router->post('/', 'VehicleFileController@storeByToken');
                $router->put('/orders', 'VehicleFileController@ordersByToken');
                $router->delete('/{id}', 'VehicleFileController@destroyByToken');
            });

            $router->get('/', 'VehicleController@searchByToken');
            $router->get('/{id}', 'VehicleController@showByToken');
            $router->post('/', 'VehicleController@storeByToken');
            $router->put('/{id}', 'VehicleController@updateByToken');
            $router->put('/spotlight/{id}', 'VehicleController@updateSpotlightByToken');
            $router->delete('/{id}', 'VehicleController@destroyByToken');
        });

        $router->group(['prefix' => 'payment-methods'], function () use ($router) {
            $router->get('/', 'PaymentMethodController@searchByToken');
            $router->post('/', 'PaymentMethodController@storeByToken');
            $router->put('/', 'PaymentMethodController@updateByToken');
            $router->delete('/{id}', 'PaymentMethodController@destroyByToken');
        });

        $router->group(['prefix' => 'billing'], function () use ($router) {
            $router->get('/', 'BillingController@searchByToken');
            $router->get('/extract', 'BillingController@extractByToken');
            $router->get('/reprocess', 'BillingController@reprocessByToken');
            $router->get('/reprocessPF/{vehicleId}', 'BillingController@reprocessPFByToken');
        });

        $router->group(['prefix' => 'offers'], function () use ($router) {
            $router->get('/', 'OfferController@searchByToken');
            $router->get('/{id}', 'OfferController@showByToken');
        });
    });

    $router->group([
        'prefix' => 'admin',
        'middleware' => ['auth', 'role:admin'],
        'namespace' => 'Admin'
    ], function () use ($router) {

        $router->group(['prefix' => 'dashboard', 'namespace' => 'DashBoard'], function () use ($router) {
            $router->get('/', 'DashBoardController@search');
        });

        $router->group(['prefix' => 'system', 'namespace' => 'System'], function () use ($router) {
            $router->group(['prefix' => 'brands'], function () use ($router) {
                $router->get('/', 'BrandController@search');
                $router->get('/{id}', 'BrandController@show');
                $router->post('/', 'BrandController@store');
                $router->put('/{id}', 'BrandController@update');
                $router->delete('/{id}', 'BrandController@destroy');
            });

            $router->group(['prefix' => 'models'], function () use ($router) {
                $router->get('/', 'ModelController@search');
                $router->get('/{id}', 'ModelController@show');
                $router->post('/', 'ModelController@store');
                $router->put('/{id}', 'ModelController@update');
                $router->delete('/{id}', 'ModelController@destroy');
            });

            $router->group(['prefix' => 'versions'], function () use ($router) {
                $router->get('/', 'VersionController@search');
                $router->get('/{id}', 'VersionController@show');
                $router->post('/', 'VersionController@store');
                $router->put('/{id}', 'VersionController@update');
                $router->delete('/{id}', 'VersionController@destroy');
            });

            $router->group(['prefix' => 'fields'], function () use ($router) {
                $router->get('/', 'VehicleFieldController@search');
                $router->get('/{id}', 'VehicleFieldController@show');
                $router->post('/', 'VehicleFieldController@store');
                $router->put('/{id}', 'VehicleFieldController@update');
                $router->delete('/{id}', 'VehicleFieldController@destroy');
            });
        });

        $router->group(['prefix' => 'billing', 'namespace' => 'Billing'], function () use ($router) {
            $router->get('/', 'BillingController@search');
        });

        $router->group(['prefix' => 'site', 'namespace' => 'Site'], function () use ($router) {
            $router->group(['prefix' => 'contacts'], function () use ($router) {
                $router->get('/', 'SiteContactController@search');
                $router->get('/{id}', 'SiteContactController@show');
                $router->put('/', 'SiteContactController@update');
            });

            $router->group(['prefix' => 'offers'], function () use ($router) {
                $router->get('/', 'OfferController@search');
                $router->get('/{id}', 'OfferController@show');
            });

            $router->group(['prefix' => 'banners'], function () use ($router) {
                $router->get('/', 'BannerController@search');
                $router->get('/{id}', 'BannerController@show');
                $router->post('/', 'BannerController@store');
                $router->put('/orders', 'BannerController@orders');
                $router->put('/{id}', 'BannerController@update');
                $router->delete('/{id}', 'BannerController@destroy');
            });
        });

        $router->group(['prefix' => 'publishers', 'namespace' => 'Publisher'], function () use ($router) {
            $router->get('/', 'PublisherController@search');
            $router->get('/{id}', 'PublisherController@show');
            $router->post('/', 'PublisherController@store');
            $router->put('/{id}', 'PublisherController@update');
            $router->delete('/{id}', 'PublisherController@destroy');
        });
    });

    $router->group([
        'prefix' => 'external',
        'middleware' => ['auth-external'],
        'namespace' => 'External'
    ], function () use ($router) {
        $router->group(['prefix' => 'properties'], function () use ($router) {
            $router->get('fuel', 'PropertyController@fuel');
            $router->get('color', 'PropertyController@color');
            $router->get('accessory', 'PropertyController@accessory');
            $router->get('bodyType', 'PropertyController@bodyType');
            $router->get('transmission', 'PropertyController@transmission');
            $router->get('vehicleType', 'PropertyController@vehicleType');
            $router->get('brand', 'PropertyController@brand');
            $router->get('model', 'PropertyController@model');
            $router->get('version', 'PropertyController@version');
            $router->get('state', 'PropertyController@state');
            $router->get('city', 'PropertyController@city');
        });

        $router->group(['prefix' => 'plan'], function () use ($router) {
            $router->get('/', 'PlanController@show');
        });

        $router->group(['prefix' => 'vehicle'], function () use ($router) {
            $router->get('/', 'VehicleController@search');
            $router->get('/{id}', 'VehicleController@show');
            $router->post('/', 'VehicleController@store');
            $router->put('/{id}', 'VehicleController@update');
            $router->delete('/{id}', 'VehicleController@destroy');
            // Images
            $router->post('image/{id}', 'VehicleFileController@store');
            $router->put('image/{id}', 'VehicleFileController@update');
            $router->delete('image/{vehicle_id}/{id}', 'VehicleFileController@destroy');
        });
    });

    $router->group(['prefix' => 'implantation', 'namespace' => 'Implantation'], function () use ($router) {
        $router->group(['prefix' => 'pagarme'], function () use ($router) {
            $router->get('/storePlans', 'PagarMeController@storePlans');
            $router->get('/listPlans', 'PagarMeController@listPlans');
        });
    });

    $router->group(['prefix' => 'pagarme', 'namespace' => 'Pagarme'], function () use ($router) {
        $router->get('/teste', 'PostBackController@teste');
        $router->post('/transaction', 'PostBackController@transaction');
        $router->get('/list-postback/{model}/{model_id}', 'PostBackController@getPostBack');
    });

    $router->group(['prefix' => 'teste', 'namespace' => 'Testes'], function () use ($router) {
        $router->post('/post', 'ExampleController@post');
        $router->get('/get', 'ExampleController@get');
        $router->get('/server-info', 'ExampleController@phpInfo');
    });
});
