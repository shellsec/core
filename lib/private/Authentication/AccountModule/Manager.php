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

namespace OC\Authentication\AccountModule;

use OCP\App\IAppManager;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\QueryException;
use OCP\Authentication\IAccountModule;
use OCP\Authentication\IAccountModuleStep;
use OCP\IUser;

class Manager {

	/** @var IAppManager */
	private $appManager;

	/**
	 * @param IAppManager $appManager
	 */
	public function __construct(IAppManager $appManager) {
		$this->appManager = $appManager;
	}

	/**
	 * Get the list of account modules for the given user
	 * Limited to auth-modules that are enabled for this user
	 *
	 * @param IUser $user
	 * @return IAccountModule[]
	 * @throws QueryException
	 * @throws \OC\NeedsUpdateException
	 */
	public function getAccountModules(IUser $user) {
		$modules = [];

		foreach ($this->appManager->getEnabledAppsForUser($user) as $appId) {
			$info = $this->appManager->getAppInfo($appId);
			if (isset($info['account-modules'])) {
				$moduleClasses = $info['account-modules'];
				foreach ($moduleClasses as $class) {
					$this->loadAccountModuleApp($appId);
					$module = \OC::$server->query($class);
					$modules[] = $module;
				}
			}
		}

		return $modules;
	}

	/**
	 * Load an app by ID if it has not been loaded yet
	 *
	 * @param string $appId
	 * @throws \OC\NeedsUpdateException
	 */
	protected function loadAccountModuleApp($appId) {
		if (!\OC_App::isAppLoaded($appId)) {
			\OC_App::loadApp($appId);
		}
	}

	public function needsUpdate(IUser $user) {
		foreach ($this->getAccountModules($user) as $accountModule) {
			if ($accountModule->needsUpdate($user)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param IUser $user
	 * @return RedirectResponse
	 * @throws QueryException
	 * @throws \OC\NeedsUpdateException
	 */
	public function nextStep(IUser $user) {
		/** @var IAccountModuleStep[] $steps */
		$steps = [];
		foreach ($this->getAccountModules($user) as $accountModule) {
			$steps[] = $accountModule->neededSteps($user);
		}
		$steps = \array_merge(...$steps);
		// TODO steps should have an order
		if (isset($steps[0])) {
			return $steps[0]->getRedirectResponse();
		}
		return null;
	}
}
