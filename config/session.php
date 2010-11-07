<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'user' => array(
		/**
		 * Database settings for session storage.
		 *
		 * string   group  configuation group name
		 * string   table  session table name
		 * integer  gc     number of requests before gc is invoked
		 * columns  array  custom column names
		 */
		'group'   => 'default',
		'table'   => 'sessions',
		'gc'      => 500,
		'columns' => array(
			/**
			 * session_id:  session identifier
			 * last_active: timestamp of the last activity
			 * contents:    serialized session data
			 */
			'session_id'  => 'session_id',
			'last_active' => 'last_active',
			'contents'    => 'contents',
		),
		/**
		 * Колонка для хранения идентификатора зарегистрированного пользователя.
		 */
		'user_column'     => 'user_id',
		/**
		 * Ключ для хранения объекта пользователя. Должен имплементировать 
		 * интерфейс Interface_User
		 * 
		 * @see Interface_User
		 */
		'session_user_key' => 'session_user',
	),
	
);
