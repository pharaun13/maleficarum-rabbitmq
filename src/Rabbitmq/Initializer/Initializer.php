<?php
/**
 * This class carries ioc initialization functionality used by this component.
 */
declare (strict_types=1);

namespace Maleficarum\Rabbitmq\Initializer;

class Initializer {

	/* ------------------------------------ Class Methods START ---------------------------------------- */

	/**
	 * This method will initialize the entire package.
	 * @return string
	 */
	static public function initialize(array $opts = []) : string {
		// load default builder if skip not requested
		$builders = $opts['builders'] ?? [];
		is_array($builders) or $builders = [];
		if (!isset($builders['queue']['skip'])) {
			\Maleficarum\Ioc\Container::register('PhpAmqpLib\Connection\AMQPConnection', function ($dep) {
				if (!array_key_exists('Maleficarum\Config', $dep) || !isset($dep['Maleficarum\Config']['queue'])) {
					throw new \RuntimeException('Impossible to create a PhpAmqpLib\Connection\AMQPConnection object - no queue config found. \Maleficarum\Ioc\Container::get()');
				}
				return new \PhpAmqpLib\Connection\AMQPStreamConnection(
					$dep['Maleficarum\Config']['queue']['broker']['host'],
					$dep['Maleficarum\Config']['queue']['broker']['port'],
					$dep['Maleficarum\Config']['queue']['broker']['username'],
					$dep['Maleficarum\Config']['queue']['broker']['password']
				);
			});

			\Maleficarum\Ioc\Container::register('Maleficarum\Rabbitmq\Connection', function ($dep) {
				return (new \Maleficarum\Rabbitmq\Connection())
					->setConfig($dep['Maleficarum\Config']);
			});
		}

		// load queue object
		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\CommandQueue', \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection'));

		// return initializer name
		return __METHOD__;
	}

	/* ------------------------------------ Class Methods END ------------------------------------------ */

}