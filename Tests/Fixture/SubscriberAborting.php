<?php
/**
 * This file is part of the Symple framework
 *
 * Copyright (c) Tyler Holcomb <tyler@tholcomb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tholcomb\Symple\Event\Tests\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tholcomb\Symple\Event\LazyConditionalSubscriberInterface;

class SubscriberAborting implements EventSubscriberInterface, LazyConditionalSubscriberInterface {
	public static function getSubscribedEvents()
	{
		$method = ['onA'];
		return [
			EventA::class => $method,
			EventB::class => $method,
		];
	}

	public static function abortInstantiation(object $event, string $eventName): bool
	{
		return $event instanceof EventB;
	}

	public function onA(EventA $event): void
	{
		$event->touch();
	}
}