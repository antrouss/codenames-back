<?php

namespace Socket;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface
{
    /**
     * A lookup of all the topics clients have subscribed to
     * 
     * Topics accepted:
     *  lobby
     *  game_id
     *  team_id
     *  public_messages
     */
    protected $subscribedTopics = array();

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        echo "subscribed to {$topic->getId()}\n";
        $this->subscribedTopics[$topic->getId()] = $topic;
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        echo "connection closed\n";
    }
    public function onOpen(ConnectionInterface $conn)
    {
        echo "connection opened.\n";
    }
    public function onClose(ConnectionInterface $conn)
    {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onAction($entry)
    {
        $entryData = json_decode($entry, true);
        echo $entryData['action'] . "\n";
        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($entryData['action'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$entryData['action']];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($entryData);
    }
}
