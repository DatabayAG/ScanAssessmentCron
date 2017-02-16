<?php
require_once 'include/inc.ilias_version.php';
if(version_compare(ILIAS_VERSION_NUMERIC, '5.2.0', '>='))
{
	require_once './Services/Cron/classes/class.ilCronStartUp.php';

	if($_SERVER['argc'] < 4)
	{
		echo "Usage: cron.php username password client\n";
		exit(1);
	}
	try {
		$cron = new ilCronStartUp($_SERVER['argv'][3], $_SERVER['argv'][1], $_SERVER['argv'][2]);
		$cron->initIlias();
		$cron->authenticate();

		require_once dirname(__FILE__) . '/classes/class.ilScanAssessmentCronPlugin.php';
		ilScanAssessmentCronPlugin::getInstance()->run();
	}
	catch(Exception $e)
	{
		echo $e->getMessage()."\n";
		exit(1);
	}
}
else
{
	chdir(dirname(__FILE__));
	$ilias_main_directory = './';
	while(!file_exists($ilias_main_directory . 'ilias.ini.php'))
	{
		$ilias_main_directory .= '../';
	}
	chdir($ilias_main_directory);
	
	include_once 'Services/Context/classes/class.ilContext.php';
	ilContext::init(ilContext::CONTEXT_CRON);
	
	include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
	ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);
	
	$_COOKIE['ilClientId']	= $_SERVER['argv'][3];
	$_POST['username']		= $_SERVER['argv'][1];
	$_POST['password']		= $_SERVER['argv'][2];
	
	if($_SERVER['argc'] < 4)
	{
		die('Usage: cron.php username password client\n');
	}
	
	require_once 'include/inc.header.php';
	
	require_once dirname(__FILE__) . '/classes/class.ilScanAssessmentCronPlugin.php';
	ilScanAssessmentCronPlugin::getInstance()->run();
}
