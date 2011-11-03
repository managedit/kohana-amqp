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
			'checkout' => array(
				'type'  => AMQP_EX_TYPE_TOPIC,
				'flags' => 0,
				'bindings' => array(
					
				),
			),
			'publish' => array(
				'type'  => AMQP_EX_TYPE_TOPIC,
				'flags' => 0,
				'bindings' => array(
					
				),
			),
			'checkin' => array(
				'type'  => AMQP_EX_TYPE_TOPIC,
				'flags' => 0,
				'bindings' => array(
					
				),
			),
		),
		'queues' => array(
			'checkout' => array(
				'flags'    => AMQP_AUTODELETE,
				'bindings' => array(
					array(
						'exchange'    => 'checkout',
						'routing_key' => '',
					),
				),
			),
			'publish' => array(
				'flags'    => AMQP_AUTODELETE,
				'bindings' => array(
					array(
						'exchange'    => 'publish',
						'routing_key' => '',
					),
				),
			),
			'checkin' => array(
				'flags'    => AMQP_AUTODELETE,
				'bindings' => array(
					array(
						'exchange'    => 'checkin',
						'routing_key' => '',
					),
				),
			),
		),
	),
);