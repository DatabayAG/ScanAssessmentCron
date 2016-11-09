<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Cron/classes/class.ilCronJob.php';
require_once 'class.ilScanAssessmentCronPlugin.php';
require_once 'Services/Cron/classes/class.ilCronJobResult.php';
ilScanAssessmentCronPlugin::getInstance()->includeClass('log/class.ilScanAssessmentCronLog.php');
/**
 * Class ilScanAssessmentCronJob
 */
class ilScanAssessmentCronJob extends ilCronJob
{

	protected $log;
	
	/**
	 * Get id
	 * @return string
	 */
	public function getId()
	{
		return 'scasc_cronjob';
	}

	/**
	 * Is to be activated on "installation"
	 * @return boolean
	 */
	public function hasAutoActivation()
	{
		return false;
	}

	/**
	 * Can the schedule be configured?
	 * @return boolean
	 */
	public function hasFlexibleSchedule()
	{
		return true;
	}

	/**
	 * Get schedule type
	 * @return int
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}

	/**
	 * Get schedule value
	 * @return int|array
	 */
	function getDefaultScheduleValue()
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasCustomSettings()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isManuallyExecutable()
	{
		return defined('DEVMODE') && DEVMODE;
	}

	/**
	 * Run job
	 * @return ilCronJobResult
	 */
	public function run()
	{
		$this->log = ilScanAssessmentCronLog::getInstance();
		$this->log->info('Starting ScanAssessment Cronjob...');

		if($this->checkPreRequirements())
		{
			$this->log->info('ScanAssessment Plugin installed and activated...');
			try
			{
				if(ilScanAssessmentCronPlugin::getInstance()->acquireLock())
				{
					$this->log->info('Created lock file: ' . ilScanAssessmentCronPlugin::getInstance()->getLockFilePath());
				}
				else
				{
					$this->log->warn('Script is probably running. Please remove the following lock file if you are sure no course reminder task is running: ' . ilScanAssessmentCronPlugin::getInstance()->getLockFilePath());
					$result = new ilCronJobResult();
					$result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
					$result->setMessage('Terminated ScanAssessment Cron script: Script is currently locked.');
					return $result;
				}
			}
			catch(Exception $e)
			{
				$this->log->crit($e->getMessage());
				$result = new ilCronJobResult();
				$result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
				$result->setMessage('Terminated ScanAssessment Cron script: Script is currently locked.');
				return $result;
			}

			try
			{
				if(ilScanAssessmentCronPlugin::getInstance()->releaseLock())
				{
					$this->log->info('Removed lock file: ' . ilScanAssessmentCronPlugin::getInstance()->getLockFilePath() . '.');
				}
				else
				{
					$this->log->info('No lock to remove: ' . ilScanAssessmentCronPlugin::getInstance()->getLockFilePath() . '.');
				}
			}
			catch(ilException $e)
			{
				$this->log->crit($e->getMessage());
			}
		}
		else
		{
			$this->log->warn('ScanAssessment Plugin not installed or activated... finishing.');
		}
		$result = new ilCronJobResult();
		$result->setMessage('Finished cron job task.');
		$result->setStatus(ilCronJobResult::STATUS_OK);
		$this->log->info('ScanAssessment Cronjob finished.');
		return $result;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return ilScanAssessmentCronPlugin::getInstance()->txt('scasc_title');
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return ilScanAssessmentCronPlugin::getInstance()->txt('scasc_desc');
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveCustomSettings(ilPropertyFormGUI $a_form)
	{
		ilScanAssessmentCronPlugin::getInstance()->save();
		return parent::saveCustomSettings($a_form);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
	{
		if($this->checkPreRequirements() !== true)
		{
			ilUtil::sendFailure(ilScanAssessmentCronPlugin::getInstance()->txt('plugin_not_installed_or_activated'));
		}
		parent::addCustomSettingsToForm($a_form);
	}

	protected function checkPreRequirements()
	{
		$scan_assessment_plugin_path = 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ScanAssessment/classes/class.ilScanAssessmentPlugin.php';
		if(!file_exists($scan_assessment_plugin_path))
		{
			return false;
		}
		else
		{
			require_once $scan_assessment_plugin_path;
			$scan_plugin = new ilScanAssessmentPlugin();
			if($scan_plugin->isActive() != 1)
			{
				return false;
			}
		}
		return true;
	}
} 