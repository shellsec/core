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

namespace OC\Core\Controller;

use OC\Authentication\AccountModule\Manager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Authentication\IAccountModuleController;
use OCP\IRequest;
use OCP\IUserSession;

class AccountModuleController extends Controller implements IAccountModuleController {

	/** @var Manager */
	private $manager;

	/** @var IUserSession */
	private $userSession;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param Manager $manager
	 * @param IUserSession $userSession
	 */
	public function __construct($appName, IRequest $request, Manager $manager, IUserSession $userSession) {
		parent::__construct($appName, $request);
		$this->manager = $manager;
		$this->userSession = $userSession;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired // TODO why not?
	 *
	 * @param string $redirect_url
	 * @return RedirectResponse
	 * @throws \OCP\AppFramework\QueryException
	 * @throws \OC\NeedsUpdateException
	 */
	public function nextStep($redirect_url) { // TODO pass on redirect_url?
		$user = $this->userSession->getUser();
		$redirectResponse = $this->manager->nextStep($user);
		if ($redirectResponse instanceof RedirectResponse) {
			return $redirectResponse;
		}
		throw new \UnexpectedValueException("User {$user->getUID()} needs an update, but no redirect response is availabe");
	}
}
