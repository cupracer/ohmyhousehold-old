<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SqliteForeignKeyActivationSubscriber implements EventSubscriberInterface
{
    const DB_IDENTIFIER = 'sqlite';

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::postConnect,
        ];
    }

    /**
     * @param ConnectionEventArgs $args
     * @throws Exception
     */
    public function postConnect(ConnectionEventArgs $args)
    {
        $databasePlatformName = strtolower($args->getConnection()->getDatabasePlatform()->getName());

        if (self::DB_IDENTIFIER !== $databasePlatformName) {
            $this->logger->debug(
                sprintf('Database platform is NOT "%s". Not enabling foreign_keys.',
                    self::DB_IDENTIFIER
                ));
            return;
        }else {
            $this->logger->debug(
                sprintf('Database platform is "%s". Enabling foreign_keys.',
                    self::DB_IDENTIFIER
                ));
        }

        $args->getConnection()->executeStatement('PRAGMA foreign_keys = ON;');
    }
}
