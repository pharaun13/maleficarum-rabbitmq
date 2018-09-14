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
     *
     * @param array $opts
     *
     * @return string
     */
    static public function initialize(array $opts = []) : string {
        // load default builder if skip not requested
        $builders = $opts['builders'] ?? [];
        is_array($builders) or $builders = [];
        if (!isset($builders['queue']['skip'])) {
            \Maleficarum\Ioc\Container::registerBuilder('Maleficarum\Rabbitmq\Connection\Connection', function ($dep, $opt) {
                // required params
                if (!isset($opt['host']) || !mb_strlen($opt['host'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - host not specified. \Maleficarum\Ioc\Container::get()');
                if (!isset($opt['port']) || !is_int($opt['port'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - port not specified. \Maleficarum\Ioc\Container::get()');
                if (!isset($opt['username']) || !mb_strlen($opt['username'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - username not specified. \Maleficarum\Ioc\Container::get()');
                if (!isset($opt['password']) || !mb_strlen($opt['password'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - password not specified. \Maleficarum\Ioc\Container::get()');
                if (!isset($opt['queue-name']) || !mb_strlen($opt['queue-name'])) throw new \RuntimeException('Impossible to create a \Maleficarum\Rabbitmq\Connection\Connection object - queue-name not specified. \Maleficarum\Ioc\Container::get()');

                // optional params
                $vhost = (isset($opt['vhost']) && mb_strlen($opt['vhost'])) ? $opt['vhost'] : '/'; 
                
                return new \Maleficarum\Rabbitmq\Connection\Connection($opt['queue-name'], $opt['host'], (int)$opt['port'], $opt['username'], $opt['password'], $vhost);
            });
            
            \Maleficarum\Ioc\Container::registerBuilder('Maleficarum\Rabbitmq\Manager\Manager', function ($dep, $opt) {
                $manager = new \Maleficarum\Rabbitmq\Manager\Manager();
                if (array_key_exists('Maleficarum\Config', $dep) && isset($dep['Maleficarum\Config']['rabbitmq'])) {
                    $config = $dep['Maleficarum\Config']['rabbitmq'];
                    
                    // add persistent connections
                    if (isset($config['persistent']) && is_array($config['persistent'])) {
                        foreach($config['persistent'] as $con_name) {
                            $params = [
                                'host' => $config['broker_'.$con_name]['host'],
                                'port' => (int)$config['broker_'.$con_name]['port'],
                                'username' => $config['broker_'.$con_name]['username'],
                                'password' => $config['broker_'.$con_name]['password'],
                                'queue-name' => $config['broker_'.$con_name]['queue']
                            ];

                            // vhost is optional
                            isset($config['broker_'.$con_name]['vhost']) && mb_strlen($config['broker_'.$con_name]['vhost']) and $params['vhost'] = $config['broker_'.$con_name]['vhost'];
                            
                            $manager->addConnection(
                                \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', $params), 
                                $con_name,
                                \Maleficarum\Rabbitmq\Manager\Manager::CON_MODE_PERSISTENT,
                                (int)$config['broker_'.$con_name]['priority']
                            );
                        }
                    }
                    
                    // add transient connections
                    if (isset($config['transient']) && is_array($config['transient'])) {
                        foreach($config['transient'] as $con_name) {
                            $params = [
                                'host' => $config['broker_'.$con_name]['host'],
                                'port' => (int)$config['broker_'.$con_name]['port'],
                                'username' => $config['broker_'.$con_name]['username'],
                                'password' => $config['broker_'.$con_name]['password'],
                                'queue-name' => $config['broker_'.$con_name]['queue']
                            ];
                            
                            // vhost is optional
                            isset($config['broker_'.$con_name]['vhost']) && mb_strlen($config['broker_'.$con_name]['vhost']) and $params['vhost'] = $config['broker_'.$con_name]['vhost'];
                            
                            $manager->addConnection(
                                \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection\Connection', $params),
                                $con_name,
                                \Maleficarum\Rabbitmq\Manager\Manager::CON_MODE_TRANSIENT,
                                (int)$config['broker_'.$con_name]['priority']
                            );
                        }
                    }
                }
                
                return $manager;
            });

            \Maleficarum\Ioc\Container::register('PhpAmqpLib\Connection\AMQPStreamConnection', function ($dep, $opt) {
                $connection = null;
                $retry_count = 0;

                // attempt to establish the connection - up to 3 times
                while (is_null($connection) && $retry_count++ < 3) {
                    try {
                        $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                            $opt[0], // host
                            $opt[1], // port
                            $opt[2], // username
                            $opt[3], // password
                            $opt[4], // vhost
                            false,
                            'AMQPLAIN',
                            null,
                            'en_US',
                            10.0,
                            10.0
                        );
                    } catch (\Exception $e) {}
                }

                // if the connection is null at this point all retry attempts have failed
                if (is_null($connection)) {
                    throw $e;
                }

                return $connection;
            });
        }

        // add the rabbimq manager as a command route dependency
        \Maleficarum\Ioc\Container::registerShare('Maleficarum\CommandRouter', \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Manager\Manager'));
        
        // return initializer name
        return __METHOD__;
    }

    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
