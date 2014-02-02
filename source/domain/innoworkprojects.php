<?php
// ----- Initialization -----
//

require_once('innowork/projects/InnoworkProject.php');
require_once('innowork/projects/InnoworkProjectField.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/widgets/WuiWidget.php');
require_once('innomatic/wui/widgets/WuiContainerWidget.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');
require_once('innomatic/wui/dispatch/WuiEvent.php');
require_once('innomatic/wui/dispatch/WuiEventRawData.php');
require_once('innomatic/wui/dispatch/WuiDispatcher.php');
require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php'); 
require_once('shared/wui/WuiSessionkey.php');
require_once('innowork/groupware/InnoworkCompany.php');

    global $gLocale, $gPage_title, $gXml_def, $gPage_status;
    global $gMain_disp;
function project_cdata($data) {
    return '<![CDATA['.$data.']]>';
}

require_once('innowork/core/InnoworkCore.php');
$gInnowork_core = InnoworkCore::instance('innoworkcore', 
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
    );

$gLocale = new LocaleCatalog(
    'innowork-projects::projects_main',
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
    );

$gWui = Wui::instance('wui');
$gWui->loadWidget( 'xml' );
$gWui->loadWidget( 'innomaticpage' );
$gWui->loadWidget( 'innomatictoolbar' );
$gWui->loadWidget( 'table' );

$gXml_def = $gPage_status = '';
$gPage_title = $gLocale->getStr( 'projects.title' );
$gCore_toolbars = $gInnowork_core->getMainToolBar(
    '',
    'project',
    isset(Wui::instance('wui')->parameters['wui']['view']['evd']['id'] ) ? Wui::instance('wui')->parameters['wui']['view']['evd']['id'] : ''
    );
$gToolbars['projects'] = array(
    'projects' => array(
        'label' => $gLocale->getStr( 'projects.toolbar' ),
        'themeimage' => 'listdetailed',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( '', array( array(
            'view',
            'default',
                array( 'done' => 'false' ) ) ) )
        ),
    'doneprojects' => array(
        'label' => $gLocale->getStr( 'doneprojects.toolbar' ),
        'themeimage' => 'listbulletleft',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( '', array( array(
            'view',
            'default',
            array( 'done' => 'true' ) ) ) )
        ),
    'newproject' => array(
        'label' => $gLocale->getStr( 'newproject.toolbar' ),
        'themeimage' => 'mathadd',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( '', array( array(
            'view',
            'newproject',
            '' ) ) )
        )
    );

$gToolbars['stats'] = array(
    'stats' => array(
        'label' => $gLocale->getStr( 'statistics.toolbar' ),
        'themeimage' => 'graph1',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( 'innoworkprojects', array( array(
            'view',
            'stats',
            '' ) ) )
        )
    );

$gToolbars['prefs'] = array(
    'prefs' => array(
        'label' => $gLocale->getStr( 'preferences.toolbar' ),
        'themeimage' => 'settings1',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( 'innoworkprojectsprefs', array( array(
            'view',
            'default',
            '' ) ) )
        )
    );

// ----- Action dispatcher -----
//
$gAction_disp = new WuiDispatcher( 'action' );

$gAction_disp->addEvent('erasefilter', 'action_erasefilter');
function action_erasefilter($eventData) {
	$customer_filter_sk = new WuiSessionKey('customer_filter', array('value' => ''));
	$priority_filter_sk = new WuiSessionKey('priority_filter', array('value' => ''));
	$status_filter_sk = new WuiSessionKey('status_filter', array('value' => ''));
	$type_filter_sk = new WuiSessionKey('type_filter', array('value' => ''));
}

$gAction_disp->addEvent(
    'newproject',
    'action_newproject'
    );
function action_newproject( $eventData )
{
    global $gLocale, $gPage_status;

    $innowork_project = new InnoworkProject(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

    if ( $innowork_project->Create(
        $eventData,
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        ) ) $gPage_status = $gLocale->getStr( 'project_added.status' );
    else $gPage_status = $gLocale->getStr( 'project_not_added.status' );
}

$gAction_disp->addEvent(
    'editproject',
    'action_editproject'
    );
function action_editproject( $eventData )
{
    global $gLocale, $gPage_status;

    $innowork_project = new InnoworkProject(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['id']
        );
    
    if ( $innowork_project->edit(
        $eventData,
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        ) ) $gPage_status = $gLocale->getStr( 'project_updated.status' );
    else $gPage_status = $gLocale->getStr( 'project_not_updated.status' );
    
    $app_deps = new \Innomatic\Application\ApplicationDependencies(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()
    );

    // Timesheet customer reporting installed?
    if ($app_deps->isInstalled('innowork-timesheet-customer-reporting')) {
        $users_query = \Innowork\Timesheet\Timesheet::getTimesheetUsers();
        $users = array();
        
        while (!$users_query->eof) {
            $fee_id = 'fee_'.$users_query->getFields('id');
            if (isset($eventData[$fee_id])) {
                \Innowork\Timesheet\TimesheetCustomerReportingUtils::setProjectFee($eventData['id'], $users_query->getFields( 'id' ), $eventData[$fee_id]);
            }
        
            $users_query->moveNext();
        }
    }
}

$gAction_disp->addEvent(
    'removeproject',
    'action_removeproject'
);
function action_removeproject( $eventData )
{
    global $gLocale, $gPage_status;

    $innowork_project = new InnoworkProject(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['id']
    );

    if ( $innowork_project->Remove(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        ) ) $gPage_status = $gLocale->getStr( 'project_removed.status' );
    else $gPage_status = $gLocale->getStr( 'project_not_removed.status' );
}

$gAction_disp->addEvent(
	'newtsrow',
	'action_newtsrow'
);
function action_newtsrow(
	$eventData
) {

	$timesheet = new \Innowork\Timesheet\Timesheet(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
	
	$locale_country = new LocaleCountry(
			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry()
	);

	$date_array = $locale_country->getDateArrayFromShortDatestamp($eventData['date']);

	$timesheet->addTimesheetRow(
			InnoworkProject::ITEM_TYPE,
			$eventData['projectid'],
			$eventData['user'],
			$date_array,
			$eventData['activitydesc'],
			$eventData['timespent'],
			$eventData['cost'],
			$eventData['costtype'],
			''
	);
}

$gAction_disp->addEvent(
		'changetsrow',
		'action_changetsrow'
);
function action_changetsrow(
		$eventData
) {
	$timesheet = new \Innowork\Timesheet\Timesheet(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

	$locale_country = new LocaleCountry(
			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry()
	);

	$date_array = $locale_country->getDateArrayFromShortDatestamp($eventData['date']);

	$timesheet->changeTimesheetRow(
			$eventData['rowid'],
			$eventData['user'],
			$date_array,
			$eventData['activitydesc'],
			$eventData['timespent'],
			$eventData['cost'],
			$eventData['costtype']
	);
}

$gAction_disp->addEvent(
		'removetsrow',
		'action_removetsrow'
);
function action_removetsrow(
		$eventData
) {
	$timesheet = new \Innowork\Timesheet\Timesheet(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
	$timesheet->deleteTimesheetRow($eventData['rowid']);
}

$gAction_disp->addEvent(
		'consolidate',
		'action_consolidate'
);
function action_consolidate(
		$eventData
) {
	global $gPage_status, $gLocale;

	$timesheet = new \Innowork\Timesheet\Timesheet(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
	$timesheet->consolidateTimesheetRow($eventData['rowid']);
}

$gAction_disp->addEvent(
		'unconsolidate',
		'action_unconsolidate'
);
function action_unconsolidate(
		$eventData
) {
	$timesheet = new \Innowork\Timesheet\Timesheet(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
	$timesheet->unconsolidateTimesheetRow( $eventData['rowid'] );
}

$gAction_disp->Dispatch();

// ----- Main dispatcher -----
//
$gMain_disp = new WuiDispatcher( 'view' );

function projects_list_action_builder( $pageNumber )
{
    return WuiEventsCall::buildEventsCallString( '', array( array(
            'view',
            'default',
            array( 'pagenumber' => $pageNumber )
        ) ) );
}

$gMain_disp->addEvent(
    'default',
    'main_default'
    );
function main_default( $eventData )
{
    global $gLocale, $gPage_title, $gXml_def, $gPage_status;

    $innowork_customers = new InnoworkCompany(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
    $search_results = $innowork_customers->Search(
        '',
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $customers['0'] = $gLocale->getStr( 'nocustomer.label' );
    while ( list( $id, $fields ) = each( $search_results ) )
    {
        $customers[$id] = $fields['companyname'];
    }

    $statuses = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_STATUS );
    $statuses['0'] = $gLocale->getStr( 'nostatus.label' );

    $priorities = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_PRIORITY );
    $priorities['0'] = $gLocale->getStr( 'nopriority.label' );

    $types = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_TYPE );
    $types['0'] = $gLocale->getStr( 'notype.label' );

    // Filtering

    if ( isset($eventData['filter'] ) )
    {
        // Customer

        $customer_filter_sk = new WuiSessionKey(
            'customer_filter',
            array(
                'value' => $eventData['filter_customerid']
                )
            );

        if ( $eventData['filter_customerid'] != 0 ) $search_keys['customerid'] = $eventData['filter_customerid'];

        // Priority

        $priority_filter_sk = new WuiSessionKey(
            'priority_filter',
            array(
                'value' => $eventData['filter_priorityid']
                )
            );

        if ( $eventData['filter_priorityid'] != 0 ) $search_keys['priority'] = $eventData['filter_priorityid'];

        // Status

        $status_filter_sk = new WuiSessionKey(
            'status_filter',
            array(
                'value' => $eventData['filter_statusid']
                )
            );

        if ( $eventData['filter_statusid'] != 0 ) $search_keys['status'] = $eventData['filter_statusid'];

        // Type

        $type_filter_sk = new WuiSessionKey(
            'type_filter',
            array(
                'value' => $eventData['filter_typeid']
                )
            );

        if ( $eventData['filter_typeid'] != 0 ) $search_keys['type'] = $eventData['filter_typeid'];

    }
    else
    {
        // Customer

        $customer_filter_sk = new WuiSessionKey( 'customer_filter' );
        if (
            strlen( $customer_filter_sk->mValue )
            and $customer_filter_sk->mValue != 0
            ) $search_keys['customerid'] = $customer_filter_sk->mValue;
        $eventData['filter_customerid'] = $customer_filter_sk->mValue;

        // Priority

        $priority_filter_sk = new WuiSessionKey( 'priority_filter' );
        if (
            strlen( $priority_filter_sk->mValue )
            and $priority_filter_sk->mValue != 0
            ) $search_keys['priority'] = $priority_filter_sk->mValue;
        $eventData['filter_priorityid'] = $priority_filter_sk->mValue;

        // Status

        $status_filter_sk = new WuiSessionKey( 'status_filter' );
        if (
            strlen( $status_filter_sk->mValue )
            and $status_filter_sk->mValue != 0
            ) $search_keys['status'] = $status_filter_sk->mValue;
        $eventData['filter_statusid'] = $status_filter_sk->mValue;

        // Type

        $type_filter_sk = new WuiSessionKey( 'type_filter' );
        if (
            strlen( $type_filter_sk->mValue )
            and $type_filter_sk->mValue != 0
            ) $search_keys['type'] = $type_filter_sk->mValue;
        $eventData['filter_typeid'] = $type_filter_sk->mValue;
    }

    if ( !isset($search_keys ) or !count( $search_keys ) ) $search_keys = '';

    // Sorting

    $tab_sess = new WuiSessionKey( 'innoworkprojecttab' );

    if ( !isset($eventData['done'] ) ) $eventData['done'] = $tab_sess->mValue;
    if ( !strlen( $eventData['done'] ) ) $eventData['done'] = 'false';

    $tab_sess = new WuiSessionKey(
        'innoworkprojecttab',
        array(
            'value' => $eventData['done']
            )
        );

    $table = new WuiTable( 'projects_done_'.$eventData['done'], array(
        'sessionobjectusername' => $eventData['done'] == 'true' ? 'done' : 'undone'
        ) );
    $sort_by = 0;
    if ( strlen( $table->mSortDirection ) ) $sort_order = $table->mSortDirection;
    else $sort_order = 'down';

    if ( isset($eventData['sortby'] ) )
    {
        if ( $table->mSortBy == $eventData['sortby'] )
        {
            $sort_order = $sort_order == 'down' ? 'up' : 'down';
        }
        else
        {
            $sort_order = 'down';
        }

        $sort_by = $eventData['sortby'];
    }
    else
    {
        if ( strlen( $table->mSortBy ) ) $sort_by = $table->mSortBy;
    }

    $headers[0]['label'] = $gLocale->getStr( 'project_nr.header' );
    $headers[0]['link'] = WuiEventsCall::buildEventsCallString(
        '',
            array(
                array(
                    'view',
                    'default',
                    array(
                        'sortby' => '0'
                        )
                    )
                )
        );
    $headers[1]['label'] = $gLocale->getStr( 'name.header' );
    $headers[1]['link'] = WuiEventsCall::buildEventsCallString(
        '',
            array(
                array(
                    'view',
                    'default',
                    array(
                        'sortby' => '1'
                        )
                    )
                )
        );
    $headers[2]['label'] = $gLocale->getStr( 'customer.header' );
    $headers[2]['link'] = WuiEventsCall::buildEventsCallString(
        '',
            array(
                array(
                    'view',
                    'default',
                    array(
                        'sortby' => '2'
                        )
                    )
                )
        );

    $headers[3]['label'] = $gLocale->getStr( 'priority.header' );
    $headers[3]['link'] = WuiEventsCall::buildEventsCallString( '',
            array( array(
                    'view',
                    'default',
                    array( 'sortby' => '3' )
                    ) ) );
    $headers[4]['label'] = $gLocale->getStr( 'status.header' );
    $headers[4]['link'] = WuiEventsCall::buildEventsCallString( '',
            array( array(
                    'view',
                    'default',
                    array( 'sortby' => '4' )
                    ) ) );
    $headers[5]['label'] = $gLocale->getStr( 'type.header' );
    $headers[5]['link'] = WuiEventsCall::buildEventsCallString( '',
            array( array(
                    'view',
                    'default',
                    array( 'sortby' => '5' )
                    ) ) );

    $projects = new InnoworkProject(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

    switch ($sort_by) {
    case '0':
        $projects->mSearchOrderBy = 'id'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    case '1':
        $projects->mSearchOrderBy = 'name'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    case '2':
        $projects->mSearchOrderBy = 'customerid'.( $sort_order == 'up' ? ' DESC' : '' ).',name';
        break;
    case '3':
        $projects->mSearchOrderBy = 'priority'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    case '4':
        $projects->mSearchOrderBy = 'status'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    case '5':
        $projects->mSearchOrderBy = 'type'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    }

        if (
            isset($eventData['done'] )
            and $eventData['done'] == 'true'
            )
        {
            $done_check = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmttrue;
            $done_icon = 'misc3';
            $done_action = 'false';
            $done_label = 'setundone.button';
        }
        else
        {
            $done_check = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse;
            $done_icon = 'drawer';
            $done_action = 'true';
            $done_label = 'setdone.button';
        }

    $search_keys['done'] = $done_check;
    
    $search_results = $projects->Search(
        $search_keys,
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );
        
    $num_projects = count( $search_results );

        $gXml_def =
'<vertgroup><name>projects</name>
  <children>

    <label><name>filter</name>
      <args>
        <bold>true</bold>
        <label>'.$gLocale->getStr( 'filter.label' ).'</label>
      </args>
    </label>

    <form><name>filter</name>
      <args>
            <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    array(
                        'filter' => 'true'
                        )
                    )
            ) ) ).'</action>
      </args>
      <children>

        <grid>
          <children>

        <button row="0" col="4"><name>filter</name>
          <args>
            <themeimage>zoom</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <formsubmit>filter</formsubmit>
            <label>'.$gLocale->getStr( 'filter.button' ).'</label>
            <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    array(
                        'filter' => 'true'
                        )
                    )
            ) ) ).'</action>
          </args>
        </button>
        <button row="1" col="4"><name>filter</name>
          <args>
            <themeimage>buttoncancel</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <formsubmit>filter</formsubmit>
            <label>'.$gLocale->getStr( 'erase_filter.button' ).'</label>
            <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    array(
                        )
                    ),
            		array(
            				'action',
            				'erasefilter',
            				array(
            				)
            		)
            ) ) ).'</action>
          </args>
        </button>
    <label row="0" col="0"><name>customer</name>
      <args>
        <label>'.$gLocale->getStr( 'filter_customer.label' ).'</label>
      </args>
    </label>
    <combobox row="0" col="1"><name>filter_customerid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode( $customers ).'</elements>
        <default>'.( isset($eventData['filter_customerid'] ) ? $eventData['filter_customerid'] : '' ).'</default>
      </args>
    </combobox>

    <label row="0" col="2">
      <args>
        <label>'.$gLocale->getStr( 'filter_priority.label' ).'</label>
      </args>
    </label>
    <combobox row="0" col="3"><name>filter_priorityid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode( $priorities ).'</elements>
        <default>'.(isset($eventData['filter_priorityid'] ) ? $eventData['filter_priorityid'] : '' ).'</default>
      </args>
    </combobox>

    <label row="1" col="2">
      <args>
        <label>'.$gLocale->getStr( 'filter_status.label' ).'</label>
      </args>
    </label>
    <combobox row="1" col="3"><name>filter_statusid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode( $statuses ).'</elements>
        <default>'.(isset($eventData['filter_statusid'] ) ? $eventData['filter_statusid'] : '' ).'</default>
      </args>
    </combobox>

    <label row="2" col="2">
      <args>
        <label>'.$gLocale->getStr( 'filter_type.label' ).'</label>
      </args>
    </label>
    <combobox row="2" col="3"><name>filter_typeid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode( $types ).'</elements>
        <default>'.(isset($eventData['filter_typeid'] ) ? $eventData['filter_typeid'] : '' ).'</default>
      </args>
    </combobox>

          </children>
        </grid>

      </children>
    </form>

    <horizbar/>

    <label><name>title</name>
      <args>
        <bold>true</bold>
        <label>'.( $gLocale->getStr(
            ( isset($eventData['done'] )
            and $eventData['done'] == 'true' ) ? 'doneprojects.label' : 'projects.label' ) ).'</label>
      </args>
    </label>

    <table><name>projects_done_'.$eventData['done'].'</name>
      <args>
        <headers type="array">'.WuiXml::encode( $headers ).'</headers>
        <rowsperpage>15</rowsperpage>
        <pagesactionfunction>projects_list_action_builder</pagesactionfunction>
        <pagenumber>'.( isset($eventData['pagenumber'] ) ? $eventData['pagenumber'] : '' ).'</pagenumber>
        <sessionobjectusername>'.( $eventData['done'] == 'true' ? 'done' : 'undone' ).'</sessionobjectusername>
        <sortby>'.$sort_by.'</sortby>
        <sortdirection>'.$sort_order.'</sortdirection>
        <rows>'.$num_projects.'</rows>
      </args>
      <children>
';

        $innowork_core = InnoworkCore::instance('innoworkcore', 
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
            );

        $summaries = $innowork_core->getSummaries();

        $row = 0;
        $page = 1;

                    if ( isset($eventData['pagenumber'] ) )
                    {
                        $page = $eventData['pagenumber'];
                    }
                    else
                    {
						require_once('shared/wui/WuiTable.php');
                    	
                        $table = new WuiTable(
                            'projects_done_'.$eventData['done'],
                            array(
                                'sessionobjectusername' => $eventData['done'] == 'true' ? 'done' : 'undone'
                                )
                            );

                        $page = $table->mPageNumber;
                    }

                    if ( $page > ceil( $num_projects / 15 ) ) $page = ceil( $num_projects / 15 );

                    $from = ( $page * 15 ) - 15;
                    $to = $from + 15 - 1;
        

        while ( list( $id, $fields ) = each( $search_results ) )
        {
if ( $row >= $from and $row <= $to )
{

            $innowork_customer = new InnoworkCompany(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                $fields['customerid']
                );

            $cust_data = $innowork_customer->getItem();

            if ( $fields['done'] == $done_check )
            {
                $gXml_def .=
'<label row="'.$row.'" col="0">
  <args>
    <label>'.project_cdata( $fields['id'] ).'</label>
  </args>
</label>

<vertgroup row="'.$row.'" col="1">
  <args>
    <compact>true</compact>
  </args>
  <children>

<link>
  <args>
    <label>'.project_cdata( $fields['name'] ).'</label>
    <bold>true</bold>
    <nowrap>true</nowrap>
    <compact>true</compact>
    <link>'.WuiEventsCall::buildEventsCallString( '', array( array(
                    'view',
                    'showproject',
                    array( 'id' => $id ) ) ) ).'</link>
    </args>
</link>
<label>
  <args>
    <label>'.project_cdata( $fields['description'] ).'</label>
    <nowrap>false</nowrap>
    <compact>true</compact>
  </args>
</label>
  </children>
</vertgroup>

    		<link row="'.$row.'" col="2"><name>customer</name>
  <args>
    <link>'.project_cdata( WuiEventsCall::buildEventsCallString(
        $summaries['directorycompany']['domainpanel'],
        array(
            array(
                $summaries['directorycompany']['showdispatcher'],
                $summaries['directorycompany']['showevent'],
                array( 'id' => $fields['customerid'] )
                )
            )
        ) ).'</link>
    <label>'.project_cdata( $cust_data['companyname'] ).'</label>
  </args>
</link>
    		
<label row="'.$row.'" col="3">
  <args>
    <label>'.project_cdata( $priorities[$fields['priority']] ).'</label>
  </args>
</label>
<label row="'.$row.'" col="4">
  <args>
    <label>'.project_cdata( $statuses[$fields['status']] ).'</label>
  </args>
</label>
<label row="'.$row.'" col="5">
  <args>
    <label>'.project_cdata( $types[$fields['type']] ).'</label>
  </args>
</label>

<innomatictoolbar row="'.$row.'" col="6"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode( array(
        'view' => array(
            'show' => array(
                'label' => $gLocale->getStr( 'showproject.button' ),
                'themeimage' => 'zoom',
                'themeimagetype' => 'mini',
                'horiz' => 'true',
                'action' => WuiEventsCall::buildEventsCallString( '', array( array(
                    'view',
                    'showproject',
                    array( 'id' => $id ) ) ) )
                ),
            'done' => array(
                'label' => $gLocale->getStr( $done_label ),
                'themeimage' => $done_icon,
                'themeimagetype' => 'mini',
                'horiz' => 'true',
                'action' => WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                ),
                array(
                    'action',
                    'editproject',
                    array( 'id' => $id, 'done' => $done_action ) ) ) )
                ),
            'remove' => array(
                'label' => $gLocale->getStr( 'removeproject.button' ),
                'themeimage' => 'trash',
                'themeimagetype' => 'mini',
                'horiz' => 'true',
                'needconfirm' => 'true',
                'confirmmessage' => $gLocale->getStr( 'removeproject.confirm' ),
                'action' => WuiEventsCall::buildEventsCallString( '', array(
                    array(
                        'view',
                        'default',
                        ''
                    ),
                    array(
                        'action',
                        'removeproject',
                        array( 'id' => $id ) ) ) )
        ) ) ) ).'</toolbars>
  </args>
</innomatictoolbar>';
                }
            }
            $row++;
        }

        $gXml_def .=
'      </children>
    </table>
  </children>
</vertgroup>';

}

$gMain_disp->addEvent(
    'newproject',
    'main_newproject'
    );
function main_newproject( $eventData )
{
    global $gXml_def, $gLocale, $gPage_title;

        $innowork_companies = new InnoworkCompany(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
            );
        $search_results = $innowork_companies->Search(
            '',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
            );

        $companies['0'] = $gLocale->getStr( 'nocompany.label' );

        while ( list( $id, $fields ) = each( $search_results ) )
        {
            $companies[$id] = $fields['companyname'];
        }

    $users_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->Execute(
        'SELECT id,fname,lname,username '.
        'FROM domain_users '.
        'WHERE username<>'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->formatText( User::getAdminUsername(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId())).' '.
        'ORDER BY lname,fname' );

    $users = array();

    while ( !$users_query->eof )
    {
        $users[$users_query->getFields( 'id' )] = $users_query->getFields( 'lname' ).
            ' '.$users_query->getFields( 'fname' ).
            ' ('.$users_query->getFields( 'username' ).')';

        $users_query->moveNext();
    }

    $statuses = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_STATUS );
    $statuses['0'] = $gLocale->getStr( 'nostatus.label' );

    $priorities = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_PRIORITY );
    $priorities['0'] = $gLocale->getStr( 'nopriority.label' );

    $types = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_TYPE );
    $types['0'] = $gLocale->getStr( 'notype.label' );

    $gXml_def .=
'<vertgroup><name>newproject</name>
  <children>

    <table><name>project</name>
      <args>
        <headers type="array">'.WuiXml::encode(
            array( '0' => array(
                'label' => $gLocale->getStr( 'newproject.label' )
                ) ) ).'</headers>
      </args>
      <children>
    
    <form row="0" col="0"><name>project</name>
      <args>
        <method>post</method>
        <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                    ),
                array(
                    'action',
                    'newproject',
                    '' )
            ) ) ).'</action>
      </args>
      <children>

        <horizgroup><name>project</name>
          <children>

            <label><name>name</name>
              <args>
                <label>'.$gLocale->getStr( 'name.label' ).'</label>
              </args>
            </label>
            <string><name>name</name>
              <args>
                <disp>action</disp>
                <size>30</size>
              </args>
            </string>

          </children>
        </horizgroup>

        <horizgroup><name>project</name>
          <children>

            <label><name>description</name>
              <args>
                <label>'.( $gLocale->getStr( 'description.label' ) ).'</label>
              </args>
            </label>
          
          </children>
        </horizgroup>

        <horizgroup><name>project</name>
          <children>

            <text><name>description</name>
              <args>
                <disp>action</disp>
                <cols>80</cols>
                <rows>7</rows>
              </args>
            </text>

          </children>
        </horizgroup>

        <horizgroup><name>project</name>
          <children>';


                $gXml_def .=
'            <label><name>company</name>
              <args>
                <label>'.( $gLocale->getStr( 'customer.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>customerid</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $companies ).'</elements>
              </args>
            </combobox>';

            $gXml_def .=
'            <label><name>responsible</name>
              <args>
                <label>'.( $gLocale->getStr( 'responsible.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>responsible</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $users ).'</elements>
              </args>
            </combobox>

          </children>
        </horizgroup>

        <horizbar><name>hb</name></horizbar>

        <label><name>contact</name>
          <args>
            <bold>true</bold>
            <label>'.( $gLocale->getStr( 'parameters.label' ) ).'</label>
          </args>
        </label>

        <horizgroup><name>project</name>
          <children>

            <label><name>status</name>
              <args>
                <label>'.( $gLocale->getStr( 'status.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>status</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $statuses ).'</elements>
              </args>
            </combobox>

            <label><name>priority</name>
              <args>
                <label>'.( $gLocale->getStr( 'priority.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>priority</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $priorities ).'</elements>
              </args>
            </combobox>

            <label><name>type</name>
              <args>
                <label>'.( $gLocale->getStr( 'type.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>type</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $types ).'</elements>
              </args>
            </combobox>

          </children>
        </horizgroup>

        <horizbar><name>hb</name></horizbar>

        <label><name>estimated</name>
          <args>
            <bold>true</bold>
            <label>'.( $gLocale->getStr( 'estimated.label' ) ).'</label>
          </args>
        </label>

        <horizgroup><name>estimated</name>
          <children>

            <label><name>estimatedstartdate</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedstartdate.label' ) ).'</label>
              </args>
            </label>
            <date><name>estimatedstartdate</name>
              <args>
                <disp>action</disp>
              </args>
            </date>

            <label><name>estimatedenddate</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedenddate.label' ) ).'</label>
              </args>
            </label>
            <date><name>estimatedenddate</name>
              <args>
                <disp>action</disp>
                <size>25</size>
              </args>
            </date>

            <label><name>estimatedtime</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedtime.label' ) ).'</label>
              </args>
            </label>
            <string><name>estimatedtime</name>
              <args>
                <disp>action</disp>
                <size>5</size>
              </args>
            </string>

            <label><name>estimatedcost</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedcost.label' ) ).'</label>
              </args>
            </label>
            <string><name>estimatedcost</name>
              <args>
                <disp>action</disp>
                <size>7</size>
              </args>
            </string>

            <label><name>estimatedrevenue</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedrevenue.label' ) ).'</label>
              </args>
            </label>
            <string><name>estimatedrevenue</name>
              <args>
                <disp>action</disp>
                <size>7</size>
              </args>
            </string>

          </children>
        </horizgroup>

        <horizbar><name>hb</name></horizbar>

        <label><name>real</name>
          <args>
            <bold>true</bold>
            <label>'.( $gLocale->getStr( 'real.label' ) ).'</label>
          </args>
        </label>

        <horizgroup><name>real</name>
          <children>

            <label><name>realstartdate</name>
              <args>
                <label>'.( $gLocale->getStr( 'realstartdate.label' ) ).'</label>
              </args>
            </label>
            <date><name>realstartdate</name>
              <args>
                <disp>action</disp>
                <size>25</size>
              </args>
            </date>

            <label><name>realenddate</name>
              <args>
                <label>'.( $gLocale->getStr( 'realenddate.label' ) ).'</label>
              </args>
            </label>
            <date><name>realenddate</name>
              <args>
                <disp>action</disp>
                <size>25</size>
              </args>
            </date>

            <label><name>realtime</name>
              <args>
                <label>'.( $gLocale->getStr( 'realtime.label' ) ).'</label>
              </args>
            </label>
            <string><name>realtime</name>
              <args>
                <disp>action</disp>
                <size>5</size>
              </args>
            </string>

            <label><name>realcost</name>
              <args>
                <label>'.( $gLocale->getStr( 'realcost.label' ) ).'</label>
              </args>
            </label>
            <string><name>realcost</name>
              <args>
                <disp>action</disp>
                <size>7</size>
              </args>
            </string>

            <label><name>realrevenue</name>
              <args>
                <label>'.( $gLocale->getStr( 'realrevenue.label' ) ).'</label>
              </args>
            </label>
            <string><name>realrevenue</name>
              <args>
                <disp>action</disp>
                <size>7</size>
              </args>
            </string>

          </children>
        </horizgroup>

        </children>
        </form>

        <button row="1" col="0"><name>apply</name>
          <args>
            <themeimage>buttonok</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
                    array(
                        'view',
                        'default',
                        ''
                        ),
                    array(
                        'action',
                        'newproject',
                        '' )
                ) ) ).'</action>
            <label>'.( $gLocale->getStr( 'newproject.submit' ) ).'</label>
            <formsubmit>project</formsubmit>
          </args>
        </button>

      </children>
    </table>
  </children>
</vertgroup>';
}

$gMain_disp->addEvent(
    'showproject',
    'main_showproject'
    );
function main_showproject( $eventData )
{
    global $gXml_def, $gLocale, $gPage_title;

    $innowork_project = new InnoworkProject(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['id']
        );

    $pj_data = $innowork_project->getItem( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId() );

        $innowork_companies = new InnoworkCompany(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
            );
        $search_results = $innowork_companies->Search(
            '',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
            );

        $companies['0'] = $gLocale->getStr( 'nocompany.label' );

        while ( list( $id, $fields ) = each( $search_results ) )
        {
            $companies[$id] = $fields['companyname'];
        }

    $users_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->Execute(
        'SELECT id,fname,lname,username '.
        'FROM domain_users '.
        'WHERE username<>'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->formatText(User::getAdminUsername(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId())).' '.
        'ORDER BY lname,fname' );

    $users = array();

    while ( !$users_query->eof )
    {
        $users[$users_query->getFields( 'id' )] = $users_query->getFields( 'lname' ).
            ' '.$users_query->getFields( 'fname' ).
            ' ('.$users_query->getFields( 'username' ).')';

        $users_query->moveNext();
    }

    $statuses = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_STATUS );
    $statuses['0'] = $gLocale->getStr( 'nostatus.label' );

    $priorities = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_PRIORITY );
    $priorities['0'] = $gLocale->getStr( 'nopriority.label' );

    $types = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_TYPE );
    $types['0'] = $gLocale->getStr( 'notype.label' );

    $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );
    $empty_date_array = $country->getDateArrayFromShortDateStamp( '' );
    $empty_date_text = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getTimestampFromDateArray( $empty_date_array );

        if ( $pj_data['done'] == \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmttrue )
        {
            $done_icon = 'misc3';
            $done_action = 'false';
            $done_label = 'setundone.button';
        }
        else
        {
            $done_icon = 'drawer';
            $done_action = 'true';
            $done_label = 'setdone.button';
        }

        $tab_counter = 0;
        $tabs[$tab_counter++]['label'] = $gLocale->getStr('projectdata.tab');
        
        $app_deps = new \Innomatic\Application\ApplicationDependencies(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()
        );
        
        // Timesheet installed?
        if ($app_deps->isInstalled('innowork-timesheet')) {
        	$ts_installed = true;
        	$tabs[$tab_counter++]['label'] = $gLocale->getStr('timesheet.tab');
        } else {
        	$ts_installed = false;
        }
        
        // Timesheet customer reporting installed?
        if ($app_deps->isInstalled('innowork-timesheet-customer-reporting')) {
            $cr_installed = true;
            $tabs[$tab_counter++]['label'] = $gLocale->getStr('customer_reporting.tab');
        } else {
            $cr_installed = false;
        }
        
        $tabs[$tab_counter++]['label'] = $gLocale->getStr('otherprojects.tab');
        
    $gXml_def .=
'<horizgroup>
  <children>

<vertgroup><name>editproject</name>
  <children>

    <table><name>project</name>
      <args>
        <headers type="array">'.WuiXml::encode(
            array( '0' => array(
                'label' => $gLocale->getStr( 'editproject.label' )
                ) ) ).'</headers>
      </args>
      <children>

    <form row="0" col="0"><name>project</name>
      <args>
        <method>post</method>
        <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                    ),
                array(
                    'action',
                    'editproject',
                    array( 'id' => $eventData['id'] ) )
            ) ) ).'</action>
      </args>
      <children>

        <horizgroup><name>project</name>
          <children>

            <label><name>name</name>
              <args>
                <label>'.( $gLocale->getStr( 'name.label' ) ).'</label>
              </args>
            </label>
            <string><name>name</name>
              <args>
                <disp>action</disp>
                <size>30</size>
                <value>'.project_cdata( $pj_data['name'] ).'</value>
              </args>
            </string>

          </children>
        </horizgroup>

        <horizgroup><name>project</name>
          <children>';

                $gXml_def .=
'            <label><name>company</name>
              <args>
                <label>'.( $gLocale->getStr( 'customer.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>customerid</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $companies ).'</elements>
                <default>'.$pj_data['customerid'].'</default>
              </args>
            </combobox>';


            $gXml_def .=
'            <label><name>responsible</name>
              <args>
                <label>'.( $gLocale->getStr( 'responsible.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>responsible</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $users ).'</elements>
                <default>'.$pj_data['responsible'].'</default>
              </args>
            </combobox>

          </children>
        </horizgroup>
                		


            <horizgroup><name>tabs</name>
              <children>
    
                <tab><name>extras</name>
                  <args>
                    <tabs type="array">'.WuiXml::encode($tabs).'</tabs>
                    <tabactionfunction>project_extras_tab_builder</tabactionfunction>
                    <activetab>'. (isset($eventData['extrastab']) ? $eventData['extrastab'] : '0').'</activetab>
                  </args>
                  <children>
    
<!-- Project data -->

					<vertgroup><children>
        <horizgroup><name>project</name>
          <children>

            <label><name>description</name>
              <args>
                <label>'.( $gLocale->getStr( 'description.label' ) ).'</label>
              </args>
            </label>

          </children>
        </horizgroup>

        <horizgroup><name>project</name>
          <children>

            <text><name>description</name>
              <args>
                <disp>action</disp>
                <cols>80</cols>
                <rows>7</rows>
                <value>'.project_cdata( $pj_data['description'] ).'</value>
              </args>
            </text>

          </children>
        </horizgroup>


        <horizbar><name>hb</name></horizbar>

        <label><name>contact</name>
          <args>
            <bold>true</bold>
            <label>'.( $gLocale->getStr( 'parameters.label' ) ).'</label>
          </args>
        </label>

        <horizgroup><name>project</name>
          <children>

            <label><name>status</name>
              <args>
                <label>'.( $gLocale->getStr( 'status.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>status</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $statuses ).'</elements>
                <default>'.$pj_data['status'].'</default>
              </args>
            </combobox>

            <label><name>priority</name>
              <args>
                <label>'.( $gLocale->getStr( 'priority.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>priority</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $priorities ).'</elements>
                <default>'.$pj_data['priority'].'</default>
              </args>
            </combobox>

            <label><name>type</name>
              <args>
                <label>'.( $gLocale->getStr( 'type.label' ) ).'</label>
              </args>
            </label>
            <combobox><name>type</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $types ).'</elements>
                <default>'.$pj_data['type'].'</default>
              </args>
            </combobox>

          </children>
        </horizgroup>

        <horizbar><name>hb</name></horizbar>

        <label><name>estimated</name>
          <args>
            <bold>true</bold>
            <label>'.( $gLocale->getStr( 'estimated.label' ) ).'</label>
          </args>
        </label>

        <horizgroup><name>estimated</name>
          <children>

            <label><name>estimatedstartdate</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedstartdate.label' ) ).'</label>
              </args>
            </label>
            <date><name>estimatedstartdate</name>
              <args>
                <disp>action</disp>
                '.( $pj_data['estimatedstartdate'] != $empty_date_text ? '<value type="array">'.WuiXml::encode( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp( $pj_data['estimatedstartdate'] ) ).'</value>' : '' ).'
              </args>
            </date>

            <label><name>estimatedenddate</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedenddate.label' ) ).'</label>
              </args>
            </label>
            <date><name>estimatedenddate</name>
              <args>
                <disp>action</disp>
                <size>25</size>
                '.( $pj_data['estimatedenddate'] != $empty_date_text ? '<value type="array">'.WuiXml::encode( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp( $pj_data['estimatedenddate'] ) ).'</value>' : '' ).'
              </args>
            </date>

            <label><name>estimatedtime</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedtime.label' ) ).'</label>
              </args>
            </label>
            <string><name>estimatedtime</name>
              <args>
                <disp>action</disp>
                <size>5</size>
                <value>'.project_cdata( $pj_data['estimatedtime'] ).'</value>
              </args>
            </string>

            <label><name>estimatedcost</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedcost.label' ) ).'</label>
              </args>
            </label>
            <string><name>estimatedcost</name>
              <args>
                <disp>action</disp>
                <size>7</size>
                <value>'.project_cdata( $pj_data['estimatedcost'] ).'</value>
              </args>
            </string>

            <label><name>estimatedrevenue</name>
              <args>
                <label>'.( $gLocale->getStr( 'estimatedrevenue.label' ) ).'</label>
              </args>
            </label>
            <string><name>estimatedrevenue</name>
              <args>
                <disp>action</disp>
                <size>7</size>
                <value>'.project_cdata( $pj_data['estimatedrevenue'] ).'</value>
              </args>
            </string>

          </children>
        </horizgroup>

        <horizbar><name>hb</name></horizbar>

        <label><name>real</name>
          <args>
            <bold>true</bold>
            <label>'.( $gLocale->getStr( 'real.label' ) ).'</label>
          </args>
        </label>

        <horizgroup><name>real</name>
          <children>

            <label><name>realstartdate</name>
              <args>
                <label>'.( $gLocale->getStr( 'realstartdate.label' ) ).'</label>
              </args>
            </label>
            <date><name>realstartdate</name>
              <args>
                <disp>action</disp>
                <size>25</size>
                '.( $pj_data['realstartdate'] != $empty_date_text ? '<value type="array">'.WuiXml::encode( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp( $pj_data['realstartdate'] ) ).'</value>' : '' ).'
              </args>
            </date>

            <label><name>realenddate</name>
              <args>
                <label>'.( $gLocale->getStr( 'realenddate.label' ) ).'</label>
              </args>
            </label>
            <date><name>realenddate</name>
              <args>
                <disp>action</disp>
                <size>25</size>
                '.( $pj_data['realenddate'] != $empty_date_text ? '<value type="array">'.WuiXml::encode( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp( $pj_data['realenddate'] ) ).'</value>' : '' ).'
              </args>
            </date>

            <label><name>realtime</name>
              <args>
                <label>'.( $gLocale->getStr( 'realtime.label' ) ).'</label>
              </args>
            </label>
            <string><name>realtime</name>
              <args>
                <disp>action</disp>
                <size>5</size>
                <value>'.project_cdata( $pj_data['realtime'] ).'</value>
              </args>
            </string>

            <label><name>realcost</name>
              <args>
                <label>'.( $gLocale->getStr( 'realcost.label' ) ).'</label>
              </args>
            </label>
            <string><name>realcost</name>
              <args>
                <disp>action</disp>
                <size>7</size>
                <value>'.project_cdata( $pj_data['realcost'] ).'</value>
              </args>
            </string>

            <label><name>realrevenue</name>
              <args>
                <label>'.( $gLocale->getStr( 'realrevenue.label' ) ).'</label>
              </args>
            </label>
            <string><name>realrevenue</name>
              <args>
                <disp>action</disp>
                <size>7</size>
                <value>'.project_cdata( $pj_data['realrevenue'] ).'</value>
              </args>
            </string>

          </children>
        </horizgroup>
                    </children></vertgroup>';
            
        // Timesheet tab
        
		if ($ts_installed) {
            $gXml_def .= '
                    <vertgroup>
                      <children>

        <iframe><name>timesheet</name>
          <args>
            <width>1200</width>
            <height>450</height>
            <source>'.project_cdata(
                WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'timesheet',
                            array(
                                'projectid' => $eventData['id']
                                )
                            )
                        )
                    )
                ).'</source>
            <scrolling>auto</scrolling>
          </args>
        </iframe>
                		
    
                      </children>
                    </vertgroup>';
		}
		
		if ($cr_installed) {
		    $default_fees = \Innowork\Timesheet\TimesheetCustomerReportingUtils::getDefaultFees();
		    $fees = \Innowork\Timesheet\TimesheetCustomerReportingUtils::getProjectFees($eventData['id']);
		    
		    $fees_headers[0]['label'] = $gLocale->getStr('fee_user.header');
		    $fees_headers[1]['label'] = $gLocale->getStr('fee_defaultfee.header');;
		    $fees_headers[2]['label'] = $gLocale->getStr('fee_projectfee.header');;
		    
		    $gXml_def .= '<vertgroup><children>
		        
        <horizgroup><args><width>0%</width></args>
          <children>
              <label><name>sendtscustomerreport</name>
                <args><label>'.$gLocale->getStr('send_timesheet_customer_report.label').'</label></args>
              </label>
                		
                <radio><name>sendtscustomerreport</name>
                  <args>
                    <disp>action</disp>
                    <value>true</value>
                    <label>'.( $gLocale->getStr( 'sendtscsrep_yes.label' ) ).'</label>
                    <checked>'.( $pj_data['sendtscustomerreport'] != InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->fmtfalse ? 'true' : 'false' ).'</checked>
                  </args>
                </radio>

                <radio><name>sendtscustomerreport</name>
                  <args>
                    <disp>action</disp>
                    <value>false</value>
                    <label>'.( $gLocale->getStr( 'sendtscsrep_not.label' ) ).'</label>
                    <checked>'.( $pj_data['sendtscustomerreport'] == InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->fmtfalse ? 'true' : 'false' ).'</checked>
                  </args>
                </radio>
                    		
          </children>
        </horizgroup>
                        
                    	<label><name>fees</name><args><label>'.WuiXml::cdata($gLocale->getStr( 'fees.label' )).'</label><bold>true</bold></args></label>
                    		
                    	<table>
      <args>
        <headers type="array">'.WuiXml::encode( $fees_headers ).'</headers>
      </args>
        		<children>';

            $users_query = \Innowork\Timesheet\Timesheet::getTimesheetUsers();
            $users = array();
            
            $fees_row = 0;

            /*
            if ($pj_data['english'] == InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->fmttrue) {
            	$fee_where = 'for';
            } else {
            	$fee_where = 'ita';	
            }
            */
            $fee_where = 'local';
            
            while ( !$users_query->eof )
            {
            	$user_id = $users_query->getFields( 'id' );
            
            	$gXml_def .= '<label row="'.$fees_row.'" col="0"><name>fee</name><args><label>'.$users_query->getFields( 'lname' ).
            	' '.$users_query->getFields( 'fname' ).'</label></args></label>
            			<label row="'.$fees_row.'" col="1"><name>fee</name><args><label>'.$default_fees[$user_id][$fee_where].'</label></args></label>
            			<string row="'.$fees_row.'" col="2"><name>fee_'.$user_id.'</name><args><disp>action</disp><size>7</size><value>'.$fees[$user_id].'</value></args></string>';
            	
            	$users_query->moveNext();
            	$fees_row++;
            }
                        
            $gXml_def .= '
        		</children>
                    		</table>
		        </children></vertgroup>';
		}

            $gXml_def .= '
                		
<!-- Related dossiers -->
                		
                    <vertgroup>
                      <children>
    <table><name>related_dossiers</name>
      <args>
        <headers type="array">'.WuiXml::encode( $tab_headers ).'</headers>
      </args>
      <children>
';

        $row = 0;

        $setdone_array = array(
        		array(
        				'view',
        				'default',
        				''
        		),
        		array(
        				'action',
        				'editproject',
        				array(
        						'id' => $eventData['id'],
        						'done' => 'true'
        				) )
        );
        
        // Related dossiers
        $dossiers_search_results = array();
        
        if ($pj_data['customerid'] != 0 and $pj_data['customerid'] != '') {
        	$innowork_dossiers = new InnoworkProject(
        			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
        			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
        	);
        	$domain_da = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess();
        	$dossiers_search_query = $domain_da->execute('SELECT * FROM innowork_projects WHERE customerid='.$pj_data['customerid'].' ORDER BY id DESC');
        	 
        	while (!$dossiers_search_query->eof) {
        		$dossiers_search_results[$dossiers_search_query->getFields('id')] = $dossiers_search_query->getFields();
        		$dossiers_search_query->moveNext();
        	}
        }
        
        while ( list( $id, $fields ) = each( $dossiers_search_results ) )
        {
			if ($id == $eventData['id']) {
				continue;
			}
        	
                $gXml_def .=
'<link row="'.$row.'" col="1">
  <args>
    <label>'.project_cdata( $fields['id'] ).'</label>
    <bold>true</bold>
    <nowrap>true</nowrap>
    <compact>true</compact>
    <link>'.WuiEventsCall::buildEventsCallString( '', array( array(
                    'view',
                    'showproject',
                    array( 'id' => $id ) ) ) ).'</link>
    </args>
</link>

<label row="'.$row.'" col="2">
  <args>
    <label>'.project_cdata( $fields['archiveid'] ).'</label>
    		<compact>true</compact>
    </args>
</label>

<link row="'.$row.'" col="3">
  <args>
    <label>'.project_cdata( $fields['name'] ).'</label>
    <bold>true</bold>
    <nowrap>false</nowrap>
    <compact>true</compact>
    <link>'.WuiEventsCall::buildEventsCallString( '', array( array(
                    'view',
                    'showproject',
                    array( 'id' => $id ) ) ) ).'</link>
    </args>
</link>

<innomatictoolbar row="'.$row.'" col="0"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode( array(
        'view' => array(
            'show' => array(
                'label' => $gLocale->getStr( 'showproject.button' ),
                'themeimage' => 'viewmag',
                'themeimagetype' => 'mini',
            	'compact' => 'true',
                'horiz' => 'true',
                'action' => WuiEventsCall::buildEventsCallString( '', array( array(
                    'view',
                    'showproject',
                    array( 'id' => $id ) ) ) )
                ) ) ) ).'</toolbars>
  </args>
</innomatictoolbar>';
			$row++;
        }

        $gXml_def .=
'      </children>
    </table>
    
                      </children>
                    </vertgroup>';

            
            $gXml_def .= '
                </children>
              </tab>

            </children>
          </horizgroup>
                		
                		
        </children>
        </form>

        <horizgroup row="1" col="0"><name>tools</name>
          <children>

        <button><name>apply</name>
          <args>
            <themeimage>buttonok</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
                    array(
                        'view',
                        'default',
                        ''
                        ),
                    array(
                        'action',
                        'editproject',
                        array( 'id' => $eventData['id'] ) )
                ) ) ).'</action>
            <label>'.( $gLocale->getStr( 'editproject.submit' ) ).'</label>
            <formsubmit>project</formsubmit>
          </args>
        </button>

        <button><name>setdone</name>
          <args>
            <themeimage>'.$done_icon.'</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
                    array(
                        'view',
                        'default',
                        ''
                        ),
                    array(
                        'action',
                        'editproject',
                        array(
                            'id' => $eventData['id'],
                            'done' => $done_action
                            ) )
                ) ) ).'</action>
            <label>'.project_cdata( $gLocale->getStr( $done_label ) ).'</label>
            <formsubmit>project</formsubmit>
          </args>
        </button>

          </children>
        </horizgroup>

      </children>
    </table>
  </children>
</vertgroup>

  <innoworkitemacl><name>itemacl</name>
    <args>
      <itemtype>project</itemtype>
      <itemid>'.$eventData['id'].'</itemid>
      <itemownerid>'.$pj_data['ownerid'].'</itemownerid>
      <defaultaction>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
        array( 'view', 'showproject', array( 'id' => $eventData['id'] ) ) ) ) ).'</defaultaction>
    </args>
  </innoworkitemacl>

  </children>
</horizgroup>';
}

function fields_tab_action_builder( $tab )
{
    return WuiEventsCall::buildEventsCallString( '', array( array(
            'view',
            'stats',
            array( 'tab' => $tab )
        ) ) );
}

$gMain_disp->addEvent(
		'timesheet',
		'main_timesheet'
);
function main_timesheet(
		$eventData
)
{
	global $gXml_def, $gLocale;

	$innowork_dossier = new InnoworkProject(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
			$eventData['projectid']
	);

	$timesheet_manager = new \Innowork\Timesheet\Timesheet(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
	$timesheet = $timesheet_manager->getTimesheet(InnoworkProject::ITEM_TYPE, $eventData['projectid']);

	// Users list

	$users_query = \Innowork\Timesheet\Timesheet::getTimesheetUsers();

	$users = array();

	while ( !$users_query->eof )
	{
		$users[$users_query->getFields( 'id' )] = $users_query->getFields( 'lname' ).
		' '.$users_query->getFields( 'fname' );

		$users_query->moveNext();
	}

	$cost_types = \Innowork\Timesheet\Timesheet::getElencoCodiciImponibili();

	$headers[0]['label'] = $gLocale->getStr( 'date.header' );
	$headers[1]['label'] = $gLocale->getStr( 'useritem.header' );
	$headers[2]['label'] = $gLocale->getStr( 'activitydesc.header' );
	$headers[3]['label'] = $gLocale->getStr( 'timespent.header' );
	$headers[4]['label'] = $gLocale->getStr( 'cost.header' );
	$headers[5]['label'] = $gLocale->getStr( 'costtype.header' );
	$headers[7]['label'] = $gLocale->getStr( 'reportperiod.header' );

	$gXml_def =
	'
<page>
  <args>
    <border>false</border>
	<ajaxloader>false</ajaxloader>
  </args>
  <children>
		
<vertgroup>
  <children>
<horizgroup row="'.$row.'" col="6" halign="" valign="top">
  <children>

  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>printer</themeimage>
	  <target>_blank</target>
      <themeimagetype>mini</themeimagetype>
      <label>'.$gLocale->getStr( 'print.button' ).'</label>
      <action>'.project_cdata(
      		WuiEventsCall::buildEventsCallString(
      				'',
      				array(
      						array(
      								'view',
      								'printtimesheet',
      								array(
      										'projectid' => $eventData['projectid']
      								)
      						)
      				)
      		)
      ).'</action>
    </args>
  </button>

  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>clouddown</themeimage>
	  <target>_blank</target>
      <themeimagetype>mini</themeimagetype>
      <label>'.$gLocale->getStr( 'export.button' ).'</label>
      <action>'.project_cdata(
      		WuiEventsCall::buildEventsCallString(
      				'',
      				array(
      						array(
      								'view',
      								'exporttimesheet',
      								array(
      										'projectid' => $eventData['projectid']
      								)
      						)
      				)
      		)
      ).'</action>
    </args>
  </button>

  </children>
</horizgroup>

<form><name>tsrow</name>
      <args>
            <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
            		array(
            				'view',
            				'timesheet',
            				array(
            						'projectid' => $eventData['projectid']
            				)
            		)
            ) ) ).'</action>
      </args>
      <children>
<table><name>timesheet</name>
  <args>
    <headers type="array">'.WuiXml::encode( $headers ).'</headers>
  </args>
  <children>';

	$row = 1;

	$country = new LocaleCountry(
			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry()
	);

	$start_date_array = array(
			'year' => date( 'Y' ),
			'mon' => date( 'm' ),
			'mday' => date( 'd' ),
			'hours' => date( 'H' ),
			'minutes' => '00',
			'seconds' => '00'
	);

	$gXml_def .=
	'       <date row="0" col="0"><name>date</name>
              <args>
                <disp>action</disp>
                <value type="array">'.WuiXml::encode( $start_date_array ).'</value>
              </args>
            </date>
		
    <combobox row="0" col="1"><name>user</name>
      <args>
        <disp>action</disp>
        <elements type="array">'.WuiXml::encode( $users ).'</elements>
        <default>'.InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId().'</default>
      </args>
    </combobox>
    <text row="0" col="2"><name>activitydesc</name>
		<args>
          <disp>action</disp>
	      <cols>30</cols>
          <rows>4</rows>
		</args>
	</text>
    <string row="0" col="3"><name>timespent</name>
		<args>
          <disp>action</disp>
	      <size>6</size>
		</args>
	</string>
    <string row="0" col="4"><name>cost</name>
		<args>
          <disp>action</disp>
	      <size>8</size>
		</args>
	</string>
    <combobox row="0" col="5"><name>costtype</name>
      <args>
        <disp>action</disp>
        <elements type="array">'.WuiXml::encode( $cost_types ).'</elements>
        <default>0</default>
      </args>
    </combobox>

  <button row="0" col="6">
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>mathadd</themeimage>
      <themeimagetype>mini</themeimagetype>
      <formsubmit>tsrow</formsubmit>
      <label>'.$gLocale->getStr( 'add_ts_row.button' ).'</label>
      <action>'.project_cdata(
      		WuiEventsCall::buildEventsCallString(
      				'',
      				array(
      						array(
      								'view',
      								'timesheet',
      								array(
      										'projectid' => $eventData['projectid']
      								)
      						),
      						array(
      								'action',
      								'newtsrow',
      								array(
      										'projectid' => $eventData['projectid'],
      										'rowid' => $message['id']
      								)
      						)
      				)
      		)
      ).'</action>
    </args>
  </button>';
	foreach ( $timesheet as $ts_row )
	{
		if ($ts_row['consolidated'] == 't') {
			$cons_action = 'unconsolidate';
			$cons_label = 'Deconsolida';
			$cons_icon = 'unlock';
		} else {
			$cons_action = 'consolidate';
			$cons_label = 'Consolida';
			$cons_icon = 'lock';
		}

		$gXml_def .=
		'
    <label row="'.$row.'" col="0" halign="left" valign="top">
      <args>
        <label>'.project_cdata(
        		$country->FormatShortArrayDate( $ts_row['activitydate'] )
        ).'</label>
        <compact>true</compact>
      </args>
    </label>
    <label row="'.$row.'" col="1" halign="left" valign="top">
      <args>
        <label>'.project_cdata($users[$ts_row['userid']]).'</label>
        <compact>true</compact>
      </args>
    </label>
    <label row="'.$row.'" col="2" halign="left" valign="top">
      <args>
        <label>'.project_cdata(
        		nl2br( $ts_row['description'] )
        ).'</label>
        		<nowrap>false</nowrap>
        <compact>true</compact>
      </args>
    </label>
    <label row="'.$row.'" col="3" halign="right" valign="top">
      <args>
        <label>'.project_cdata($ts_row['spenttime']).'</label>
        <compact>true</compact>
      </args>
    </label>
    <label row="'.$row.'" col="4" halign="right" valign="top">
      <args>
        <label>'.project_cdata($ts_row['cost']).'</label>
        <compact>true</compact>
        		<!--
        <editable>true</editable>
        		-->
        <id>cost_'.$row.'</id>
      </args>
      <events><click>xajax_editcost('.$row.')</click></events>
    </label>
    <label row="'.$row.'" col="5" halign="left" valign="top">
      <args>
        <label>'.project_cdata($cost_types[$ts_row['costtype']]).'</label>
        <compact>true</compact>
      </args>
    </label>
    <label row="'.$row.'" col="7" halign="left" valign="top">
      <args>
        <label>'.project_cdata($ts_row['reportingperiod']).'</label>
        <compact>true</compact>
      </args>
    </label>


<horizgroup row="'.$row.'" col="6" halign="" valign="top">
  <children>

  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>pencil</themeimage>
      <themeimagetype>mini</themeimagetype>
      <label>Modifica</label>
      <action>'.project_cdata(
      		WuiEventsCall::buildEventsCallString(
      				'',
      				array(
      						array(
      								'view',
      								'timesheetrow',
      								array(
      										'projectid' => $eventData['projectid'],
      										'rowid' => $ts_row['id']
      								)
      						)
      				)
      		)
      ).'</action>
    </args>
  </button>

  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>mathsub</themeimage>
      <themeimagetype>mini</themeimagetype>
      <label>'.$gLocale->getStr( 'remove_ts_row.button' ).'</label>
      <needconfirm>true</needconfirm>
      <confirmmessage>'.$gLocale->getStr( 'remove_ts_row.confirm' ).'</confirmmessage>
      <action>'.project_cdata(
      		WuiEventsCall::buildEventsCallString(
      				'',
      				array(
      						array(
      								'view',
      								'timesheet',
      								array(
      										'projectid' => $eventData['projectid']
      								)
      						),
      						array(
      								'action',
      								'removetsrow',
      								array(
      										'projectid' => $eventData['projectid'],
      										'rowid' => $ts_row['id']
      								)
      						)
      				)
      		)
      ).'</action>
    </args>
  </button>

  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>'.$cons_icon.'</themeimage>
      <themeimagetype>mini</themeimagetype>
      <label>'.$cons_label.'</label>
      <action>'.project_cdata(
      		WuiEventsCall::buildEventsCallString(
      				'',
      				array(
      						array(
      								'view',
      								'timesheet',
      								array(
      										'projectid' => $eventData['projectid']
      								)
      						),
      						array(
      								'action',
      								$cons_action,
      								array(
      										'projectid' => $eventData['projectid'],
      										'rowid' => $ts_row['id']
      								)
      						)
      				)
      		)
      ).'</action>
    </args>
  </button>

  </children>
</horizgroup>';
		$row++;
	}

	$gXml_def .=
	'  </children>
</table>
			</children>
		</form>

      </children>
	</vertgroup>
  </children>
</page>';

	$wui = new WuiXml( '', array( 'definition' => $gXml_def ) );
	$wui->Build(new WuiDispatcher('wui'));
	echo $wui->render();

	InnomaticContainer::instance('innomaticcontainer')->halt();
}

$gMain_disp->addEvent(
		'timesheetrow',
		'main_timesheetrow'
);
function main_timesheetrow(
		$eventData
)
{
	global $gXml_def, $gLocale;

	$innowork_dossier = new InnoworkProject(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
			$eventData['projectid']
	);

	$timesheet_manager = new \Innowork\Timesheet\Timesheet(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
	$timesheet = $timesheet_manager->getTimesheet(InnoworkProject::ITEM_TYPE, $eventData['projectid']);


	// Users list

	$users_query = \Innowork\Timesheet\Timesheet::getTimesheetUsers();

	$users = array();

	while ( !$users_query->eof )
	{
		$users[$users_query->getFields( 'id' )] = $users_query->getFields( 'lname' ).
		' '.$users_query->getFields( 'fname' );

		$users_query->moveNext();
	}

	$cost_types = \Innowork\Timesheet\Timesheet::getElencoCodiciImponibili();

	$headers[0]['label'] = $gLocale->getStr( 'date.header' );
	$headers[1]['label'] = $gLocale->getStr( 'useritem.header' );
	$headers[2]['label'] = $gLocale->getStr( 'activitydesc.header' );
	$headers[3]['label'] = $gLocale->getStr( 'timespent.header' );
	$headers[4]['label'] = $gLocale->getStr( 'cost.header' );
	$headers[5]['label'] = $gLocale->getStr( 'costtype.header' );
	$headers[7]['label'] = $gLocale->getStr( 'reportperiod.header' );

	$gXml_def =
	'
<page>
  <args>
    <border>false</border>
	<ajaxloader>false</ajaxloader>
  </args>
  <children>

<vertgroup>
  <children>

<form><name>tsrow</name>
      <args>
            <action>'.project_cdata( WuiEventsCall::buildEventsCallString( '', array(
            		array(
            				'view',
            				'timesheet',
            				array(
            						'projectid' => $eventData['projectid']
            				)
            		),
            		array(
            				'action',
            				'changetsrow',
            				array(
            						'projectid' => $eventData['projectid'],
            						'rowid' => $eventData['rowid']
            				)
            		)
            ) ) ).'</action>
      </args>
      <children>
<table><name>timesheet</name>
  <args>
    <headers type="array">'.WuiXml::encode( $headers ).'</headers>
  </args>
  <children>';

	$country = new LocaleCountry(
			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry()
	);

	$start_date_array = array(
			'year' => date( 'Y' ),
			'mon' => date( 'm' ),
			'mday' => date( 'd' ),
			'hours' => date( 'H' ),
			'minutes' => '00',
			'seconds' => '00'
	);
	foreach ( $timesheet as $ts_row )
	{
		if ($ts_row['id'] != $eventData['rowid']) {
			continue;
		}

		$gXml_def .=
		'       <date row="0" col="0"><name>date</name>
              <args>
                <disp>action</disp>
                <value type="array">'.WuiXml::encode( $ts_row['activitydate'] ).'</value>
              </args>
            </date>

    <combobox row="0" col="1"><name>user</name>
      <args>
        <disp>action</disp>
        <elements type="array">'.WuiXml::encode( $users ).'</elements>
        <default>'.$ts_row['userid'].'</default>
      </args>
    </combobox>
    <text row="0" col="2"><name>activitydesc</name>
		<args>
          <disp>action</disp>
	      <cols>30</cols>
          <rows>4</rows>
          <value>'.project_cdata(
          		nl2br( $ts_row['description'] )
          ).'</value>
		</args>
	</text>
    <string row="0" col="3"><name>timespent</name>
		<args>
          <disp>action</disp>
	      <size>6</size>
          <value>'.project_cdata($ts_row['spenttime']).'</value>
		</args>
	</string>
    <string row="0" col="4"><name>cost</name>
		<args>
          <disp>action</disp>
	      <size>8</size>
          <value>'.project_cdata($ts_row['cost']).'</value>
		</args>
	</string>
    <combobox row="0" col="5"><name>costtype</name>
      <args>
        <disp>action</disp>
        <elements type="array">'.WuiXml::encode( $cost_types ).'</elements>
        <default>'.$ts_row['costtype'].'</default>
      </args>
    </combobox>

  <horizgroup row="0" col="6"><children>
  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>buttonok</themeimage>
      <themeimagetype>mini</themeimagetype>
      <formsubmit>tsrow</formsubmit>
      <label>Conferma</label>
      <action>'.project_cdata(
      		WuiEventsCall::buildEventsCallString( '', array(
      				array(
      						'view',
      						'timesheet',
      						array(
      								'projectid' => $eventData['projectid']
      						)
      				),
      				array(
      						'action',
      						'changetsrow',
      						array(
      								'projectid' => $eventData['projectid'],
      								'rowid' => $eventData['rowid']
      						)
      				)
      		) )
      ).'</action>
    </args>
  </button>
  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>arrowleft</themeimage>
      <themeimagetype>mini</themeimagetype>
      <formsubmit>tsrow</formsubmit>
      <label>Torna al timesheet</label>
      <action>'.project_cdata(
      		WuiEventsCall::buildEventsCallString( '', array(
      				array(
      						'view',
      						'timesheet',
      						array(
      								'projectid' => $eventData['projectid']
      						)
      				)
      		) )
      ).'</action>
    </args>
  </button>
  </children></horizgroup>

    <label row="0" col="7" halign="left" valign="top">
      <args>
        <label>'.project_cdata($ts_row['reportingperiod']).'</label>
        <compact>true</compact>
      </args>
    </label>';
	}

	$gXml_def .=
	'  </children>
</table>
			</children>
		</form>

      </children>
	</vertgroup>
  </children>
</page>';

	$wui = new WuiXml( '', array( 'definition' => $gXml_def ) );
	$wui->Build(new WuiDispatcher('wui'));
	echo $wui->render();

	InnomaticContainer::instance('innomaticcontainer')->halt();
}

$gMain_disp->addEvent(
		'printtimesheet',
		'main_printtimesheet'
);
function main_printtimesheet(
		$eventData
)
{
	global $gXml_def, $gLocale;

	$innowork_dossier = new InnoworkProject(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
			$eventData['projectid']
	);

	$pj_data = $innowork_dossier->getItem( InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId() );

	$innowork_customer = new InnoworkCompany(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
			$pj_data['customerid']
	);

	$cust_data = $innowork_customer->getItem();

	$timesheet_manager = new \Innowork\Timesheet\Timesheet(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
	$timesheet = $timesheet_manager->getTimesheet(InnoworkProject::ITEM_TYPE, $eventData['projectid']);

	// Users list

	$users_query = \Innowork\Timesheet\Timesheet::getTimesheetUsers();

	$users = array();

	while ( !$users_query->eof )
	{
		$users[$users_query->getFields( 'id' )] = $users_query->getFields( 'lname' ).
		' '.$users_query->getFields( 'fname' );

		$users_query->moveNext();
	}

	$cost_types = \Innowork\Timesheet\Timesheet::getElencoCodiciImponibili();

	$headers[0]['label'] = $gLocale->getStr( 'date.header' );
	$headers[1]['label'] = $gLocale->getStr( 'useritem.header' );
	$headers[2]['label'] = $gLocale->getStr( 'activitydesc.header' );
	$headers[3]['label'] = $gLocale->getStr( 'timespent.header' );
	$headers[4]['label'] = $gLocale->getStr( 'cost.header' );
	$headers[5]['label'] = $gLocale->getStr( 'costtype.header' );

	$row = 1;

	$country = new LocaleCountry(
			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry()
	);

	echo "<html><head><title>Timesheet Report</title><meta charset=\"UTF-8\"></head><body onload=\"window.print()\">\n";

	echo '<table style="width: 100%"><tr><td>Project n.</td><td>Customer</td><td>Title</td></tr>
			<tr><td><strong>'.$eventData['projectid'].'</strong></td><td><strong>'.$cust_data['companyname'].'</strong></td><td><strong>'.$pj_data['name'].'</strong></td></tr></table>';

	echo "<br><table border=\"1\" cellspacing=\"0\" cellpadding=\"4\" style=\"border: solid 1px; width: 100%;\">\n";
	echo "<tr>\n";
	echo "<th valign=\"top\">".$headers[0]['label']."</th>\n";
	echo "<th valign=\"top\">".$headers[1]['label']."</th>\n";
	echo "<th valign=\"top\">".$headers[2]['label']."</th>\n";
	echo "<th valign=\"top\">".$headers[3]['label']."</th>\n";
	echo "</tr>\n";

	foreach ( $timesheet as $ts_row )
	{
		echo "<tr>\n";
		echo "<td valign=\"top\">".$country->FormatShortArrayDate( $ts_row['activitydate'] )."</td>\n";
		echo "<td valign=\"top\">".$users[$ts_row['userid']]."</td>\n";
		echo "<td valign=\"top\">".nl2br( $ts_row['description'] )."</td>\n";
		echo "<td align=\"right\" valign=\"top\">".$ts_row['spenttime']."</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "</body></html>\n";

	InnomaticContainer::instance('innomaticcontainer')->halt();
}

$gMain_disp->addEvent(
		'exporttimesheet',
		'main_exporttimesheet'
);
function main_exporttimesheet(
		$eventData
)
{
	global $gXml_def, $gLocale;

	$innowork_dossier = new InnoworkProject(
			InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
			$eventData['projectid']
	);

	$timesheet_manager = new \Innowork\Timesheet\Timesheet(
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
	$timesheet = $timesheet_manager->getTimesheet(InnoworkProject::ITEM_TYPE, $eventData['projectid']);

	// Users list

	$users_query = \Innowork\Timesheet\Timesheet::getTimesheetUsers();
	$users = array();

	while (!$users_query->eof) {
		$users[$users_query->getFields( 'id' )] = $users_query->getFields( 'lname' ).
		' '.$users_query->getFields( 'fname' );
		$users_query->moveNext();
	}

	$cost_types = \Innowork\Timesheet\Timesheet::getElencoCodiciImponibili();

	$headers[0]['label'] = $gLocale->getStr( 'date.header' );
	$headers[1]['label'] = $gLocale->getStr( 'useritem.header' );
	$headers[2]['label'] = $gLocale->getStr( 'activitydesc.header' );
	$headers[3]['label'] = $gLocale->getStr( 'timespent.header' );
	$headers[4]['label'] = $gLocale->getStr( 'cost.header' );
	$headers[5]['label'] = $gLocale->getStr( 'costtype.header' );

	$row = 1;

	$country = new LocaleCountry(
			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry()
	);

	$filename="timesheet.xls";
	header ("Content-Type: application/vnd.ms-excel");
	header ("Content-Disposition: inline; filename=$filename");

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html lang=it><head>
	<title>Timesheet</title></head>
	<body>
	<table border="1">';
	echo "<tr>\n";
	echo "<th valign=\"top\">".$headers[0]['label']."</th>\n";
	echo "<th valign=\"top\">".$headers[1]['label']."</th>\n";
	echo "<th valign=\"top\">".$headers[2]['label']."</th>\n";
	echo "<th valign=\"top\">".$headers[3]['label']."</th>\n";
	echo "<th valign=\"top\">".$headers[4]['label']."</th>\n";
	echo "<th valign=\"top\">".$headers[5]['label']."</th>\n";
	echo "</tr>\n";
	foreach ( $timesheet as $ts_row )
	{
		echo "<tr>\n";
		echo "<td valign=\"top\">".$country->FormatShortArrayDate( $ts_row['activitydate'] )."</td>\n";
		echo "<td valign=\"top\">".$users[$ts_row['userid']]."</td>\n";
		echo "<td valign=\"top\">".nl2br( $ts_row['description'] )."</td>\n";
		echo "<td valign=\"top\">".$ts_row['spenttime']."</td>\n";
		echo "<td valign=\"top\">".$ts_row['cost']."</td>\n";
		echo "<td valign=\"top\">".$cost_types[$ts_row['costtype']]."</td>\n";
		echo "</tr>\n";
	}
	echo "</table>
	</body></html>\n";

	InnomaticContainer::instance('innomaticcontainer')->halt();
}

$gMain_disp->addEvent(
    'stats',
    'main_stats'
    );
function main_stats(
    $eventData
    )
{
    global $gXml_def, $gLocale;

    $projects = new InnoworkProject(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
    $projects_search = $projects->Search(
        '',
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $tabs[0]['label'] = $gLocale->getStr( 'status.tab' );
    $tabs[1]['label'] = $gLocale->getStr( 'type.tab' );
    $tabs[2]['label'] = $gLocale->getStr( 'priority.tab' );
    
    foreach ( $projects_search as $id => $project_data )
    {
        $_status_data[$project_data['status']]++;
        $_type_data[$project_data['type']]++;
        $_priority_data[$project_data['priority']]++;
    }

    $statuses_list = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_STATUS );
    $status_data[1][] = 0;

    foreach ( $_status_data as $status => $number )
    {
        $status_data[1][] = $number;
        $status_legend[] = $statuses_list[$status];
    }

    $types_list = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_TYPE );
    $type_data[1][] = 0;

    foreach ( $_type_data as $type => $number )
    {
        $type_data[1][] = $number;
        $type_legend[] = $types_list[$type];
    }

    $priorities_list = InnoworkProjectField::getFields( INNOWORKPROJECTS_FIELDTYPE_PRIORITY );
    $priority_data[1][] = 0;

    foreach ( $_priority_data as $priority => $number )
    {
        $priority_data[1][] = $number;
        $priority_legend[] = $priorities_list[$priority];
    }

    $gXml_def =
'<vertgroup>
  <children>

        <label row="0" col="1">
          <args>
            <label>'.( $gLocale->getStr( 'statistics.label' ) ).'</label>
            <bold>true</bold>
          </args>
        </label>

    <tab><name>fieldsvalues</name>
      <args>
        <tabs type="array">'.WuiXml::encode( $tabs ).'</tabs>
        <tabactionfunction>fields_tab_action_builder</tabactionfunction>
        <activetab>'.( isset($eventData['tab'] ) ? $eventData['tab'] : '' ).'</activetab>
      </args>
      <children>

        <vertgroup>
          <args>
            <align>center</align>
          </args>
          <children>

        <label row="0" col="0">
          <args>
            <label>'.( $gLocale->getStr( 'status_stats.label' ) ).'</label>
            <bold>true</bold>
          </args>
        </label>

        <phplot row="1" col="0">
          <args>
            <data type="array">'.WuiXml::encode( $status_data ).'</data>
            <width>500</width>
            <height>300</height>
            <linewidth>0</linewidth>
            <plottype>pie</plottype>
            <legend type="array">'.WuiXml::encode( $status_legend ).'</legend>
          </args>
        </phplot>

          </children>
        </vertgroup>

        <vertgroup>
          <args>
            <align>center</align>
          </args>
          <children>

        <label row="0" col="1">
          <args>
            <label>'.( $gLocale->getStr( 'type_stats.label' ) ).'</label>
            <bold>true</bold>
          </args>
        </label>

        <phplot row="1" col="1">
          <args>
            <data type="array">'.WuiXml::encode( $type_data ).'</data>
            <width>500</width>
            <height>300</height>
            <linewidth>0</linewidth>
            <plottype>pie</plottype>
            <legend type="array">'.WuiXml::encode( $type_legend ).'</legend>
          </args>
        </phplot>

          </children>
        </vertgroup>

        <vertgroup>
          <args>
            <align>center</align>
          </args>
          <children>

        <label row="0" col="2">
          <args>
            <label>'.( $gLocale->getStr( 'priority_stats.label' ) ).'</label>
            <bold>true</bold>
          </args>
        </label>

        <phplot row="1" col="2">
          <args>
            <data type="array">'.WuiXml::encode( $priority_data ).'</data>
            <width>500</width>
            <height>300</height>
            <linewidth>0</linewidth>
            <plottype>pie</plottype>
            <legend type="array">'.WuiXml::encode( $priority_legend ).'</legend>
          </args>
        </phplot>

          </children>
        </vertgroup>

      </children>
    </tab>

  </children>
</vertgroup>';
}

$gMain_disp->Dispatch();

function project_extras_tab_builder($tab) {
	global $gMain_disp;
	$ev_data = $gMain_disp->getEventData();

	$args = array('id' => $ev_data['id'], 'extrastab' => $tab);

	if (isset($GLOBALS['create_mode'])) {
		$args['create_mode'] = 'true';
	}
	return WuiEventsCall::buildEventsCallString('', array(array('view', 'showproject', $args)));
}

// ----- Rendering -----
//
$gWui->addChild( new WuiInnomaticPage( 'page', array(
    'pagetitle' => $gPage_title,
    'icon' => 'folder',
    'toolbars' => array(
        new WuiInnomaticToolbar(
            'view',
            array(
                'toolbars' => $gToolbars, 'toolbar' => 'true'
                ) ),
        new WuiInnomaticToolBar(
            'core',
            array(
                'toolbars' => $gCore_toolbars, 'toolbar' => 'true'
                ) ),
            ),
    'maincontent' => new WuiXml(
        'page', array(
            'definition' => $gXml_def
            ) ),
    'status' => $gPage_status
    ) ) );

$gWui->render();

?>
