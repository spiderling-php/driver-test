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

    public function testAlertText()
    {
        $session = $this->getBrowserSession();

        $session->clickOn('#navlink-3');

        $this->assertEquals('Some alert message', $session->getAlertText());

        $session->confirm(true);
    }

    public function testConfirm()
    {
        $session = $this->getBrowserSession();

        $session
            ->clickOn('#navlink-4')
            ->confirm(true);

        $this->assertEquals('Other page', $session->get('h1')->getText());
    }

    public function testMouseHover()
    {
        $session = $this->getBrowserSession();

        $session->executeJs("
            document
                .getElementById('p-1')
                .addEventListener('mouseover', function () {
                    this.setAttribute('title', 'Hovered');
                });
        ");

        $session->hover('#p-1');

        $this->assertEquals('Hovered', $session->get('#p-1')->getAttribute('title'));
    }
}
