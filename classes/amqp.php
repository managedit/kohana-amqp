<?php

class AMQP {
	static $instances = array();
	static $exchange_instances = array();

	protected $_config_group;
	protected $_config;
	protected $_connection;

	/**
	 *
	 * @param string $config_group
	 * @return Stomp
	 */
	public static function instance($config_group = 'default')
	{
		if ( ! isset(AMQP::$instances[$config_group]))
		{
			$instance = new AMQP($config_group);

			AMQP::$instances[$config_group] = $instance;
		}

		return AMQP::$instances[$config_group];
	}
	
	public function exchange($name)
	{
		if ( ! isset(AMQP::$exchange_instances[$name]))
		{
			$config = $this->_config['exchanges'][$exchange];
			
			$instance = new AMQPExchange($this->_connection);
		
			$instance->declare($name, $config['type'], $config['flags']);

			foreach ($config['bindings'] as $binding)
			{
				$instance->bind($binding['queue'], $binding['routing_key']);
			}
			
			AMQP::$exchange_instances[$name] = $instance;
		}

		return AMQP::$exchange_instances[$name];
	}
	
	public function queue($name)
	{
		if ( ! isset(AMQP::$queue_instances[$name]))
		{
			$config = $this->_config['queues'][$exchange];
			
			$instance = new AMQPQueue($this->_connection);
		
			$instance->declare($name, $config['flags']);
			
			foreach ($config['bindings'] as $binding)
			{
				$instance->bind($binding['exchange'], $binding['routing_key']);
			}
		
			AMQP::$queue_instances[$name] = $instance;
		}

		return AMQP::$queue_instances[$name];
	}

	public function __construct($config_group)
	{
		$this->_config_group = $config_group;
		$this->_config = Kohana::$config->load('amqp.'.$config_group);

		$this->_connection = new AMQPConnection($this->_config['credentials']);
	}

	public function __destruct()
	{
		return $this->_connection->disconnect();
	}
	
	public function is_connected()
	{
		return $this->_connection->isConnected();
	}
	
	public function reconnect()
	{
		return $this->_connection->reconnect();
	}

	public function publish($exchange, $routing_key, $message, $params = 0, $attributes = NULL)
	{
		Kohana::$log->add(Log::DEBUG, "AMQP: Publishing to :exchange", array(
			':exchange' => $exchange,
		));
		
		return $this->exchange($exchange)->publish($message, $routing_key, $params, $attributes);
	}

	public function consume($queue, $options = NULL)
	{
		Kohana::$log->add(Log::DEBUG, "AMQP: Consuming (consume) from :queue", array(
			':queue' => $queue,
		));
		
		if ($options === NULL)
		{
			// TODO: Make these configurable
			$options = array(
				'min' => 1,
				'max' => 10,
				'ack' => 0,
			);
		}
		
		return $this->queue($queue)->consume($options);
	}
	
	public function get($queue, $ack = AMQP_NOACK)
	{
		Kohana::$log->add(Log::DEBUG, "AMQP: Consuming (get) from :queue", array(
			':queue' => $queue,
		));
		
		if ($options === NULL)
		{
			$options = array(
				'min' => 1,
				'max' => 1,
				'ack' => 0,
			);
		}
		
		return $this->queue($queue)->consume($options);
	}
}