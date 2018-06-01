<?php
/**
 * @author Juan Pablo Villafañez Ramos <jvillafanez@owncloud.com>
 * @author Tom Neehdam <tom@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Test\Log;

use OC\Log\ConsoleLogger;
use Test\TestCase;
use OCP\Util;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLoggerTest extends TestCase {
	protected function setUp() {
		ConsoleLogger::setGlobalConsoleLogger(null);
	}

	public function testGetWrappedOutput() {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertSame($outputMock, $consoleLogger->getWrappedOutput());
	}

	public function testGetSetGlobalConsoleLogger() {
		$this->assertNull(ConsoleLogger::getGlobalConsoleLogger());

		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();
		$consoleLogger = new ConsoleLogger($outputMock);

		ConsoleLogger::setGlobalConsoleLogger($consoleLogger);
		$this->assertSame($consoleLogger, ConsoleLogger::getGlobalConsoleLogger());
	}

	public function logMessageDataProvider() {
		return [
			[Util::DEBUG, 'message', '<comment>message</comment>', OutputInterface::VERBOSITY_DEBUG],
			[Util::INFO, 'message', 'message', OutputInterface::VERBOSITY_VERBOSE],
			[Util::WARN, 'message', '<info>message</info>', OutputInterface::VERBOSITY_NORMAL],
			[Util::ERROR, 'message', '<error>message</error>', OutputInterface::VERBOSITY_QUIET],
			[Util::FATAL, 'message', '<error>message</error>', OutputInterface::VERBOSITY_QUIET],
			[Util::ERROR, 'öñö', '<error>öñö</error>', OutputInterface::VERBOSITY_QUIET],
			[985, 'öñö', 'öñö', OutputInterface::VERBOSITY_NORMAL],
		];
	}

	/**
	 * @dataProvider logMessageDataProvider
	 */
	public function testLog($level, $message, $expectedMessage, $expectedVerbosity) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with($expectedMessage, $expectedVerbosity);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->log($level, $message));
	}

	public function messagesDataProvider() {
		return [
			['dummy'],
			['another dummy with spaces'],
			['ñutf8'],
			['sdflkj%!"·$%%&/()=?¿¡|@#~½¬{[]'],
			['降雨量'],
			[' ̿ ̿̿ ̿̿\̵͇̿̿\=(•̪●)=/̵͇̿̿/ ̿̿ ̿ ̿'],
		];
	}

	/**
	 * @dataProvider messagesDataProvider
	 */
	public function testEmergency($message) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with("<error>$message</error>", OutputInterface::VERBOSITY_QUIET);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->emergency($message));
	}

	/**
	 * @dataProvider messagesDataProvider
	 */
	public function testAlert($message) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with("<error>$message</error>", OutputInterface::VERBOSITY_QUIET);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->alert($message));
	}

	/**
	 * @dataProvider messagesDataProvider
	 */
	public function testCritical($message) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with("<error>$message</error>", OutputInterface::VERBOSITY_QUIET);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->critical($message));
	}

	/**
	 * @dataProvider messagesDataProvider
	 */
	public function testError($message) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with("<error>$message</error>", OutputInterface::VERBOSITY_QUIET);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->error($message));
	}

	/**
	 * @dataProvider messagesDataProvider
	 */
	public function testWarning($message) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with("<info>$message</info>", OutputInterface::VERBOSITY_NORMAL);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->warning($message));
	}

	/**
	 * @dataProvider messagesDataProvider
	 */
	public function testNotice($message) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with($message, OutputInterface::VERBOSITY_VERBOSE);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->notice($message));
	}

	/**
	 * @dataProvider messagesDataProvider
	 */
	public function testInfo($message) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with($message, OutputInterface::VERBOSITY_VERBOSE);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->info($message));
	}

	/**
	 * @dataProvider messagesDataProvider
	 */
	public function testDebug($message) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with("<comment>$message</comment>", OutputInterface::VERBOSITY_DEBUG);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->debug($message));
	}

	/**
	 * @dataProvider messagesDataProvider
	 */
	public function testLogException($message) {
		$outputMock = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('writeln')
			->with($this->stringStartsWith("<error>$message"), OutputInterface::VERBOSITY_QUIET);

		$consoleLogger = new ConsoleLogger($outputMock);
		$this->assertNull($consoleLogger->logException(new \Exception($message)));
	}
}