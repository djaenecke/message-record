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
 *  Lousson\Message\Record\RecordMessageConsumerTest class definition
 *
 *  @package    org.lousson.message-record
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Record;

/** Dependencies: */
use Lousson\Message\AbstractMessageTest;
use Lousson\Message\Record\RecordMessageConsumer;

/**
 *  A test case for the generic message handler class
 *
 *  @since      lousson/Lousson_Message_Record-0.1.0
 *  @package    org.lousson.message-record
 */
final class RecordMessageConsumerTest extends AbstractMessageTest
{
    /**
     *
     */
    public function getMessageConsumer(&$factory = null)
    {
        $factory = $this->getMock("Lousson\\Record\\AnyRecordFactory");
        $consumer = $this->getMock(
            "Lousson\\Message\\Record\\RecordMessageConsumer",
            array("processRecord"), array($factory)
        );

        return $consumer;
    }

    /**
     *
     */
    public function testProcessMessage()
    {
        $consumer = $this->getMessageConsumer($factory);
        $callback = function($content) {
            $record = json_decode($content, true);
            return $record;
        };

        $parser = $this->getMock("Lousson\\Record\\AnyRecordParser");
        $parser
            ->expects($this->once())
            ->method("parseRecord")
            ->will($this->returnCallback($callback));

        $factory
            ->expects($this->once())
            ->method("getRecordParser")
            ->will($this->returnValue($parser));

        $content = '{"foo":"bar"}';
        $consumer->process("urn:foo:bar", $content, "text/json");
    }

    /**
     *  @expectedException  Lousson\Message\Error\MessageRuntimeError
     *  @test
     */
    public function testProcessMessageException()
    {
        $consumer = $this->getMessageConsumer($factory);
        $factory
            ->expects($this->once())
            ->method("getRecordParser")
            ->will($this->throwException(new \DomainException));

        $consumer->process("urn:foo:bar", null);
    }
}

