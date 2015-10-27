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
 * @vendor   Eve
 * @package  Framework
 * @author   April Sacil <asacil@openovate.com>
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Queue extends \Eden\Core\Base
{   
    protected $task;
    protected $user;
    protected $type;
    protected $delay;
    protected $appId;
    protected $corrId;
    protected $channel;
    protected $message = array();
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

    public function __construct($host, $port, $username, $password)
    {
        $this->connection = new AMQPConnection($host, $port, $username, $password);
        $this->channel = $this->connection->channel();
    }

    /**
     * Gets connection
     *
     *
     * @return  PhpAmqpLib\Connection\AMQPConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Gets channel
     *
     *
     * @return  PhpAmqpLib\Connection\AMQPConnection
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set Application
     *
     * @param *string $app The application name
     *
     * @return Eve\Framework\Queue
     */
    public function setApplication($app)
    {
        Argument::i()->test(1, 'string');

        $this->application = $app;
        return $this->setData($this->message);
    }

    /**
     * Set Job name
     *
     * @param *string The task name
     *
     * @return Eve\Framework\Queue
     */
    public function setTask($name)
    {
        Argument::i()->test(1, 'string');

        $this->task = $name;
        return $this;
    }

    /**
     * Set data
     *
     * @param *array The data to send
     *
     * @return Eve\Framework\Queue
     */
    public function setData(array $data)
    {
        $data['TASK'] = $this->task;
        $data['RETRY'] = $this->retry;
        $data['APPLICATION'] = $this->application;

        $this->message = $data;
        return $this;
    }

    /**
     * Set retries
     *
     * @param *int $retry The number of retries
     *
     * @return Eve\Framework\Queue
     */
    public function setRetry($retry)
    {
        Argument::i()
            ->test(1, 'int');

        $this->retry = $retry;
        return $this->setData($this->message);
    }

    /**
     * Set persistence
     *
     * @param bool $persistent Whether to be persistent
     *
     * @return Eve\Framework\Queue
     */
    public function setPersistent($persistent)
    {
        Argument::i()->test(1, 'bool');

        $this->persistent = 1;

        if($persistent) {
            $this->persistent = 2;
        }

        return $this;
    }

    /**
     * Set Priority
     *
     * @param *int|string $priority high|medium|low
     *
     * @return Eve\Framework\Queue
     */
    public function setPriority($priority)
    {
        Argument::i()->test(1, 'string', 'number');

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
                $this->priority = $priority;
                break;
        }

        return $this;
    }

    /**
     * Sets Delay in seconds
     *
     * @param *int $delay The delay length
     *
     * @return Eve\Framework\Queue
     */
    public function setDelay($delay)
    {
        Argument::i()->test(1, 'int');

        $this->delay = $delay;
        return $this;
    }

    /**
     * sets expiration in seconds
     *
     * @param *int $seconds The timeout in seconds
     *
     * @return Eve\Framework\Queue
     */
    public function setExpiration($seconds)
    {
        Argument::i()->test(1, 'int');

        $this->expiration = $seconds;
        return $this;
    }

    /**
     * Sets message content type
     * MIME content type of message payload. Has the 
     * same purpose/semantics as HTTP Content-Type header
     *
     * @param *string $type The content type
     *
     * @return Eve\Framework\Queue
     */
    public function setContentType($type)
    {
        Argument::i()->test(1, 'string');

        $this->contentType = $type;
        return $this;
    }

    /**
     * Sets message content encoding
     * MIME content encoding of message payload. Has the
     * same purpose/semantics as HTTP Content-Encoding header.
     *
     * @param *string $encoding The content encoding
     *
     * @return Eve\Framework\Queue
     */
    public function setContentEncoding($encoding)
    {
        Argument::i()->test(1, 'string');

        $this->contentEncoding = $encoding;
        return $this;
    }

    /**
     * Sets application identifier string, 
     * for example, "eventoverse" or "webcrawler"
     *
     * @param *string $appId The application identifier
     *
     * @return Eve\Framework\Queue
     */
    public function setAppId($appId)
    {
        Argument::i()->test(1, 'string');

        $this->appId = $appId;
        return $this;
    }

    /**
     * Sets ID of the message that this message is a reply to. 
     * Applications are encouraged to use this attribute instead 
     * of putting this information into the message payload.
     *
     * @param *string $correlationId the correlation ID
     *
     * @return Eve\Framework\Queue
     */
    public function setCorrelationId($correlationId)
    {
        Argument::i()->test(1, 'string');

        $this->corrId = $corrId;
        return $this;
    }

    /**
     * Sets timestamp in secs
     * Timestamp of the moment when message 
     * was sent, in seconds since the Epoch
     *
     * @param *string $timestamp The timestamp
     *
     * @return Eve\Framework\Queue
     */
    public function setTimestamp($timestamp)
    {
        Argument::i()->test(1, 'string');

        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * Used to name a reply queue
     * Commonly used to name a reply queue 
     * (or any other identifier that helps a consumer 
     * application to direct its response). Applications 
     * are encouraged to use this attribute instead of putting 
     * this information into the message payload.
     *
     * @param *string $replyTo The reply name
     *
     * @return Eve\Framework\Queue
     */
    public function setReply($replyTo)
    {
        Argument::i()->test(1, 'string');

        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * sets Message type as a string. 
     * Recommended to be used by applications instead 
     * of including this information into the message payload.
     *
     * @param *string $type the task type
     *
     * @return Eve\Framework\Queue
     */
    public function setType($type)
    {
        Argument::i()->test(1, 'string');

        $this->type = $type;
        return $this;
    }

    /**
     * sets task/message sender
     * the user used to create the channel
     *
     * @param *string $user The user ID
     *
     * @return Eve\Framework\Queue
     */
    public function setUserId($user)
    {
        Argument::i()->test(1, 'string');

        $this->user = $user;
        return $this;
    }

    /**
     * Process the add queue request
     *
     * @return Eve\Framework\Queue
     */
    public function save()
    {
        // declare queue container
        $this->channel->queue_declare('queue', false, false, false, false);
        $this->channel->exchange_declare('queue-xchnge', 'direct');
        $this->channel->queue_bind('queue', 'queue-xchnge');

        // set message
        $message = new AMQPMessage(json_encode($this->message), $this->setOptions());

        // if no delay queue it now
        if (!$this->delay) {
            // queue it up main queue container
            $this->channel->basic_publish($message, 'queue-xchnge');

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
                'x-expires' => array('I', $this->delay*1000+1000),
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
     * Set options
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