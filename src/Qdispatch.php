<?php //-->
/*
 * This file is part of the Openovate Labs Inc. framework library
 * (c) 2015 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eve\Framework;

/**
 * Worker Controller
 *
 * @vendor   Eve
 * @package  Framework
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Qdispatch extends \Eve\Framework\Queue
{
    /**
     * Run the jobs
     *
     * @return void
     */
    public function run()
    {
        // notify its up
        echo ' * Worker online. waiting for tasks.', "\n";

        // define job
        $callback = function($message) {
            // notify once a task is received
            echo " * a task is received", "\n";

            // get the data
            $data = json_decode($message->body, true);

            // get application
            $app = ucfirst($data['APPLICATION']);

            //remove starting \\ and append the Job holder
            $app = substr($app, 1).'\\Job\\';

            // extract the job to perform
            $task = str_replace(' ', '\\', ucwords(str_replace('-', ' ', $data['TASK'])));
            $task = $app.$task;

            $attempts = isset($data['RETRY']) ? $data['RETRY'] : 0;

            try {

                // if there's not a class
                if(!class_exists($task)) {
                    //throw
                    throw new Exception('task do not exist', 404);
                }

                //instantiate the job
                $job = new $task();

                // set data and run the process
                $job->setData($data)->run();

                // once done, notify again, that it is done
                echo " * task is done", "\n";

                // set or flag that the worker is free
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            } catch (Exception $e) {
                // once an exception is encountered, notify that task is not done
                echo " * task not done", "\n";
                echo $e->getMessage()."\n";

                if ($attempts && $message->delivery_info['redelivered'] > $attempts) {
                    echo " * Requeueing ....", "\n";
                    // set or flag that the task is not done and the worker is free and requeue task
                    $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag'], false, true);
                }

                // set or flag that the task is not done and the worker is free
                $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);
            }
        };

        // worker consuming tasks from queue
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume('queue', '', false, false, false, false, $callback);

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
}
