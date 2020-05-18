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

use Symfony\Contracts\EventDispatcher\Event;

class EventA extends Event {
	public $touched = false;

	public function touch() {
		$this->touched = true;
	}
}