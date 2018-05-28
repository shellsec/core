<?php

namespace OCP\Authentication;

use OCP\AppFramework\Http\RedirectResponse;

/**
 * Interface IAccountModuleStep
 *
 * @package OCP\Authentication
 * @since 10.0.9
 */
interface IAccountModuleStep {

	/**
	 *
	 * @since 10.0.9
	 *
	 * @return RedirectResponse
	 */
	public function getRedirectResponse();

	// TODO make generic step with an execute() method?
}
