<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Cron/classes/class.ilCronHookPlugin.php';

/**
 * Class ilScanAssessmentCronPlugin
 */
class ilScanAssessmentCronPlugin extends ilCronHookPlugin
{
	/**
	 * @var string
	 */
	const CTYPE = 'Services';

	/**
	 * @var string
	 */
	const CNAME = 'Cron';

	/**
	 * @var string
	 */
	const SLOT_ID = 'crnhk';

	/**
	 * @var string
	 */
	const PNAME = 'ScanAssessmentCron';

	/**
	 * @var self
	 */
	private static $instance = null;

	/**
	 * @var ilSetting ilSetting
	 */
	private $settings;

	/**
	 * @return ilScanAssessmentCronJob[]
	 */
	public function getCronJobInstances()
	{
		require_once 'class.ilScanAssessmentCronJob.php';
		return array(new ilScanAssessmentCronJob());
	}

	/**
	 * @param int $a_job_id
	 * @return ilScanAssessmentCronJob
	 */
	public function getCronJobInstance($a_job_id)
	{
		require_once 'class.ilScanAssessmentCronJob.php';
		return new ilScanAssessmentCronJob();
	}

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->settings = new ilSetting('pl_' . strtolower(self::PNAME));
		$this->read();
	}

	/**
	 *
	 */
	protected function read()
	{
	}

	/**
	 *
	 */
	public function run()
	{
		require_once dirname(__FILE__) . '/class.ilScanAssessmentCronJob.php';
		$task = new ilScanAssessmentCronJob();
		$task->run();
	}
	
	/**
	 *
	 */
	public function save()
	{
	}

	/**
	 * Get Plugin Name. Must be same as in class name il<Name>Plugin
	 * and must correspond to plugins subdirectory name.
	 * Must be overwritten in plugin class of plugin
	 * (and should be made final)
	 * @return    string    Plugin Name
	 */
	public function getPluginName()
	{
		return self::PNAME;
	}

	/**
	 * @return self
	 */
	public static function getInstance()
	{
		if(null === self::$instance)
		{
			require_once 'Services/Component/classes/class.ilPluginAdmin.php';
			return self::$instance = ilPluginAdmin::getPluginObject(self::CTYPE, self::CNAME, self::SLOT_ID, self::PNAME);
		}

		return self::$instance;
	}

	/**
	 * @return bool
	 */
	public function acquireLock()
	{
		if(! $this->isLocked())
		{
			if(!@file_put_contents($this->getLockFilePath(), getmypid(), LOCK_EX))
			{
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * @return string
	 */
	public function getLockFilePath()
	{
		return ilUtil::getDataDir() . '/scan_assessment_cron.lock';
	}

	/**
	 * @return boolean
	 */
	public function isLocked()
	{
		if(file_exists($this->getLockFilePath()))
		{
			return true;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function releaseLock()
	{
		if(file_exists($this->getLockFilePath()))
		{
			if(@unlink($this->getLockFilePath()))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return true;
	}
} 