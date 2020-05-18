<?php
/**
 * This file is part of the Symple framework
 *
 * Copyright (c) Tyler Holcomb <tyler@tholcomb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tholcomb\Symple\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function Tholcomb\Symple\Core\class_implements_interface;

class LazyEventDispatcher extends EventDispatcher {
	public function addLazySubscriber(string $class, callable $definition)
	{
		class_implements_interface($class, EventSubscriberInterface::class, true);
		$subscriber = new LazySubscriberWrapper($class, $definition);
		foreach (call_user_func([$class, 'getSubscribedEvents']) as $eventName => $params) {
			if (\is_string($params)) {
				$this->addListener($eventName, [$subscriber, $params]);
			} elseif (\is_string($params[0])) {
				$this->addListener($eventName, [$subscriber, $params[0]], isset($params[1]) ? $params[1] : 0);
			} else {
				foreach ($params as $listener) {
					$this->addListener($eventName, [$subscriber, $listener[0]], isset($listener[1]) ? $listener[1] : 0);
				}
			}
		}
	}

	public function addLazyListener(string $eventName, callable $definition, string $method, int $priority = 0)
	{
		$subscriber = new LazySubscriberWrapper(\stdClass::class, $definition);
		$this->addListener($eventName, [$subscriber, $method], $priority);
	}

	protected function callListeners(iterable $listeners, string $eventName, object $event)
	{
		$stoppable = $event instanceof StoppableEventInterface;

		foreach ($listeners as $listener) {
			if ($stoppable && $event->isPropagationStopped()) {
				break;
			}
			if (is_array($listener) && isset($listener[0])) {
				$wrapper = $listener[0];
				if ($wrapper instanceof LazySubscriberWrapper) {
					if (class_implements_interface($wrapper->getClass(), LazyConditionalSubscriberInterface::class)) {
						$abort = call_user_func([$wrapper->getClass(), 'abortInstantiation'], $event, $eventName);
						if ($abort === true) continue;
					}
					$listener[0] = $wrapper->getListener();
				} elseif ($wrapper instanceof LazyConditionalSubscriberInterface) {
					if ($wrapper::abortInstantiation($event, $eventName) === true) continue;
				}
			}
			$listener($event, $eventName, $this);
		}
	}
}