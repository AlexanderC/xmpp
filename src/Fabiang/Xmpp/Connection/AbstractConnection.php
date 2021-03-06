<?php

/**
 * Copyright 2014 Fabian Grutschus. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of the copyright holders.
 *
 * @author    Fabian Grutschus <f.grutschus@lubyte.de>
 * @copyright 2014 Fabian Grutschus. All rights reserved.
 * @license   BSD
 * @link      http://github.com/fabiang/xmpp
 */

namespace Fabiang\Xmpp\Connection;

use Fabiang\Xmpp\Stream\XMLStream;
use Fabiang\Xmpp\EventListener\EventListenerInterface;
use Fabiang\Xmpp\Event\EventManager;
use Fabiang\Xmpp\Event\EventManagerInterface;
use Fabiang\Xmpp\EventListener\BlockingEventListenerInterface;
use Fabiang\Xmpp\Options;
use Psr\Log\LogLevel;

/**
 * Connection test double.
 *
 * @package Xmpp\Connection
 */
abstract class AbstractConnection implements ConnectionInterface
{

    /**
     *
     * @var XMLStream
     */
    protected $outputStream;

    /**
     *
     * @var \XMLStream
     */
    protected $inputStream;

    /**
     * Options.
     *
     * @var Options
     */
    protected $options;

    /**
     * Eventmanager.
     *
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Event listeners.
     *
     * @var EventListenerInterface[]
     */
    protected $listeners = array();

    /**
     * Connected.
     *
     * @var boolean
     */
    protected $connected = false;

    /**
     *
     * @var boolean
     */
    protected $ready = false;

    /**
     * {@inheritDoc}
     */
    public function getOutputStream()
    {
        if (null === $this->outputStream) {
            $this->outputStream = new XMLStream();
        }

        return $this->outputStream;
    }

    /**
     * {@inheritDoc}
     */
    public function getInputStream()
    {
        if (null === $this->inputStream) {
            $this->inputStream = new XMLStream();
        }

        return $this->inputStream;
    }

    /**
     * {@inheritDoc}
     */
    public function setOutputStream(XMLStream $outputStream)
    {
        $this->outputStream = $outputStream;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setInputStream(XMLStream $inputStream)
    {
        $this->inputStream = $inputStream;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addListener(EventListenerInterface $eventListener)
    {
        $this->listeners[] = $eventListener;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * {@inheritDoc}
     */
    public function isReady()
    {
        return $this->ready;
    }

    /**
     * {@inheritDoc}
     */
    public function setReady($flag)
    {
        $this->ready = (bool) $flag;
        return $this;
    }

    /**
     * Reset streams.
     *
     * @return void
     */
    public function resetStreams()
    {
        $this->getInputStream()->reset();
        $this->getOutputStream()->reset();
    }

    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * {@inheritDoc}
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Get listeners.
     *
     * @return EventListenerInterface
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(Options $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Call logging event.
     *
     * @param string  $message Log message
     * @param integer $level   Log level
     * @return void
     */
    protected function log($message, $level = LogLevel::DEBUG)
    {
        $this->getEventManager()->trigger('logger', $this, array($message, $level));
    }

    /**
     * Check blocking event listeners.
     *
     * @return boolean
     */
    protected function checkBlockingListeners()
    {
        $blocking = false;
        foreach ($this->listeners as $listerner) {
            $instanceof = $listerner instanceof BlockingEventListenerInterface;
            if ($instanceof && true === $listerner->isBlocking()) {
                $this->log('Listener "' . get_class($listerner) . '" is currently blocking', LogLevel::DEBUG);
                $blocking = true;
            }
        }

        return $blocking;
    }

}
