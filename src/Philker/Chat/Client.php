<?php namespace Philker\Chat;

use Ratchet\ConnectionInterface;
use Cartalyst\Sentry\Facades\Laravel\Sentry;
use User;

class Client implements ClientInterface {

    protected $socket;

    protected $id;

    protected $uuid;

    protected $name;

    public function getSocket()
    {
        return $this->socket;
    }

    public function setSocket(ConnectionInterface $socket)
    {
        $this->socket = $socket;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        $this->setName(User::find($uuid)->email);
        return $this;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}