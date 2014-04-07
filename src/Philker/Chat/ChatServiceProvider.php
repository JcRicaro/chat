<?php namespace Philker\Chat;

use Illuminate\Support\ServiceProvider;
use Evenement\EventEmitter;
use Ratchet\Server\IoServer;

class ChatServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('chat.emitter', function()
		{
			return new EventEmitter();
		});

		$this->app->bind('chat.chat', function()
		{
			return new Chat($this->app->make('chat.emitter'));
		});

		$this->app->bind('chat.client', function()
		{
			return new Client();
		});

		$this->app->bind('chat.command.serve', function()
		{
			return new Command\Serve($this->app->make('chat.chat'));
		});

		$this->commands('chat.command.serve');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
			'chat.chat',
			'chat.command.serve',
			'chat.emitter',
			'chat.server'
			);
	}

}
