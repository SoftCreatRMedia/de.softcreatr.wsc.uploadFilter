<?php
namespace wcf\system\event\listener;
use wcf\system\WCF;
use DateTime;

/**
 * Provides an indicator wether to remove/replace
 * user avatars, profile banners, etc. or not.
 * 
 * @copyright	SoftCreatR Media
 * @license	GNU General Public License
 * @package	WoltLabSuite\Core\System\Event\Listener
 */
class ScUploadFilterAbstractPageListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (WCF::getSession()->getPermission('user.uploadFilter.canIgnoreUploadFilter')) return;
		
		$tz = WCF::getUser()->getTimeZone();
		$now = new DateTime('now', $tz);
		$start = DateTime::createFromFormat('!Y-m-d', SC_UPLOAD_FREE_SUNDAY_START_DATE, $tz);
		$end = DateTime::createFromFormat('!Y-m-d', SC_UPLOAD_FREE_SUNDAY_END_DATE, $tz);
		
		WCF::getTPL()->assign('uploadFilterActive', false);
		
		if ($now->getTimestamp() < $start->getTimestamp() || $now->getTimestamp() > $end->getTimestamp()) {
			return;
		}
		
		WCF::getTPL()->assign('uploadFilterActive', true);
	}
}
