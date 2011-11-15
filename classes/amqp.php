<?php

class AMQP {
	static $instances = array();
	static $exchange_instances = array();
	static $queue_instances = array();

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
			$config = $this->_config['exchanges'][$name];
			
			$instance = new AMQPExchange($this->_connection);
		
			$instance->declare($name, $config['type'], $config['flags']);
			
//			foreach ($config['bindings'] as $binding)
//			{
//				// Ensure queue is declared
//				$this->queue($binding['queue']);
//				
//				// Bind queue to exchange
//				$instance->bind($binding['queue'], $binding['routing_key']);
//			}
			
			AMQP::$exchange_instances[$name] = $instance;
		}

		return AMQP::$exchange_instances[$name];
	}
	
	public function queue($name)
	{
		if ( ! isset(AMQP::$queue_instances[$name]))
		{
			$config = $this->_config['queues'][$name];
			
			$instance = new AMQPQueue($this->_connection);
		
			$instance->declare($name, $config['flags']);
			
			foreach ($config['bindings'] as $binding)
			{
				// Ensure exchange is declared
				$this->exchange($binding['exchange']);
				
				// Bind queue to exchange
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
	
	public function reconnect($force = FALSE)
	{
		if ( ! $this->is_connected() OR $force)
		{
			Kohana::$log->add(Log::DEBUG, "AMQP: Reconnecting");

			return $this->_connection->reconnect();
		}
			
		
		return TRUE;
	}

	public function publish($exchange, $routing_key, $message, $params = 0, $attributes = array())
	{
		Kohana::$log->add(Log::DEBUG, "AMQP: Publishing to ':exchange' exchange", array(
			':exchange' => $exchange,
		));
		
		$this->reconnect();
		
		return $this->exchange($exchange)->publish($message, $routing_key, $params, $attributes);
	}

	public function consume($queue, $options = NULL)
	{
		Kohana::$log->add(Log::DEBUG, "AMQP: Consuming (consume) from ':queue' queue", array(
			':queue' => $queue,
		));
		
		if ($options === NULL)
		{
			// TODO: Make these configurable
			$options = array(
				'min' => 1,
				'max' => 2,
				'ack' => FALSE,
			);
		}
		
		$this->reconnect();
		
		return $this->queue($queue)->consume($options);
	}
	
	public function get($queue, $flags = NULL)
	{
		Kohana::$log->add(Log::DEBUG, "AMQP: Consuming (get) from :queue", array(
			':queue' => $queue,
		));
		
		$this->reconnect();
		
		return $this->queue($queue)->get($flags);
	}
	
	public function ack($queue, $message, $flags = NULL)
	{
		Kohana::$log->add(Log::DEBUG, "AMQP: Ack'ing message :delivery_tag for :queue", array(
			':delivery_tag' => $message['delivery_tag'],
			':queue'        => $queue,
		));
		
		return $this->queue($queue)->ack($message['delivery_tag'], $flags);
	}
}
