<?php namespace Philker\Chat;

use Evenement\EventEmitterInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

interface ChatInterface extends MessageComponentInterface {
	public function getClientBySocket(ConnectionInterface $socket);
    public function getEmitter();
    public function setEmitter(EventEmitterInterface $emitter);
    public function getClients();
}