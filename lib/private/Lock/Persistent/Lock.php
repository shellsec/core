<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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
 */

namespace OC\Lock\Persistent;

use OCP\AppFramework\Db\Entity;

/**
 * Class Lock
 *
 * @method int getFileId()
 * @method string getOwner()
 * @method int getTimeout()
 * @method int getCreatedAt()
 * @method string getToken()
 * @method int getScope()
 * @method int getDepth()
 * @method string getPath()
 *
 * TODO: add setter
 *
 * @package OC\Lock\Persistent
 */
class Lock extends Entity {
	protected $fileId;
	protected $owner;
	protected $timeout;
	protected $createdAt;
	protected $token;
	protected $scope;
	protected $depth;

	protected $path;

	public function __construct() {
		$this->addType('fileId', 'integer');
		$this->addType('timeout', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('scope', 'integer');
		$this->addType('depth', 'integer');
	}
}
