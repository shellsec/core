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

namespace Test\Authentication\AccountModule;

use OC\Authentication\AccountModule\Manager;
use OCP\App\IAppManager;
use OCP\Authentication\IAccountModule;
use OCP\IUser;
use Test\TestCase;

class ManagerTest extends TestCase {

	/** @var \OCP\IUser|\PHPUnit_Framework_MockObject_MockObject */
	private $user;

	/** @var \OC\App\AppManager|\PHPUnit_Framework_MockObject_MockObject */
	private $appManager;

	/** @var Manager */
	private $manager;

	protected function setUp() {
		parent::setUp();

		$this->user = $this->createMock(IUser::class);
		$this->appManager = $this->createMock(IAppManager::class);

		$this->manager = new Manager($this->appManager);
	}

	public function testGetAccountModules() {
		$fakeModule = $this->createMock(IAccountModule::class);
		\OC::$server->registerService('\OCA\AccountModuleApp\Module', function () use ($fakeModule) {
			return $fakeModule;
		});

		$this->appManager->expects($this->once())
			->method('getEnabledAppsForUser')
			->with($this->user)
			->will($this->returnValue(['accountmoduleapp']));

		$this->appManager->expects($this->once())
			->method('getAppInfo')
			->with('accountmoduleapp')
			->will($this->returnValue([
				'account-modules' => [
					'\OCA\AccountModuleApp\Module',
				],
			]));

		$modules = $this->manager->getAccountModules($this->user);

		self::assertCount(1, $modules);
		self::assertSame($fakeModule, $modules[0]);
	}
}
