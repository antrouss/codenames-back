<?php

/**
 * This class is used in order to send easily messages to the socket server.
 *
 * PHP version 7.4
 *
 * @category   Service
 * @author     Antony Roussos <antrouss4@gmail.com>
 * @version    0.0.1
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ZMQService extends BaseService
{

    /**
     * @param \ZMQSocket
     */
    private $socket;

    /**
     * @param EntityManagerInterface $doctrine
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(EntityManagerInterface $doctrine, UserPasswordEncoderInterface $encoder)
    {
        $context = new \ZMQContext();
        $this->socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
        $this->socket->connect("tcp://localhost:5555");
    }

    /**
     * Sends message to the socket.
     *
     * @param string $action the action that the users have subscribed
     * @param array $data
     * 
     * @return boolean
     */
    public function send(string $action, array $data): bool
    {
        $message = [
            'action' => $action,
            'data' => $data,
        ];
        try {
            $this->socket->send(json_encode($message));
            return true;
        } catch (\ZMQSocketException $exception) {
            // log the error
            return false;
        }
    }
}
