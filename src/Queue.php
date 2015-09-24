<?php //-->
/*
 * This file is part of the Openovate Labs Inc. framework library
 * (c) 2015 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eve\Framework;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Queue Controller
 *
 * @package Eve
 */
class Queue extends \Eve\Framework\Index
{	
	protected $task;
	protected $user;
	protected $type;
	protected $delay;
	protected $appId;
	protected $corrId;
	protected $channel;
	protected $message;
	protected $replyTo;
	protected $timestamp;
	protected $connection;
	protected $expiration;
	protected $contentType;
	protected $application;
	protected $contentEncoding;
	protected $retry = null;
	protected $persistent = 2;
	protected $priority = 'low';

	public function __construct($host, $port, $username, $password, $task = null, $data = array())
	{
		$this->connection = new AMQPConnection($host, $port, $username, $password);
		$this->channel = $this->connection->channel();

		if ($task && $data) {
			$this->setTask($task)->setData($data);
		}
	}

	/**
     * set Application
     *
     * @param *string
     * @return this
     */
	public function setApplication($app)
	{	
		Argument::i()
            ->test(1, 'string');

		$this->application = $app;
		return $this->setData($this->message);
	}
	
    /**
     * set Job name
     *
     * @param *string
     * @return this
     */
	public function setTask($name)
	{	
		Argument::i()
            ->test(1, 'string');

		$this->task = $name;
		return $this;
	}

    /**
     * send data
     *
     * @param *array
     * @return this
     */
	public function setData($data)
	{	
		Argument::i()
            ->test(1, 'array');        

		$data['TASK'] = $this->task;
		$data['RETRY'] = $this->retry;
		$data['APPLICATION'] = $this->application;

		$this->message = $data;
		return $this;
	}

	/**
     * send data
     *
     * @param *int
     * @return this
     */
	public function setRetry($retry)
	{	
		Argument::i()
            ->test(1, 'int');

		$this->retry = $retry;
		return $this->setData($this->message);
	}

    /**
     * set persistence
     *
     * @param bool
     * @return this
     */
	public function setPersistent($persistent)
	{	
		Argument::i()
            ->test(1, 'bool');

		$this->persistent = $persistent == true ? 2 : 1;
		return $this;
	}
	
    /**
     * add Priority
     *
     * @param *string
     * @return this
     */
	public function setPriority($priority)
	{	
		Argument::i()
            ->test(1, 'string');

        switch ($priority) {
        	case 'high':
        		$this->priority = 9;
        		break;

        	case 'medium':
        		$this->priority = 4;
        		break;

        	case 'low':
        		$this->priority = 0;
        		break;
        	
        	default:
        		break;
        }
		
		return $this;
	}

	/**
     * sets Delay in seconds
     *
     * @param *int
     * @return this
     */
	public function setDelay($delay)
	{
		Argument::i()
            ->test(1, 'int');

        $this->delay = $delay;
        return $this;
	}

	/**
     * sets expiration in seconds
     *
     * @param *int
     * @return this
     */
	public function setExpiration($sec)
	{
		Argument::i()
            ->test(1, 'int');

        $this->expiration = $sec;
        return $this;
	}

	/**
     * sets message content type
     *
     * @param *string
     * @return this
     */
	public function setContentType($type)
	{
		Argument::i()
            ->test(1, 'string');

        $this->contentType = $type;
        return $this;
	}

	/**
     * sets message content encoding
     *
     * @param *string
     * @return this
     */
	public function setContentEncoding($encoding)
	{
		Argument::i()
            ->test(1, 'string');

        $this->contentEncoding = $encoding;
        return $this;
	}

	/**
     * sets application id
     *
     * @param *string
     * @return this
     */
	public function setAppId($appId)
	{
		Argument::i()
            ->test(1, 'string');

        $this->appId = $appId;
        return $this;
	}

	/**
     * sets correlation id
     *
     * @param *string
     * @return this
     */
	public function setCorrelationId($corrId)
	{
		Argument::i()
            ->test(1, 'string');

        $this->corrId = $corrId;
        return $this;
	}

	/**
     * sets timestamp in secs
     *
     * @param *string
     * @return this
     */
	public function setTimestamp($sec)
	{
		Argument::i()
            ->test(1, 'string');

        $this->timestamp = $timestamp;
        return $this;
	}

	/**
     * used to name a reply queue
     *
     * @param *string
     * @return this
     */
	public function setReply($queue)
	{
		Argument::i()
            ->test(1, 'string');

        $this->replyTo = $replyTo;
        return $this;
	}

	/**
     * sets task/message type
     *
     * @param *string
     * @return this
     */
	public function setType($type)
	{
		Argument::i()
            ->test(1, 'string');

        $this->type = $type;
        return $this;
	}

	/**
     * sets task/message sender
     * the user used to create the channel
     *
     * @param *string
     * @return this
     */
	public function setUserId($user)
	{
		Argument::i()
            ->test(1, 'string');

        $this->user = $user;
        return $this;
	}
	
    /**
     * process the add queue request
     *
     * @return this
     */
	public function save()
	{	
		// declare queue container
		$this->channel->queue_declare('queue', false, false, false, false);
		$this->channel->exchange_declare('queue-xchnge', 'direct');
		$this->channel->queue_bind('queue', 'queue-xchnge');

		// set message
		$msg = new AMQPMessage(json_encode($this->message), $this->setOptions());

		// if no delay queue it now
		if (!$this->delay) {	
			// queue it up main queue container
			$this->channel->basic_publish($msg, 'queue-xchnge');

			return $this;
		}

		// the logic if a delay is set is that
		// we place it to a temporary container first, 
		// then forward to main queue container after
		// the expected delayed time passed
		$this->channel->queue_declare(
	        'que-delay',
	        false,
	        false,
	        false,
	        true,
	        true,
	        array(
				// delay in seconds to milliseconds
	            'x-message-ttl' => array('I', $this->delay*1000), 
				// set an expiration to assigned seconds of delay + 1 sec
	            "x-expires" => array("I", $this->delay*1000+1000),  
				// after message expiration in delay queue, move message to the main queue
	            'x-dead-letter-exchange' => array('S', 'queue-xchnge') 
	        )
		);

		$this->channel->exchange_declare('xchnge-delay', 'direct');
		$this->channel->queue_bind('que-delay', 'xchnge-delay');

		// queue it up on delay container
		$this->channel->basic_publish($msg, 'xchnge-delay');

		return $this;
	}

	/**
     * set options
     *
     * @return array options
     */
	public function setOptions() {
		$options = array('delivery_mode' => $this->persistent);

		// if priority is set
		if ($this->priority) {
			$options['priority'] = $this->priority;
		}

		// if expiration is set
		if ($this->expiration) {
			$options['expiration'] = $this->expiration;
		}

		// if content type is set
		if ($this->contentType) {
			$options['content_type'] = $this->contentType;
		}

		// if content encoding is set
		if ($this->contentEncoding) {
			$options['content_encoding'] = $this->contentEncoding;
		}

		// if application id is set
		if ($this->appId) {
			$options['app_id'] = $this->appId;
		}

		// if user id is set
		if ($this->user) {
			$options['user_id'] = $this->user;
		}

		// if timestamp is set
		if ($this->timestamp) {
			$options['timestamp'] = $this->timestamp;
		}

		// if type is set
		if ($this->type) {
			$options['type'] = $this->type;
		}

		// if correlation id is set
		if ($this->corrId) {
			$options['correlation_id'] = $this->corrId;
		}

		// if correlation id is set
		if ($this->replyTo) {
			$options['reply_to'] = $this->replyTo;
		}

		return $options;
	}
}