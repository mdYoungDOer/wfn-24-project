<?php

require_once __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use WFN24\Config\Database;

class WFN24WebSocket implements MessageComponentInterface
{
    protected $clients;
    protected $subscriptions;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->subscriptions = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        
        if (!$data) {
            return;
        }

        switch ($data['type']) {
            case 'subscribe_match':
                $this->subscribeToMatch($from, $data['match_id']);
                break;
                
            case 'subscribe_league':
                $this->subscribeToLeague($from, $data['league_id']);
                break;
                
            case 'unsubscribe':
                $this->unsubscribe($from, $data['channel']);
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->removeSubscriptions($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function subscribeToMatch($client, $matchId)
    {
        $channel = "match_{$matchId}";
        $this->subscriptions[$channel][] = $client;
        echo "Client {$client->resourceId} subscribed to match {$matchId}\n";
    }

    protected function subscribeToLeague($client, $leagueId)
    {
        $channel = "league_{$leagueId}";
        $this->subscriptions[$channel][] = $client;
        echo "Client {$client->resourceId} subscribed to league {$leagueId}\n";
    }

    protected function unsubscribe($client, $channel)
    {
        if (isset($this->subscriptions[$channel])) {
            $this->subscriptions[$channel] = array_filter(
                $this->subscriptions[$channel],
                function($c) use ($client) {
                    return $c !== $client;
                }
            );
        }
    }

    protected function removeSubscriptions($client)
    {
        foreach ($this->subscriptions as $channel => $clients) {
            $this->subscriptions[$channel] = array_filter(
                $clients,
                function($c) use ($client) {
                    return $c !== $client;
                }
            );
        }
    }

    public function broadcastToChannel($channel, $data)
    {
        if (isset($this->subscriptions[$channel])) {
            $message = json_encode($data);
            foreach ($this->subscriptions[$channel] as $client) {
                $client->send($message);
            }
        }
    }

    public function broadcastToAll($data)
    {
        $message = json_encode($data);
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }
}

// Create WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WFN24WebSocket()
        )
    ),
    8080
);

echo "WFN24 WebSocket Server started on port 8080\n";
$server->run();
