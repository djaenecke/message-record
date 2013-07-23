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
 *  Lousson\Message\Record\RecordMessageConsumer class definition
 *
 *  @package    org.lousson.message-record
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Record;

/** Interfaces: */
use Lousson\Message\AnyMessageFactory;
use Lousson\Message\AnyMessage;
use Lousson\Message\Record\RecordMessageHandler;
use Lousson\Record\AnyRecordFactory;
use Lousson\URI\AnyURIFactory;

/** Dependencies: */
use Lousson\Message\AbstractMessageHandler;

/** Exceptions: */
use Lousson\Message\Error\MessageArgumentError;
use Lousson\Message\Error\MessageRuntimeError;

/**
 *  An abstract record message consumer
 *
 *  The RecordMessageConsumer class is an abstract implementation of the
 *  AnyMessageHandler and RecordMessageHandler interfaces. It allows the
 *  authors of derived classes to create record-processing consumers by
 *  by implementing just one method: processRecord(), as declared in the
 *  RecordMessageHandler interface.
 *
 *  @since      lousson/Lousson_Message_Record-0.1.0
 *  @package    org.lousson.message-record
 */
abstract class RecordMessageConsumer
    extends AbstractMessageHandler
    implements RecordMessageHandler
{
    /**
     *  Create a consumer instance
     *
     *  The constructor requires the caller to provide a record factory
     *  for (de-) serialization. It also allows the provisioning of
     *  custom message and URI factories, to be used instead of the builtin
     *  ones.
     *
     *  @param  AnyRecordFactory    $recordFactory      The record factory
     *  @param  AnyMessageFactory   $messageFactory     The message factory
     *  @param  AnyURIFactory       $uriFactory         The URI factory
     */
    public function __construct(
        AnyRecordFactory $recordFactory,
        AnyMessageFactory $messageFactory = null,
        AnyURIFactory $uriFactory = null
    ) {
        parent::__construct($messageFactory, $uriFactory);
        $this->factory = $recordFactory;
    }

    /**
     *  Process message instances
     *
     *  The processMessage() method is used to invoke the logic that
     *  processes the given $message according to the event $uri.
     *
     *  Note that the default implementation in the RecordMessageConsumer
     *  class will forward the call to the processRecord() method - after
     *  parsing the record from the message's content, according to it's
     *  media type.
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
    final public function processMessage($uri, AnyMessage $message)
    {
        $record = $this->fetchRecord($message);
        $this->processRecord($uri, $record);
    }

    /**
     *  Extract a data record from a message
     *
     *  The fetchRecord() method is used internally to parse the content
     *  of the given $message into a record data array, provided that the
     *  factory returned by getRecordFactory() can provide a parser for
     *  the message's type.
     *
     *  @param  AnyMessage          $message    The message instance
     *
     *  @return array
     *          A data record array is returned on success
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the message content cannot get parsed
     */
    final protected function fetchRecord(AnyMessage $message)
    {
        try {
            $type = $message->getType();
            $parser = $this->factory->getRecordParser($type);
            $content = $message->getContent();
            $record = $parser->parseRecord($content);
        }
        catch (\Exception $error) {
            $class = get_class($error);
            $notice = "Could not parse message: Caught $class";
            $code = MessageRuntimeError::E_INTERNAL_ERROR;
            throw new MessageRuntimeError($notice, $code, $error);
        }

        return $record;
    }
}

