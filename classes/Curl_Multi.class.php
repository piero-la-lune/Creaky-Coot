<?php
/**
 * This software is licensed under the MIT License
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
 * @author Keith Minkler <kminkler@gmail.com>
 * @package Curl
 * @copyright Copyright (c) 2010 Keith Minkler
 */

/**
 * Curl_Multi can be used to make asynchronous curl calls.
 *
 * When making an asynchronous call, you first add individual curl handles
 * to the class using the {@link Curl_Multi::addHandle() addHandle()} method.
 *
 * Next you must periodically invoke the {@link Curl_Multi::poll() poll()} command.
 * PHP is (usually) not a multi-threaded language, so curl events will not be processed 
 * in the background, and will only be processed with subsequent calls to poll().  
 * poll() is non-blocking, so it will not interrupt your application if there is 
 * nothing to do.
 *
 * When data is received from the request, the callback method supplied in the 
 * {@link Curl_Multi::addHandle() addHandle()} method will be called.  The prototype
 * for this callback is: callback($curl_result_info, $curl_data, $user_data)
 *
 * Additional requests may be added at any time, they do not have to be added all at once.
 */
class Curl_Multi
{
	/**
	 * The internal storage for each individual curl handle, indexed on handle ID.
	 * @var array $_curls
	 */
	private $_curls = array();

	/**
	 * The curl multi handle.
	 * @var handle $_handle
	 */
	private $_handle = NULL;

	/**
	 * Initializes the curl multi request.
	 */
	public function __construct()
	{
		$this->_handle = curl_multi_init();
	}

	/**
	 * Cleans up the curl multi request
	 *
	 * If individual curl requests were not completed, they will be closed through curl_close()
	 */
	public function __destruct()
	{
		foreach ($this->_curls as $handle_id => $data)
		{
			curl_multi_remove_handle($this->_handle, $data['handle']);
			curl_close($data['handle']);
		}
		curl_multi_close($this->_handle);
	}

	/**
	 * Adds a curl handle to the multi request.
	 *
	 * This function will set CURLOPT_RETURNTRANSFER = TRUE on the curl handle for you.
	 *
	 * This function does not impose any timeouts on your curl requests.  be sure to set appropriate timeouts
	 * using curl_setopt($curl_handle, CURLOPT_TIMEOUT, $timeout) BEFORE calling this function.
	 *
	 * Once this function is called, you should not perform any curl functions on this handle, unless you first
	 * issue a {@link Curl_Multi::removeHandle() removeHandle()} call. 
	 *
	 * Once the request is finished, this handle will be closed through a curl_close() call and cannot be re-used.
	 *
	 * The callback must have the prototype callback(array $curl_result_info, string $curl_data, mixed $callback_data)
	 *
	 * @param handle $curl_handle A curl handle as created by curl_init().
	 * @param callback $callback_function A valid PHP callback as used in call_user_func_array()
	 * @param mixed $callback_data user data which will be passed to the callback function.
	 * @return boolean TRUE on success
	 * @throws Exception on invalid curl handle or callback function
	 */
	public function addHandle($curl_handle, $callback_function, $callback_data, $wait_for_connect = false)
	{
		if (get_resource_type($curl_handle) !== 'curl' || !is_callable($callback_function))
		{
			throw new Exception("Invalid curl handle or callback");
		}

		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);

		$this->_curls[(int)$curl_handle] = array(
			'handle' => $curl_handle,
			'callback' => $callback_function,
			'callback_data' => $callback_data,
		);

		curl_multi_add_handle($this->_handle, $curl_handle);

		if ($wait_for_connect) {
			// Attempt to make the connection and send data,
			// might not work if the connect takes too long.
			$this->poll();
		}

		return TRUE;
	}

	/**
	 * Removes a curl handle from the multi request.
	 *
	 * Once removed, curl_exec() can be called on the handle to complete its transfer.
	 *
	 * @param handle $curl_handle
	 * @return boolean TRUE on success, FALSE if this handle is not recognized or already completed.
	 */
	public function removeHandle($curl_handle)
	{
		if (!isset($this->_curls[(int)$curl_handle]))
		{
			return FALSE;
		}

		curl_multi_remove_handle($this->_handle, $curl_handle);
		unset($this->_curls[(int)$curl_handle]);

		return TRUE;
	}

	/**
	 * Polls (non-blocking) the curl requests for additional data.
	 *
	 * This function must be called periodically while processing other data.  This function is non-blocking
	 * and will return if there is no data ready for processing on any of the internal curl handles.
	 *
	 * @return boolean TRUE if there are transfers still running or FALSE if there is nothing left to do.
	 */
	public function poll()
	{
		$still_running = 0; // number of requests still running.

		do
		{
			$result = curl_multi_exec($this->_handle, $still_running);

			if ($result == CURLM_OK)
			{
				do
				{
					$messages_in_queue = 0;
					$info = curl_multi_info_read($this->_handle, $messages_in_queue);	
					if ($info && isset($info['handle']) && isset($this->_curls[(int)$info['handle']]))
					{
						$callback_info = $this->_curls[(int)$info['handle']];

						$curl_data = curl_multi_getcontent($info['handle']);
						$curl_info = curl_getinfo($info['handle']);

						call_user_func($callback_info['callback'], $curl_info, $curl_data, $callback_info['callback_data']);

						$this->removeHandle($info['handle']);
						curl_close($info['handle']);
					}
				}
				while($messages_in_queue > 0);
			}
		}
		while ($result == CURLM_CALL_MULTI_PERFORM && $still_running > 0);

		// don't trust $still_running, as user may have added more urls
		// in callbacks
		return (boolean)$this->_curls;
	}

	/**
	 * Waits (blocking) on the curl requests for additional data.
	 *
	 * This function is like {@link Curl_Multi::poll() poll()} in that it processes additional
	 * data on the curl requests, however it will block (wait) until something happens.
	 *
	 * This method will not wait until all requests are completed, only until there is at least one request
	 * with additional data to process.  If you wish to wait for all remaining requests to complete, you can
	 * use the {@link Curl_Multi::finish() finish()} method.
	 *
	 * The timeout here is for this function invocation, and is independent of any timeouts which were set
	 * on the individual curl handles using curl_setopt($curl_handle, CURLOPT_TIMEOUT, $timeout).
	 *
	 * @param float $timeout The maximum number of seconds to wait for data to be processed in this invocation.
	 * @return boolean TRUE if there are transfers still running or FALSE if there is nothing left to do.
	 */
	public function select($timeout = 1.0)
	{
		// don't call select unless there is something to poll, otherwise, select(2) will
		// just wait the timeout period for no reason.

		$result = $this->poll();

		if ($result)
		{
			curl_multi_select($this->_handle, $timeout);
			$result = $this->poll();
		}

		return $result;
	}

	/**
	 * Waits (blocking) on the curl requests until all remaining requests have completed.
	 *
	 * @return boolean TRUE
	 */
	public function finish()
	{
		while ($this->select() === TRUE) { /* no op */ }

		return TRUE;
	}
}

?>