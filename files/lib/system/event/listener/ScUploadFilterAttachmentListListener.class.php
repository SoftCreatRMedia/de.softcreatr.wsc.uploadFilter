<?php
namespace wcf\system\event\listener;
use wcf\system\WCF;
use DateTime;

/**
 * Removes non-embedded attachments from view.
 * 
 * @copyright	SoftCreatR Media
 * @license	GNU General Public License
 * @package	WoltLabSuite\Core\System\Event\Listener
 */
class ScUploadFilterAttachmentListListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (WCF::getSession()->getPermission('user.uploadFilter.canIgnoreUploadFilter')) return;
		if (!SC_UPLOAD_FREE_SUNDAY_HIDE_ATTACHMENTS) return;
		
		$tz = WCF::getUser()->getTimeZone();
		$now = new DateTime('now', $tz);
		$start = DateTime::createFromFormat('!Y-m-d', SC_UPLOAD_FREE_SUNDAY_START_DATE, $tz);
		$end = DateTime::createFromFormat('!Y-m-d', SC_UPLOAD_FREE_SUNDAY_END_DATE, $tz);
		
		if ($now->getTimestamp() < $start->getTimestamp() || $now->getTimestamp() > $end->getTimestamp()) {
			return;
		}
		
		// nuke em all
		WCF::getTPL()->assign('attachmentList', null);
	}
}
