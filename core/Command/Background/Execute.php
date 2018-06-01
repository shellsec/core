<?php
/**
 * @author Tom Needham <tom@owncloud.com>
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

namespace OC\Core\Command\Background;

use OC\BackgroundJob\TimedJob;
use OC\Log\ConsoleLogger;
use OCP\BackgroundJob\IJobList;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Execute extends Command {

	/**
	 * @var IJobList
	 */
	protected $jobList;

	/**
	 * @param IJobList $jobList
	 */
	public function __construct(IJobList $jobList) {
		$this->jobList = $jobList;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName("background:execute")
			->setDescription("Run a single background job")
			->addArgument('jobId', InputArgument::REQUIRED)
			->addOption('force', 'f', InputOption::VALUE_NONE);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		// Get the job to run
		$jobId = $input->getArgument('jobId');
		// Try to find the job
		$job = $this->jobList->getById($jobId);
		if($job === null) {
			$output->writeln("<error>Job not found</error>");
			return 1;
		}

		$output->writeln('<info>Found job</info>');

		$start = time();

		// Run the job if not reserved
		$logger = new \OC\Log(new ConsoleLogger($output), \OC::$server->getSystemConfig());

		if($job instanceof TimedJob && $input->getOption('force')) {
			// Force the execution to ignore the interval
			$output->writeln('<info>Forcing job to run</info>');
			$job->setLastRun(0);
		}

		$job->execute($this->jobList, $logger);

		$duration = time() - $start;

		$output->writeln("<info>Finished in $duration seconds</info>");
		$this->jobList->setLastJob($job);
		$this->jobList->setExecutionTime($job, $duration);

		return 0;
	}
}
