<?php

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

class InnoworktasksPanelActions extends \Innomatic\Desktop\Panel\PanelActions
{
    private $localeCatalog;
    private $innomaticContainer;

    public $status;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
        $this->innomaticContainer = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $this->localeCatalog = new LocaleCatalog(
            'innowork-projects::tasks_domain_main',
            $this->innomaticContainer->getCurrentUser()->getLanguage()
        );
    }

    public function endHelper()
    {
    }

    public function executeNewtask($eventData)
    {
        require_once('innowork/projects/InnoworkTask.php');
        $task = new InnoworkTask(
            $this->innomaticContainer->getDataAccess(),
            $this->innomaticContainer->getCurrentDomain()->getDataAccess()
        );

        if (isset($eventData['projectid_id'])) {
            $eventData['projectid'] = $eventData['projectid_id'];
            unset($eventData['projectid_id']);
        }

        $eventData['openedby'] = $this->innomaticContainer->getCurrentUser()->getUserId();
        $eventData['assignedto'] = $this->innomaticContainer->getCurrentUser()->getUserId();

        if ($task->Create($eventData)) {
            $GLOBALS['innowork-tasks']['newtaskid'] = $task->mItemId;
            $this->status = $this->localeCatalog->getStr('task_created.status');
        } else {
            $this->status = $this->localeCatalog->getStr('task_not_created.status');
        }

        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeEdittask($eventData)
    {
    	require_once('innowork/projects/InnoworkTask.php');

        $task = new InnoworkTask(
            $this->innomaticContainer->getDataAccess(),
            $this->innomaticContainer->getCurrentDomain()->getDataAccess(),
            $eventData['id']
        );

        if (isset($eventData['impediment']) and $eventData['impediment'] == 'on') {
            $eventData['impediment'] = 'true';
        } else {
            $eventData['impediment'] = 'false';
        }

        if ($task->Edit($eventData)) {
            $this->status = $this->localeCatalog->getStr('task_updated.status');
        } else {
            $this->status = $this->localeCatalog->getStr('task_not_updated.status');
        }

        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeTrashtask($eventData)
    {
        require_once('innowork/projects/InnoworkTask.php');

        $task = new InnoworkTask(
            $this->innomaticContainer->getDataAccess(),
            $this->innomaticContainer->getCurrentDomain()->getDataAccess(),
            $eventData['id']
        );

        if ($task->trash($this->innomaticContainer->getCurrentUser()->getUserId())) {
            $this->status = $this->localeCatalog->getStr('task_trashed.status');
        } else {
            $this->status = $this->localeCatalog->getStr('task_not_trashed.status');
        }

        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeNewmessage($eventData)
    {
        require_once('innowork/projects/InnoworkTask.php');

        $task = new InnoworkTask(
            $this->innomaticContainer->getDataAccess(),
            $this->innomaticContainer->getCurrentDomain()->getDataAccess(),
            $eventData['taskid']
        );

        if ($task->addMessage(
            $this->innomaticContainer->getCurrentUser()->getUserName(),
            $eventData['content']
        )) {
            $this->status = $this->localeCatalog->getStr('message_created.status');
        } else {
            $this->status = $this->localeCatalog->getStr('message_not_created.status');
        }

        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeRemovemessage($eventData)
    {
        require_once('innowork/projects/InnoworkTask.php');

        $task = new InnoworkTask(
            $this->innomaticContainer->getDataAccess(),
            $this->innomaticContainer->getCurrentDomain()->getDataAccess(),
            $eventData['taskid']
        );

        if ($task->removeMessage($eventData['messageid'])) $this->status = $this->localeCatalog->getStr('message_removed.status');
        else $this->status = $this->localeCatalog->getStr('message_not_removed.status');

        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeErasefilter($eventData) 
    {
        $filter_sk = new WuiSessionKey('customer_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('project_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('priority_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('status_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('resolution_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('type_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('year_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('month_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('day_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('openedby_filter', array('value' => ''));
        $filter_sk = new WuiSessionKey('assignedto_filter', array('value' => ''));
    }

    /**
     * Ajax for change view with new list of user stories.
     *
     * @param string $project_id_selected identify project selected.
     * 
     * @return void
     */
    public function ajaxOnChangeListUserStories($project_id_selected) 
    {
        $objResponse = new XajaxResponse();

        $innomaticContainer = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $localeCatalog = new LocaleCatalog(
            'innowork-projects::tasks_domain_main',
            $innomaticContainer->getCurrentUser()->getLanguage()
        );

        // Check if inowork-userstories is installed and enabled
        $appDeps = new \Innomatic\Application\ApplicationDependencies(
            $innomaticContainer->getDataAccess()
        );

        if ($appDeps->isEnabled('innowork-userstories', $innomaticContainer->getCurrentDomain()->domainid)) {
            $userStoriesAvailable = true;

            require_once('innowork/userstories/InnoworkUserStory.php');
            $innoworkUserStories = new InnoworkUserStory(
                $innomaticContainer->getDataAccess(),
                $innomaticContainer->getCurrentDomain()->getDataAccess()
            );
            $userStoriesSearchResult = $innoworkUserStories->search(
                array('projectid' => $project_id_selected),
                $innomaticContainer->getCurrentUser()->getUserId()
            );
            $userStories['0'] = $localeCatalog->getStr('nouserstory.label');
            while (list($id, $fields) = each($userStoriesSearchResult)) {
                $userStories[$id] = $fields['title'];
            }
        } else {
            $userStoriesAvailable = false;
        }

        $xml = '';
        if ($userStoriesAvailable) {
            $xml = '<horizgroup>
                      <args>
                        <align>middle</align>
                        <width>0%</width>
                      </args>
                      <children>

                        <label>
                          <args>
                            <label>'.$localeCatalog->getStr('userstory.label').'</label>
                          </args>
                        </label>

                        <combobox><name>userstoryid</name>
                          <args>
                            <disp>action</disp>
                            <elements type="array">'.WuiXml::encode($userStories).'</elements>
                            <default>'.$userStories['0'].'</default>
                          </args>
                        </combobox>
                      </children>
                    </horizgroup>';

        }

        $html = WuiXml::getContentFromXml('', $xml);

        $objResponse->addAssign("div_list_userstories", "innerHTML", $html);

        return $objResponse;
    }

}
