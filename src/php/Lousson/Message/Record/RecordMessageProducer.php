<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 textwidth=75: *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Copyright (c) 2013, The Lousson Project                               *
 *                                                                       *
 * All rights reserved.                                                  *
 *                                                                       *
 * Redistribution and use in source and binary forms, with or without    *
 * modification, are permitted provided that the following conditions    *
 * are met:                                                              *
 *                                                                       *
 * 1) Redistributions of source code must retain the above copyright     *
 *    notice, this list of conditions and the following disclaimer.      *
 * 2) Redistributions in binary form must reproduce the above copyright  *
 *    notice, this list of conditions and the following disclaimer in    *
 *    the documentation and/or other materials provided with the         *
 *    distribution.                                                      *
 *                                                                       *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   *
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     *
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS     *
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE        *
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,            *
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES    *
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR    *
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)    *
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,   *
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)         *
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED   *
 * OF THE POSSIBILITY OF SUCH DAMAGE.                                    *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 *  Lousson\Message\Record\RecordMessageProducer class definition
 *
 *  @package    org.lousson.message-record
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Record;

/** Interfaces: */
use Lousson\Message\AnyMessageHandler;
use Lousson\Message\AnyMessage;
use Lousson\Message\Record\RecordMessageHandler;
use Lousson\Record\AnyRecordFactory;

/** Exceptions: */
use Lousson\Message\Error\MessageRuntimeError;

/**
 *  An abstract record message producer
 *
 *  The RecordMessageProducer class is a decorator for instances of the
 *  AnyMessageHandler interface, extending the API by the processRecord()
 *  method as declared by the RecordMessageHandler interface.
 *
 *  @since      lousson/Lousson_Message_Record-0.1.0
 *  @package    org.lousson.message-record
 */
class RecordMessageProducer implements RecordMessageHandler
{
    /**
     *  The default record type
     *
     *  @var string
     */
    const DEFAULT_RECORD_TYPE = "application/json";

    /**
     *  Create a producer instance
     *
     *  The constructor requires the caller to provide the upstream message
     *  handler and the record factory to use when serializing messages.
     *
     *  @param  AnyMessageHandler   $handler        The message handler
     *  @param  AnyRecordFactory    $factory        The record factory
     */
    public function __construct(
        AnyMessageHandler $handler,
        AnyRecordFactory $factory
    ) {
        $this->handler = $handler;
        $this->factory = $factory;
    }

    /**
     *  Aggregate the record type
     *
     *  The getRecordType() method is a hook for derived classes that
     *  allows the dynamic aggregation of a record media type per $uri.
     *  In it's default implementation, however, it always returns the
     *  default record type: application/json.
     *
     *  @param  string              $uri            The message URI
     *
     *  @return string
     *          An internet media type name is returned on success
     */
    public function getRecordType($uri)
    {
        assert(isset($uri));
        return self::DEFAULT_RECORD_TYPE;
    }

    /**
     *  Obtain a record builder
     *
     *  The getRecordBuilder() method is used to obtain a record builder
     *  for the given media $type from the factory provided at construction
     *  time.
     *
     *  @param  string              $type           The message type
     *
     *  @return \Lousson\Record\AnyRecordBuilder
     *          A record builder instance is returned on success
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \RuntimeException
     *          Raised in case the $type is not associated with a builder
     */
    public function getRecordBuilder($type)
    {
        try {
            $builder = $this->factory->getRecordBuilder($type);
            return $builder;
        }
        catch (\Lousson\Record\AnyRecordException $error) {
            $class = get_class($error);
            $notice = "Could not get record builder: Caught $class";
            $code = $error->getCode();
            throw new MessageRuntimeError($notice, $code, $error);
        }
    }

    /**
     *  Process message data
     *
     *  The process() method is used to invoke the logic that processes
     *  the message $data, a byte sequence of the given mime- or media-
     *  $type, according to the event $uri provided. If the $type is not
     *  provided, implementations should assume "application/octet-stream"
     *  or may attempt to detect it.
     *
     *  @param  string              $uri        The event URI
     *  @param  string              $data       The message data
     *  @param  string              $type       The media type
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case an argument is considered invalid
     *
     *  @throws \RuntimeException
     *          Raised in case an internal error occurred
     */
    public function process($uri, $data, $type = null)
    {
        $this->handler->process($uri, $data, $type);
    }

    /**
     *  Process message instances
     *
     *  The processMessage() method is used to invoke the logic that
     *  processes the given $message according to the event $uri.
     *
     *  @param  string              $uri        The event URI
     *  @param  AnyMessage          $message    The message instance
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case an argument is considered invalid
     *
     *  @throws \RuntimeException
     *          Raised in case an internal error occurred
     */
    public function processMessage($uri, AnyMessage $message)
    {
        $this->handler->processMessage($uri, $message);
    }

    /**
     *  Process message records
     *
     *  The processRecord() method is used to invoke the logic to process
     *  the data $record provided, according to the given event $uri.
     *
     *  Note that the default implementation in the RecordMessageProducer
     *  class does not implement any processing logic beside some basic
     *  validation of the parameters provided. Thus; authors of derived
     *  classes may override this method without an actual invocation of
     *  parent::processRecord().
     *
     *  @param  string              $uri        The message URI
     *  @param  array               $record     The message record
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case an argument is considered invalid
     *
     *  @throws \RuntimeException
     *          Raised in case an internal error occurred
     */
    public function processRecord($uri, array $record)
    {
        try {
            $type = $this->getRecordType($uri);
            $builder = $this->getRecordBuilder($type);
            $data = $builder->buildRecord($record);
        }
        catch (\Lousson\Message\AnyMessageException $error) {
            /* Allowed by the RecordMessageHandler interface */
            throw $error;
        }
        catch (\Exception $error) {
            $class = get_class($error);
            $notice = "Could not process record: Caught $class";
            $code = MessageRuntimeError::E_INTERNAL_ERROR;
            throw new MessageRuntimeError($notice, $code, $error);
        }

        $this->process($uri, $data, $type);
    }

    /**
     *  The upstream message handler
     *
     *  @var \Lousson\Message\AnyMessageHandler
     */
    private $handler;

    /**
     *  The record factory for serialization
     *
     *  @var \Lousson\Record\AnyRecordFactory
     */
    private $factory;
}

