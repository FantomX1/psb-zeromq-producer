<?php

namespace Prooph\ServiceBus\Message\ZeroMQ\Container;

use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQSocket;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use Interop\Container\ContainerInterface;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQMessageProducer;

class ZeroMQMessageProducerFactory
{
    const DEFAULT_DSN = 'tcp://127.0.0.1:5555';
    const DEFAULT_PERSISTENT_ID = 'prooph';

    /**
     * @param ContainerInterface $container
     * @return ZeroMQMessageProducer
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $this->getConfigFromContainer($container);

        $dsn = isset($config['dsn']) ? $config['dsn'] : self::DEFAULT_DSN;
        $persistentId = isset($config['persistent_id']) ? $config['persistent_id'] : self::DEFAULT_PERSISTENT_ID;
        $rpc = isset($config['rpc']) ? boolval($config['rpc']) : false;

        $socket = $this->makeConnection($persistentId, $dsn, $rpc);
        $messageConverter = $this->makeMessageConverter($config);

        return new ZeroMQMessageProducer($socket, $messageConverter);
    }

    /**
     * Override this method in a custom factory to access a custom config path
     *
     * @param ContainerInterface $container
     * @return array
     */
    protected function getConfigFromContainer(ContainerInterface $container)
    {
        return isset($container->get('config')['prooph']['zeromq_producer'])
            ? $container->get('config')['prooph']['zeromq_producer'] : [] ;
    }

    /**
     * @param string $persistentId
     * @param string $dsn
     * @param bool $rpc
     * @return ZeroMQSocket
     */
    private function makeConnection($persistentId, $dsn, $rpc)
    {
        $mode = $rpc ? ZMQ::SOCKET_REQ : ZMQ::SOCKET_PUSH;
        $context = new ZMQContext;
        $socket = new ZMQSocket($context, $mode, $persistentId);

        return new ZeroMQSocket($socket, $dsn);
    }

    /**
     * @param array $config
     * @return NoOpMessageConverter
     */
    private function makeMessageConverter($config)
    {
        // TODO determine from config.
        return new NoOpMessageConverter;
    }
}
