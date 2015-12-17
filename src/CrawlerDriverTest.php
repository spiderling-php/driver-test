<?php

namespace SP\DriverTest;

use PHPUnit_Framework_TestCase;
use SP\Spiderling\CrawlerSession;
use SP\Spiderling\CrawlerInterface;
use Symfony\Component\Process\Process;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
abstract class CrawlerDriverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Process
     */
    private static $server;

    /**
     * @var CrawlerInterface
     */
    private static $driver;

    /**
     * @return CrawlerInterface
     */
    public static function getDriver()
    {
        return self::$driver;
    }

    /**
     * @return CrawlerInterface
     */
    public static function setDriver(CrawlerInterface $driver)
    {
        self::$driver = $driver;
    }

    /**
     * @return Process
     */
    public static function setServer(Process $server)
    {
        self::$server = $server;
    }

    /**
     * @var Process
     */
    public static function getServer()
    {
        return static::$server;
    }

    /**
     * @return string
     */
    public static function getServerUri()
    {
        return 'http://127.0.0.1:4295';
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $server = new Process('php -S 127.0.0.1:4295', __DIR__.'/../html');
        $server->start();

        self::setServer($server);
    }

    /**
     * @return CrawlerSession
     */
    public function getCrawlerSession()
    {
        $session = new CrawlerSession(static::getDriver());
        $session->open(self::getServerUri());

        return $session;
    }

    public function testAccessors()
    {
        $session = $this->getCrawlerSession();

        $input = $session->getLink('Subpage 1');

        $this->assertEquals('Subpage Title 1', $input->getAttribute('title'));
        $this->assertEquals('/test_functest/subpage1', $input->getAttribute('href'));
        $this->assertEquals('Subpage 1', $input->getText());

        $expected = <<<HTML
<a class="navlink" id="navlink-1" title="Subpage Title 1" href="/test_functest/subpage1">Subpage 1</a>
HTML;
        $this->assertEquals(
            $expected,
            $input->getHtml()
        );

        $male = $session->getField('Gender Male');
        $this->assertEquals('gender', $male->getAttribute('name'));
        $this->assertFalse($male->isChecked());

        $female = $session->getField('Gender Female');
        $this->assertEquals('female', $female->getValue());
        $this->assertTrue($female->isChecked());

        $uk = $session->get('option[value="uk"]');
        $this->assertTrue($uk->isSelected());

        $us = $session->get('option[value="us"]');
        $this->assertFalse($us->isSelected());

        $message = $session->getField('message');
        $expected = <<<MESSAGE
Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua.
MESSAGE;
        $this->assertEquals($expected, $message->getValue());
    }

    public function testFinders()
    {
        $session = $this->getCrawlerSession();

        $p1 = $session->get('#p-1');

        $this->assertEquals(
            'p-1',
            $p1->getAttribute('id'),
            'Should be able to find by ID'
        );

        $pArray = $session->getArray('p');

        $this->assertEquals(
            ['p-1', 'p-2', 'p-3'],
            array_map(function ($p) {
                return $p->getAttribute('id');
            }, $pArray),
            'Should be able to find an array of all the p elements'
        );

        $pArray = $session->getArray('p:visible(true)');

        $this->assertEquals(
            ['p-1', 'p-2'],
            array_map(function ($p) {
                return $p->getAttribute('id');
            }, $pArray),
            'Should be able to find an array of all the p elements, using filters'
        );

        $button = $session->getButton('Submit Image');
        $this->assertEquals(
            'submit-btn-icon',
            $button->getAttribute('id'),
            'Should find a button by alt text of its img'
        );

        $button = $session->getButton('Submit Button');
        $this->assertEquals(
            'submit-btn',
            $button->getAttribute('id'),
            'Should find a button by text inside of it'
        );

        $button = $session->getButton('Submit Item');
        $this->assertEquals(
            'submit',
            $button->getAttribute('id'),
            'Should find input button by its value'
        );
    }

    public function testFieldValues()
    {
        $session = $this->getCrawlerSession();

        $email = $session->getField('Enter Email');
        $this->assertEquals('tom@example.com', $email->getValue());
        $email->setValue('other@example.com');
        $this->assertEquals('other@example.com', $email->getValue());

        $select = $session->getField('Enter Country');
        $this->assertEquals('uk', $select->getValue());
        $select->setValue('bulgaria');
        $this->assertEquals('bulgaria', $select->getValue());
        $select->setValue('Tunisia');
        $this->assertEquals('Tunisia', $select->getValue());

        $us = $session->get('option:text("United States")');
        $this->assertFalse($us->isSelected());
        $us->select();
        $this->assertTrue($us->isSelected());
    }

    public function testFormPostEmpty()
    {
        $session = $this->getCrawlerSession();

        $session->setField('Enter Email', 'my@example.com');
        $session->setField('Enter Name', 'Pesho');
        $session->check('Gender Male');
        $session->check('Enter Notify Me');
        $session->setField('Enter Message', 'Some new bio');
        $session->setField('Enter Country', 'bulgaria');

        $session->clickButton('Submit Button');

        $this->assertEquals('Submit page', $session->get('h1')->getText());

        $this->assertEquals('Pesho', $session->get('#name')->getText());
        $this->assertEquals('notify', $session->get('#notifyme')->getText());
        $this->assertEquals('male', $session->get('#gender')->getText());
        $this->assertEquals('Some new bio', $session->get('#message')->getText());
        $this->assertEquals('bulgaria', $session->get('#country')->getText());
    }

    public function testFormPostFilled()
    {
        $session = $this->getCrawlerSession();

        $session->clickButton('Submit Button');

        $this->assertEquals('Submit page', $session->get('h1')->getText());

        $this->assertEquals('Tomas', $session->get('#name')->getText());
        $this->assertEquals('female', $session->get('#gender')->getText());
        $this->assertEquals('uk', $session->get('#country')->getText());
    }

    public function testLinkFollow()
    {
        $session = $this->getCrawlerSession();

        $session->clickLink('Subpage 2');

        $this->assertEquals('Other page', $session->get('h1')->getText());
    }
}
