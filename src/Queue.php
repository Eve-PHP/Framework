<?php //-->
/*
 * This file is part of the Openovate Labs Inc. framework library
 * (c) 2015 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eve;

use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Queue Controller
 *
 * @package Eve
 */
class Queue extends Base
{	
	protected $connection;
	protected $channel;
	protected $message;
	protected $priority;
	protected $task;
	protected $delay;
	protected $persistent = 2;
	protected $priority = 'low';

	public function __construct($host, $port, $username, $password, $task = null, $data = array())
	{
		$this->connection = new AMQPConnection($host, $port, $username, $password);
		$this->channel = $this->connection->channel();

		if($task && $data) {
			$this->setTask($task)->setData($data);
		}
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

		$data['task'] = $this->task;
		$this->message = json_encode($data);
		return $this;
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
        		$this->priority = 10;
        		break;

        	case 'medium':
        		$this->priority = 5;
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
		$msg = new AMQPMessage($this->message, array(
			'delivery_mode' => $this->persistent, 
			'priority' => $this->priority));

		// if no delay queue it now
		if($this->delay) {	
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
}