<?php defined('SYSPATH') OR die('No direct access allowed.');

return array (
	'default' => array (
		'credentials' => array(
			'host'     => '127.0.0.1',
			'port'     => 5672,
			'vhost'    => '/',
			'login'    => 'guest',
			'password' => 'guest',
		),
		'exchanges' => array(
//			'my_exchange' => array(
//				'type'  => AMQP_EX_TYPE_TOPIC,
//				'flags' => AMQP_DURABLE,
//			),
		),
		'queues' => array(
//			'my_queue' => array(
//				'flags'    => AMQP_AUTODELETE,
//				'bindings' => array(
//					array(
//						'exchange'    => 'my_exchange',
//						'routing_key' => 'my_routing_key.*',
//					),
//				),
//			),
		),
	),
);
