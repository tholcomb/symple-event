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

class LazySubscriberWrapper {
	private $class;
	private $instance;

	public function __construct(string $class, callable $instance)
	{
		$this->class = $class;
		$this->instance = $instance;
	}

	public function getClass(): string
	{
		return $this->class;
	}

	public function getListener()
	{
		return call_user_func($this->instance);
	}
}