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

use Pimple\Container;
use Pimple\Exception\FrozenServiceException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tholcomb\Symple\Core\AbstractProvider;
use Tholcomb\Symple\Core\UnregisteredProviderException;
use function Tholcomb\Symple\Core\class_implements_interface;

class EventProvider extends AbstractProvider {
	public const KEY_DISPATCHER = 'event.dispatch';
	protected const SUBSCRIBER_PREFIX = 'event.sub.';
	protected const NAME = 'event';

	public function register(Container $c)
	{
		parent::register($c);
		$c[static::KEY_DISPATCHER] = function () {
			return new LazyEventDispatcher();
		};
	}

	public static function getDispatcher(Container $c): LazyEventDispatcher
	{
		if (!isset($c[static::KEY_DISPATCHER])) {
			throw new UnregisteredProviderException(static::class);
		}
		return $c[static::KEY_DISPATCHER];
	}

	public static function addSubscriber(Container $c, string $class, callable $definition): void
	{
		class_implements_interface($class, EventSubscriberInterface::class, true);
		$key = static::SUBSCRIBER_PREFIX . $class;
		$c[$key] = $definition;
		$extension = function (LazyEventDispatcher $dispatcher, Container $c) use ($class, $key) {
			$dispatcher->addLazySubscriber($class, function () use ($c, $key) {
				return $c[$key];
			});
			return $dispatcher;
		};
		try {
			$c->extend(static::KEY_DISPATCHER, $extension);
		} catch (FrozenServiceException $e) {
			$extension($c[static::KEY_DISPATCHER], $c);
		}
	}

	public static function addListener(Container $c, string $eventName, callable $definition, string $method, int $priority = 0): void
	{
		$key = static::SUBSCRIBER_PREFIX . "$eventName.$method.$priority." . microtime();
		$c[$key] = $definition;
		$extension = function (LazyEventDispatcher $dispatcher, Container $c) use ($eventName, $key, $method, $priority) {
			$dispatcher->addLazyListener($eventName, function () use ($c, $key) {
				return $c[$key];
			}, $method, $priority);
			return $dispatcher;
		};
		try {
			$c->extend(static::KEY_DISPATCHER, $extension);
		} catch (FrozenServiceException $e) {
			$extension($c[static::KEY_DISPATCHER], $c);
		}
	}
}