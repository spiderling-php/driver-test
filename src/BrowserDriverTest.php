<?php

namespace SP\DriverTest;

use SP\Spiderling\BrowserSession;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
abstract class BrowserDriverTest extends CrawlerDriverTest
{
    /**
     * @return BrowserSession
     */
    public function getBrowserSession()
    {
        $session = new BrowserSession(self::getDriver());
        $session->open(self::getServerUri());

        return $session;
    }

    public function testScreenshot()
    {
        $session = $this->getBrowserSession();

        $session->saveScreenshot(__DIR__.'/../file.jpg');

        $this->assertFileExists(__DIR__.'/../file.jpg');

        unlink(__DIR__.'/../file.jpg');
    }

    public function testMouseHover()
    {
        $session = $this->getBrowserSession();

        $session->executeJs("
            document
                .getElementById('p-1')
                .addEventListener('mouseover', function () {
                    console.log('hovered over p-1');
                });
        ");

        $session->hover('#p-1');

        $messages = $session->getJsMessages();
        $this->assertEquals(['hovered over p-1'], $messages);
    }

    public function testGetMessages()
    {
        $session = $this->getBrowserSession();

        $session->executeJs('console.log("test message")');
        $session->executeJs('window.test();');

        $messages = $session->getJsMessages();
        $errors = $session->getJsErrors();

        $this->assertEquals(['test message'], $messages);

        $this->assertEquals(
            ["TypeError: 'undefined' is not a function (evaluating 'window.test()') in :1"],
            $errors
        );
    }
}
