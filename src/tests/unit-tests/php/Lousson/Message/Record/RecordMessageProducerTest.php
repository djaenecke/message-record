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
 *  Lousson\Message\Record\RecordMessageProducerTest class definition
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
use Lousson\Message\Record\RecordMessageProducer;

/** Exceptions: */
use Lousson\Record\Error\RecordArgumentError;

/**
 *  A test case for the generic message handler class
 *
 *  @since      lousson/Lousson_Message_Record-0.1.0
 *  @package    org.lousson.message-record
 */
final class RecordMessageProducerTest extends AbstractMessageTest
{
    /**
     *
     */
    public function getMessageProducer(&$handler = null, &$factory = null)
    {
        $handler = $this->getMock(self::I_HANDLER);
        $factory = $this->getMock("Lousson\\Record\\AnyRecordFactory");
        $producer = new RecordMessageProducer($handler, $factory);
        return $producer;
    }

    /**
     *
     */
    public function testProcess()
    {
        $producer = $this->getMessageProducer($handler);
        $expected = array(
            "urn:lousson:test",
            "foo! bar? baz.",
            "text/plain",
        );

        $test = $this;
        $callback = function() use ($test, $expected)  {
            $args = func_get_args();
            $test->assertEquals($expected, $args);
        };

        $handler
            ->expects($this->once())
            ->method("process")
            ->will($this->returnCallback($callback));

        call_user_func_array(array($producer, "process"), $expected);
    }

    /**
     *
     */
    public function testProcessMessage()
    {
        $producer = $this->getMessageProducer($handler);
        $expected = array(
            "urn:lousson:test",
            $this->getMessageMock("foo! bar? baz.", "text/plain"),
        );

        $test = $this;
        $callback = function() use ($test, $expected)  {
            $args = func_get_args();
            $test->assertEquals($expected, $args);
        };

        $handler
            ->expects($this->once())
            ->method("processMessage")
            ->will($this->returnCallback($callback));

        call_user_func_array(array($producer, "processMessage"), $expected);
    }

    /**
     *
     */
    public function testProcessRecord()
    {
        $producer = $this->getMessageProducer($handler, $factory);

        $builder = $this->getMock("Lousson\\Record\\AnyRecordBuilder");
        $builder
            ->expects($this->once())
            ->method("buildRecord")
            ->will($this->returnCallback("json_encode"));

        $factory
            ->expects($this->once())
            ->method("getRecordBuilder")
            ->will($this->returnValue($builder));

        $record = array("foo" => "bar");
        $producer->processRecord("urn:foo:bar", $record);
    }

    /**
     *  @expectedException  Lousson\Message\Error\MessageRuntimeError
     *  @test
     */
    public function testProcessRecordException()
    {
        $producer = $this->getMessageProducer($handler, $factory);

        $factory
            ->expects($this->once())
            ->method("getRecordBuilder")
            ->will($this->throwException(new \DomainException));

        $record = array("foo" => "bar");
        $producer->processRecord("urn:foo:bar", $record);
    }

    /**
     *  @expectedException  Lousson\Message\Error\MessageRuntimeError
     *  @test
     */
    public function testProcessRecordError()
    {
        $producer = $this->getMessageProducer($handler, $factory);

        $factory
            ->expects($this->once())
            ->method("getRecordBuilder")
            ->will($this->throwException(new RecordArgumentError));

        $record = array("foo" => "bar");
        $producer->processRecord("urn:foo:bar", $record);
    }
}

