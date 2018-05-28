<?php

namespace OCP\Authentication;

use OCP\IUser;

/**
 * Interface IAccountModule
 *
 * @package OCP\Authentication
 * @since 10.0.9
 */
interface IAccountModule {

	/**
	 *
	 * @since 10.0.9
	 *
	 * @param IUser $user
	 * @return bool
	 */
	public function needsUpdate(IUser $user);

	/**
	 *
	 * @since 10.0.9
	 *
	 * @param IUser $user
	 * @return IAccountModuleStep[]
	 */
	public function neededSteps(IUser $user);
}
