<?php namespace Philker\Chat;

use Evenement\EventEmitterInterface;
use Exception;
use Ratchet\ConnectionInterface;
use SplObjectStorage;
use Chat as Ch;

class Chat implements ChatInterface {

	protected $clients;

	protected $emitter;

	protected $id = 1;
	
	public function __construct(EventEmitterInterface $emitter)
	{
		$this->emitter = $emitter;
		$this->clients = new SplObjectStorage();
	}

	public function getClientBySocket(ConnectionInterface $socket)
	{
		foreach($this->clients as $next)
		{
			if($next->getSocket() === $socket)
			{
				return $next;
			}
		}

		return null;
	}

    public function setEmitter(EventEmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    public function getEmitter()
    {
        return $this->emitter;
    }

	public function getClients()
	{
		return $this->clients;
	}

	public function onOpen(ConnectionInterface $socket)
	{
		$client = new Client();
		$client->setId($this->id++);
		$client->setSocket($socket);

		$this->clients->attach($client);
		$this->emitter->emit("open", array($client));
	}

	public function onMessage(ConnectionInterface $socket, $message)
	{
		$client = $this->getClientBySocket($socket);
		$message = json_decode($message);

		switch($message->type)
		{
			case "message":
				$ch = new Ch;
				$ch->users = json_encode($message->recipients);
				$ch->message = $message->data;
				$ch->save();

				$this->emitter->emit("message", array($client, $message->data));	

				foreach($this->clients as $next)
				{
					if(in_array($next->getUuid(),	$message->recipients))
					{
						$next->getSocket()->send(json_encode(array(
							"user" => array(
								"id" => $client->getUuid(),
								"name" => $client->getName()
								),
							"message" => $message
							)));
					}
				}
			break;

			case "login":
				$client->setUuid($message->uuid);
			break;
		}
	}

	public function onClose(ConnectionInterface $socket)
    {
        $client = $this->getClientBySocket($socket);

        if ($client)
        {
            $this->clients->detach($client);
            $this->emitter->emit("close", array($client));
        }
    }

    public function onError(ConnectionInterface $socket, Exception $exception)
    {
        $client = $this->getClientBySocket($socket);

        if ($client)
        {
            $client->getSocket()->close();
            $this->emitter->emit("error", array($client, $exception));
        }
    }


}