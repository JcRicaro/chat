<?php namespace Philker\Chat\Command;

use Illuminate\Console\Command;
use Philker\Chat\ChatInterface;
use Philker\Chat\ClientInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Serve extends Command
{
    protected $name = "chat:serve";

    protected $description = "Command description.";

    protected $chat;

    protected function getUserName($client)
    {
        $suffix =  " (" . $client->getId() . ")";

        if($client->getName())
        {
            return $client->getName() . $suffix;
        }

        return "User" . $suffix;
    }

    public function __construct(ChatInterface $chat)
    {
        parent::__construct();

        $this->chat = $chat;

        $open = function(ClientInterface $client)
        {
            $socket = $this->getUserName($client);
            $this->line("<info>" . $socket . " connected.</info>");
        };

        $this->chat->getEmitter()->on("open", function(ClientInterface $client)
        {
            $socket = $this->getUserName($client);
            $this->line("<info>" . $socket . " connected.</info>");
        });

        $this->chat->getEmitter()->on("login", function(ClientInterface $client, $uuid)
        {
            $socket = $this->getUsername($uuid);
        });

        $this->chat->getEmitter()->on("close", function(ClientInterface $client)
        {
            $socket = $this->getUserName($client);
            $this->line("<info>" . $socket . " disconnected.</info>");
        });

        $this->chat->getEmitter()->on("message", function(ClientInterface $client, $message)
        {
            $socket = $this->getUserName($client);
            $this->line("<info>New message from " . $socket . ":</info> <comment>" . $message . "</comment><info><info>.</info>");
        });

        $this->chat->getEmitter()->on("test", function()
        {
            $this->line("<info>Test</info>");
        });


        $this->chat->getEmitter()->on("error", function(ClientInterface $client, $exception)
        {
            $this->line("<info>User encountered an exception:</info> <comment>" . $exception->getMessage() . "</comment><info>.</info>");
        });
    }


    public function fire()
    {
        $port = (integer) $this->option("port");

        if (!$port)
        {
            $port = 7778;
        }

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this->chat
                )
            ),
            $port
        );

        $this->line("<info>Listening on port</info> <comment>" . $port . "</comment><info>.</info>");

        $server->run();
    }

    protected function getOptions()
    {
        return [
            ["port", null, InputOption::VALUE_REQUIRED, "Port to listen on.", null]
        ];
    }
}