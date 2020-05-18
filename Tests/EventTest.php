<?php
/**
 * This file is part of the Symple framework
 *
 * Copyright (c) Tyler Holcomb <tyler@tholcomb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tholcomb\Symple\Event\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Tholcomb\Symple\Event\EventProvider;
use Tholcomb\Symple\Event\Tests\Fixture\EventA;
use Tholcomb\Symple\Event\Tests\Fixture\EventB;
use Tholcomb\Symple\Event\Tests\Fixture\Listener;
use Tholcomb\Symple\Event\Tests\Fixture\SubscriberA;
use Tholcomb\Symple\Event\Tests\Fixture\SubscriberAborting;
use Tholcomb\Symple\Event\Tests\Fixture\SubscriberB;

class EventTest extends TestCase {
	public function testTestSubscriber()
	{
		$a = new SubscriberA();
		$ea = new EventA();
		$a->onEvent($ea);
		$this->assertTrue($ea->touched, 'Event not touched');
	}

	private function getContainer(): Container
	{
		$c = new Container();
		$c->register(new EventProvider());

		return $c;
	}

	public function testDispatch() {
		$c = $this->getContainer();
		EventProvider::addSubscriber($c, SubscriberA::class, function () {
			return new SubscriberA();
		});
		$d = EventProvider::getDispatcher($c);
		$e = new EventA();
		$d->dispatch($e);
		$this->assertTrue($e->touched, 'Event not touched');
	}

	public function testLazySubscriber() {
		$c = $this->getContainer();
		$arr = [];
		EventProvider::addSubscriber($c, SubscriberA::class, function () use (&$arr) {
			$arr[SubscriberA::class] = true;
			return new SubscriberA();
		});
		EventProvider::addSubscriber($c, SubscriberB::class, function () use (&$arr) {
			$arr[SubscriberB::class] = true;
			return new SubscriberB();
		});
		$d = EventProvider::getDispatcher($c);
		$d->dispatch(new EventA());
		$this->assertTrue(isset($arr[SubscriberA::class]), 'sub a not loaded');
		$this->assertFalse(isset($arr[SubscriberB::class]), 'sub b loaded early');
		$d->dispatch(new EventB());
		$this->assertTrue(isset($arr[SubscriberA::class]), 'sub b not loaded');
	}

	public function testAbortInstantiation() {
		$c = $this->getContainer();
		$touched = false;
		EventProvider::addSubscriber($c, SubscriberAborting::class, function () use (&$touched) {
			$touched = true;
			return new SubscriberAborting();
		});
		$d = EventProvider::getDispatcher($c);
		$d->dispatch(new EventB());
		$this->assertFalse($touched, 'subscriber was instantiated');
		$a = new EventA();
		$d->dispatch($a);
		$this->assertTrue($touched, 'subscriber was not instantiated');
		$this->assertTrue($a->touched, 'EventA was not touched');
		$b = new EventB();
		$d->dispatch($b);
		$this->assertFalse($b->touched, 'EventB was touched');
	}

	public function testLazyListener() {
		$c = $this->getContainer();
		$touched = false;
		EventProvider::addListener($c, EventA::class, function () use (&$touched) {
			$touched = true;
			return new Listener();
		}, 'onEventA');
		$d = EventProvider::getDispatcher($c);
		$d->dispatch(new EventB());
		$this->assertFalse($touched, 'Listener loaded early');
		$e = new EventA();
		$d->dispatch($e);
		$this->assertTrue($e->touched, 'Event not touched');
		$this->assertTrue($touched, 'Listener supposed not loaded');
	}
}