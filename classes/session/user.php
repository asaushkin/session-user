<?php 
/*
 * Copyright (c) 2010 BUTEO.RU
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 */

/**
 * 
 * Класс для работы с пользовательскими сессиями. Подразумевается, что не
 * может существовать более одной пользовательской сессии одновременно.
 * 
 * alter table `sessions` 
 * 		add `user_id` integer unsigned, 
 * 		add unique `uniq_sess_user_id` (`user_id`);
 * 
 * @author ags
 *
 */
class Session_User extends Session_Database 
{
	protected $_user;
	
	protected $_session_user_key;
	
	protected $_user_column;
	
	public function __construct(array $config = NULL, $id = NULL)
	{
		$this->_session_user_key = $config['session_user_key'];
		$this->_user_column      = $config['user_column'];
		
		parent::__construct($config, $id);
	}

	/**
	 * Загружает пользовательскую сессию. Если сессия уже является 
	 * пользовательской, то загружает ее в случае, если параметр $force
	 * установлен в TRUE
	 * 
	 * Для объединения текущих значений сессии с с загружаемой установите 
	 * параметр $merge в TRUE
	 * 
	 * @param $user  
	 * @param $force
	 * @param $merge
	 */
	public function session_load($user, $force = FALSE, $merge = FALSE)
	{
		if ( $user instanceof Interface_User)
		{
			if ($this->_user == NULL || $force == TRUE)
			{
				$this->set($this->_session_user_key, $user);
				$this->_user = $user;
				
				$data = $this->_data;
				
				// Пытаемся загрузить сессию запрошенного пользователя.
				$this->_read_user($user);

				if ($merge == TRUE)
					$this->_data = arr::merge($data, $this->_data);
			}
		}
		
		return $this;
	}
	
	/**
	 * Читает сессию пользователя и заменяет текущую сессию найденной.
	 * 
	 * @param $user
	 */
	protected function _read_user($user)
	{
		$user_id = NULL;
		
		if ($user instanceof Interface_User)
			$user_id = $user->get_pk();
		else
			throw new Kohana_Exception("parameter \$user not implement Interface_User");

		$query = DB::select($this->_columns['session_id'])
			->from($this->_table)
			->where($this->_user_column, '=', ':id')
			->bind(':id', $user_id);

		$result = $query->execute($this->_db);
		
		if ($result->count() == 1)
		{
			// TODO: Delete current *anonymouse* session. Don't delete if session
			// already have user owner. 
			
			$this->read($result->get('session_id'));
			return TRUE;
		}
		else if ($result->count() == 0)
			return FALSE;
		else
			throw new Kohana_Exception("user session more than 1...");
	}
		
	/**
	 * Загружает сессию по ее идентификатору и пытается загрузить 
	 * пользовательский объект.
	 */
	public function read($id = NULL)
	{
		parent::read($id);
		
		$this->_user =	$this->get($this->_session_user_key); 
	}
	
	protected function _write()
	{
		parent::_write();
		
		DB::update($this->_table)
			->value($this->_user_column, ':user_id')
			->where($this->_columns['session_id'], '=', ':session_id')
			->param(':user_id',    
				$this->_user instanceof Interface_User ?
					$this->_user->get_pk() : NULL)
			->param(':session_id', $this->_session_id)
			->execute($this->_db);
			
		return TRUE;
	}
}

?>