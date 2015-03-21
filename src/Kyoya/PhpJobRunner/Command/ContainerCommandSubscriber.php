<?php

namespace Kyoya\PhpJobRunner\Command;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContainerCommandSubscriber implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvents::COMMAND => array(
                array('onConsoleCommand', 0)
            ),
            ConsoleEvents::EXCEPTION => array(
                array('onConsoleException', 0)
            ),
        );
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->container);
        }
    }

    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $e = $event->getException();

        /** @var LoggerInterface $errorLogger */
        $errorLogger = $this->container->get('logger.error');
        $errorLogger->log(
            LogLevel::ERROR,
            sprintf('Uncaught Exception %s: "%s" at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
            array('exception' => $e)
        );
    }
}
