<?php
/**
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
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

namespace Test\Core\Controller;

use OC\Authentication\AccountModule\Manager;
use OC\Core\Controller\AccountModuleController;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use Test\TestCase;

class AccountModuleControllerTest extends TestCase {

	/** @var IRequest | \PHPUnit_Framework_MockObject_MockObject */
	private $request;
	/** @var Manager | \PHPUnit_Framework_MockObject_MockObject */
	private $manager;
	/** @var IUserSession | \PHPUnit_Framework_MockObject_MockObject */
	private $userSession;
	/** @var ISession | \PHPUnit_Framework_MockObject_MockObject */
	private $session;
	/** @var IURLGenerator | \PHPUnit_Framework_MockObject_MockObject */
	private $urlGenerator;

	/** @var AccountModuleController */
	private $controller;

	protected function setUp() {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->manager = $this->createMock(Manager::class);
		$this->userSession = $this->createMock(IUserSession::class);

		$this->controller = new AccountModuleController(
			'core',
			$this->request,
			$this->manager,
			$this->userSession
		);
	}

	public function testNextStep() {
		$user = $this->createMock(IUser::class);
		$redirectResponse = new RedirectResponse('test/url');

		$this->userSession->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));
		$this->manager->expects($this->once())
			->method('nextStep')
			->with($user)
			->will($this->returnValue($redirectResponse));

		$this->assertEquals($redirectResponse, $this->controller->nextStep('/some/url'));
	}

	/**
	 * @expectedException \UnexpectedValueException
	 */
	public function testNextStepResponseUnavailable() {
		$user = $this->createMock(IUser::class);

		$this->userSession->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));
		$this->manager->expects($this->once())
			->method('nextStep')
			->with($user)
			->will($this->returnValue(null));

		$this->controller->nextStep('/some/url');
	}
}
