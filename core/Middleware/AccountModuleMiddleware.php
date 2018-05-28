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

namespace OC\Core\Middleware;

use Exception;
use OC\Authentication\AccountModule\Manager;
use OC\Authentication\Exceptions\AccountNeedsUpdateException;
use OC\Core\Controller\LoginController;
use OC\ForbiddenException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Middleware;
use OCP\AppFramework\Utility\IControllerMethodReflector;
use OCP\Authentication\IAccountModuleController;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;

/**
 * Class AccountModuleMiddleware
 *
 * Apps can regisetr account-modules in their info.xml. These AccountModules
 * will be notified after a user has been authenticated via login(IUser $user).
 *
 * The account module can then do any check it deems necessary. This Account
 * Middleware will ask the AccountModules if they have a step the user needs to
 * take before he can login, eg. change his password, setup TOTP or acknowledge
 * terms of service.
 *
 *
 *
 * @package OC\Core\Middleware
 */
class AccountModuleMiddleware extends Middleware {

	/** @var Manager */
	private $manager;

	/** @var IUserSession */
	private $session;

	/** @var IControllerMethodReflector */
	private $reflector;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var IRequest */
	private $request;

	/**
	 * @param Manager $manager
	 * @param IUserSession $session
	 * @param IControllerMethodReflector $reflector
	 * @param IURLGenerator $urlGenerator
	 * @param IRequest $request
	 */
	public function __construct(
		Manager $manager,
		IUserSession $session,
		IControllerMethodReflector $reflector,
		IURLGenerator $urlGenerator,
		IRequest $request
	) {
		$this->manager = $manager;
		$this->session = $session;
		$this->reflector = $reflector;
		$this->urlGenerator = $urlGenerator;
		$this->request = $request;
	}

	/**
	 * @param Controller $controller
	 * @param string $methodName
	 * @throws AccountNeedsUpdateException
	 * @throws ForbiddenException
	 */
	public function beforeController($controller, $methodName) {
		if ($this->reflector->hasAnnotation('PublicPage')) {
			// Don't block public pages
			return;
		}

		if ($controller instanceof LoginController && $methodName === 'logout') {
			// Don't block the logout page
			return;
		}

		if ($controller instanceof IAccountModuleController) {
			// Don't block any IAccountModuleController controllers
			return;
		}

		if ($this->session->isLoggedIn()) {
			$user = $this->session->getUser();
			if ($user === null) {
				throw new \UnexpectedValueException('User isLoggedIn but session does not contain user');
			}
			if ($this->manager->needsUpdate($user)) {
				throw new AccountNeedsUpdateException('Account needs to be updated');
			}
		}
	}

	/**
	 * @param Controller $controller
	 * @param string $methodName
	 * @param Exception $exception
	 * @return Http\Response
	 * @throws Exception
	 */
	public function afterException($controller, $methodName, Exception $exception) {
		if ($exception instanceof AccountNeedsUpdateException) {
			return new RedirectResponse($this->urlGenerator->linkToRoute('core.AccountModule.nextStep', [
				'redirect_url' => \urlencode($this->request->server['REQUEST_URI']),
			]));
		}
		throw $exception;
	}
}
