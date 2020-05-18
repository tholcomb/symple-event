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

class SubscriberA implements EventSubscriberInterface {
	protected const EVENT = EventA::class;

	public static function getSubscribedEvents()
	{
		return [
			static::EVENT => ['onEvent'],
		];
	}

	public function onEvent(EventA $e) {
		$e->touch();
	}
}