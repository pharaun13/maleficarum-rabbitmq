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
			\Maleficarum\Ioc\Container::register('Maleficarum\Rabbitmq\Connection', function ($dep, $opt) {
				if (isset($opt['useConfig'])) {
					if (!array_key_exists('Maleficarum\Config', $dep) || !isset($dep['Maleficarum\Config']['queue'])) {
						throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - no queue config found. \Maleficarum\Ioc\Container::get()');
					}

					$host = $dep['Maleficarum\Config']['queue']['broker']['host'];
					$port = $dep['Maleficarum\Config']['queue']['broker']['port'];
					$username = $dep['Maleficarum\Config']['queue']['broker']['username'];
					$password = $dep['Maleficarum\Config']['queue']['broker']['password'];
					$queueName = $dep['Maleficarum\Config']['queue']['commands']['queue-name'];
				} else {
					if (!isset($opt['host']) || !mb_strlen($opt['host'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - host not specified. \Maleficarum\Ioc\Container::get()');
					if (!isset($opt['port']) || !mb_strlen($opt['port'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - port not specified. \Maleficarum\Ioc\Container::get()');
					if (!isset($opt['username']) || !mb_strlen($opt['username'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - username not specified. \Maleficarum\Ioc\Container::get()');
					if (!isset($opt['password']) || !mb_strlen($opt['password'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - password not specified. \Maleficarum\Ioc\Container::get()');
					if (!isset($opt['queue-name']) || !mb_strlen($opt['queue-name'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - queue-name not specified. \Maleficarum\Ioc\Container::get()');

					$host = $opt['host'];
					$port = $opt['port'];
					$username = $opt['username'];
					$password = $opt['password'];
					$queueName = $opt['queue-name'];
				}

				return new \Maleficarum\Rabbitmq\Connection\Connection($queueName, $host, $port, $username, $password);
			});
		}

		\Maleficarum\Ioc\Container::registerDependency('Maleficarum\CommandQueue', \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', ['useConfig' => true]));

		// return initializer name
		return __METHOD__;
	}

	/* ------------------------------------ Class Methods END ------------------------------------------ */

}