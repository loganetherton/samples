<?php
namespace ValuePad\Debug\Support;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use ValuePad\Debug\Controllers\EmailsController;
use ValuePad\Debug\Controllers\InvoicesController;
use ValuePad\Debug\Controllers\LinkController;
use ValuePad\Debug\Controllers\LiveController;
use ValuePad\Debug\Controllers\LiveEventsController;
use ValuePad\Debug\Controllers\MobileController;
use ValuePad\Debug\Controllers\PushController;
use ValuePad\Debug\Controllers\PushNotificationsController;
use ValuePad\Debug\Controllers\ResetController;
use ValuePad\Support\Shortcut;

class RouteServiceProvider extends ServiceProvider
{
	/**
	 * @param Router $router
	 */
	public function map(Router $router)
	{
		$router->group(['prefix' => Shortcut::prependGlobalRoutePrefix('debug')], function(Router $router){
			$router->get('reset', ResetController::class.'@reset');

			$router->post('link', LinkController::class.'@store');

			$router->get('push', PushController::class.'@index');
			$router->post('push', PushController::class.'@store');
			$router->delete('push', PushController::class.'@destroy');

			$router->get('live', LiveController::class.'@index');
			$router->post('live', LiveController::class.'@store');
			$router->delete('live', LiveController::class.'@destroy');

			$router->get('emails', EmailsController::class.'@index');
			$router->post('emails', EmailsController::class.'@store');
			$router->delete('emails', EmailsController::class.'@destroy');

			$router->get('mobile', MobileController::class.'@index');
			$router->post('mobile', MobileController::class.'@store');
			$router->delete('mobile', MobileController::class.'@destroy');

			$router->get('live/events/order-create/{id}', LiveEventsController::class.'@orderCreate');
			$router->get('live/events/order-update/{id}', LiveEventsController::class.'@orderUpdate');
			$router->get('live/events/order-delete/{id}', LiveEventsController::class.'@orderDelete');
			$router->get('live/events/update-process-status/{id}', LiveEventsController::class.'@updateProcessStatus');
			$router->get('live/events/change-additional-status/{id}', LiveEventsController::class.'@changeAdditionalStatus');
			$router->get('live/events/send-message/{id}', LiveEventsController::class.'@sendMessage');
			$router->get('live/events/bid-request/{id}', LiveEventsController::class.'@bidRequest');
			$router->get('live/events/accept-with-conditions/{id}', LiveEventsController::class.'@acceptWithConditions');
			$router->get('live/events/decline/{id}', LiveEventsController::class.'@decline');
			$router->get('live/events/create-log/{id}', LiveEventsController::class.'@createLog');


			$router->get('push-notifications/create-order/{id}', PushNotificationsController::class.'@createOrder');
			$router->get('push-notifications/update-order/{id}', PushNotificationsController::class.'@updateOrder');
			$router->get('push-notifications/delete-order/{id}', PushNotificationsController::class.'@deleteOrder');
			$router->get('push-notifications/update-process-status/{id}', PushNotificationsController::class.'@updateProcessStatus');
			$router->get('push-notifications/change-additional-status/{id}', PushNotificationsController::class.'@changeAdditionalStatus');
			$router->get('push-notifications/create-document/{id}', PushNotificationsController::class.'@createDocument');
			$router->get('push-notifications/delete-document/{id}', PushNotificationsController::class.'@deleteDocument');
			$router->get('push-notifications/create-additional-document/{id}', PushNotificationsController::class.'@createAdditionalDocument');
			$router->get('push-notifications/delete-additional-document/{id}', PushNotificationsController::class.'@deleteAdditionalDocument');
			$router->get('push-notifications/bid-request/{id}', PushNotificationsController::class.'@bidRequest');
			$router->get('push-notifications/send-message/{id}', PushNotificationsController::class.'@sendMessage');
			$router->get('push-notifications/revision-request/{id}', PushNotificationsController::class.'@revisionRequest');
			$router->get('push-notifications/reconsideration-request/{id}', PushNotificationsController::class.'@reconsiderationRequest');

            $router->post('amcs/{amcId}/invoices', InvoicesController::class.'@store');
		});
	}
}
