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

namespace Test\Core\Middleware;

use OC\AppFramework\Http\Request;
use OC\Authentication\AccountModule\Manager;
use OC\Authentication\Exceptions\AccountNeedsUpdateException;
use OC\Core\Controller\LoginController;
use OC\Core\Middleware\AccountModuleMiddleware;
use OCP\AppFramework\Utility\IControllerMethodReflector;
use OCP\Authentication\IAccountModuleController;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Security\ISecureRandom;
use Test\TestCase;

class AccountModuleMiddlewareTest extends TestCase {

	/** @var Manager|\PHPUnit_Framework_MockObject_MockObject */
	private $manager;

	/** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
	private $userSession;

	/** @var IControllerMethodReflector|\PHPUnit_Framework_MockObject_MockObject */
	private $reflector;

	/** @var IURLGenerator|\PHPUnit_Framework_MockObject_MockObject */
	private $urlGenerator;

	/** @var Request */
	private $request;

	/** @var AccountModuleMiddleware */
	private $middleware;

	protected function setUp() {
		parent::setUp();

		$this->manager = $this->createMock(Manager::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->reflector = $this->createMock(IControllerMethodReflector::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->request = new Request(
			[
				'server' => [
					'REQUEST_URI' => 'test/url'
				]
			],
			$this->createMock(ISecureRandom::class),
			$this->createMock(IConfig::class)
		);

		$this->middleware = new AccountModuleMiddleware($this->manager, $this->userSession, $this->reflector, $this->urlGenerator, $this->request);
	}

	public function testBeforeControllerPublicPage() {
		$this->reflector->expects($this->once())
			->method('hasAnnotation')
			->with('PublicPage')
			->will($this->returnValue(true));
		$this->userSession->expects($this->never())
			->method('isLoggedIn');

		$this->middleware->beforeController(null, 'create');
	}

	public function testBeforeControllerLoginController() {
		$this->reflector->expects($this->once())
			->method('hasAnnotation')
			->with('PublicPage')
			->will($this->returnValue(false));
		$this->userSession->expects($this->never())
			->method('isLoggedIn');

		$controller = $this->getMockBuilder(LoginController::class)
			->disableOriginalConstructor()
			->getMock();

		$this->middleware->beforeController($controller, 'logout');
	}
	public function testBeforeControllerAccountModuleController() {
		$this->reflector->expects($this->once())
			->method('hasAnnotation')
			->with('PublicPage')
			->will($this->returnValue(false));
		$this->userSession->expects($this->never())
			->method('isLoggedIn');

		$controller = $this->createMock(IAccountModuleController::class);

		$this->middleware->beforeController($controller, null);
	}

	public function testBeforeControllerNotLoggedIn() {
		$this->reflector->expects($this->once())
			->method('hasAnnotation')
			->with('PublicPage')
			->will($this->returnValue(false));
		$this->userSession->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(false));

		$this->userSession->expects($this->never())
			->method('getUser');

		$this->middleware->beforeController(null, null);
	}

	/**
	 * @expectedException \UnexpectedValueException
	 */
	public function testBeforeControllerNoUserInSession() {
		$this->reflector->expects($this->once())
			->method('hasAnnotation')
			->with('PublicPage')
			->will($this->returnValue(false));
		$this->userSession->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(true));

		$this->userSession->expects($this->once())
			->method('getUser')
			->will($this->returnValue(null));

		$this->middleware->beforeController(null, null);
	}

	public function testBeforeControllerAccountUpToDate() {
		$this->reflector->expects($this->once())
			->method('hasAnnotation')
			->with('PublicPage')
			->will($this->returnValue(false));
		$this->userSession->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(true));

		$user = $this->createMock(IUser::class);

		$this->userSession->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));

		$this->manager->expects($this->once())
			->method('needsUpdate')
			->will($this->returnValue(false));

		$this->middleware->beforeController(null, null);
	}

	/**
	 * @expectedException \OC\Authentication\Exceptions\AccountNeedsUpdateException
	 */
	public function testBeforeControllerAccountNeedsUpdate() {
		$this->reflector->expects($this->once())
			->method('hasAnnotation')
			->with('PublicPage')
			->will($this->returnValue(false));
		$this->userSession->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(true));

		$user = $this->createMock(IUser::class);

		$this->userSession->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));

		$this->manager->expects($this->once())
			->method('needsUpdate')
			->will($this->returnValue(true));

		$this->middleware->beforeController(null, null);
	}

	public function testAfterExceptionAccountNeedsUpdate() {
		$ex = new AccountNeedsUpdateException();

		$this->urlGenerator->expects($this->once())
			->method('linkToRoute')
			->with('core.AccountModule.nextStep')
			->will($this->returnValue('test/url'));

		$expected = new \OCP\AppFramework\Http\RedirectResponse('test/url');

		$this->assertEquals($expected, $this->middleware->afterException(null, null, $ex));
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage testAfterException
	 */
	public function testAfterException() {
		$ex = new \Exception('testAfterException');

		$this->urlGenerator->expects($this->never())
			->method('getAbsoluteUrl');

		$this->middleware->afterException(null, null, $ex);
	}
}
