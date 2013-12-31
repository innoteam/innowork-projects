<?php

require_once('innowork/core/InnoworkItem.php');

require_once('innomatic/dataaccess/DataAccess.php');
require_once('innomatic/logging/Logger.php');

class InnoworkProject extends InnoworkItem {
    var $mTable = 'innowork_projects';
    var $mNewDispatcher = 'view';
    var $mNewEvent = 'newproject';
    const ITEM_TYPE = 'project';

    function InnoworkProject(
        $rrootDb,
        $rdomainDA,
        $projectId = 0
        )
    {
        parent::__construct(
            $rrootDb,
            $rdomainDA,
            InnoworkProject::ITEM_TYPE,
            $projectId
            );


        $this->mKeys['name'] = 'text';
        $this->mKeys['description'] = 'text';
        $this->mKeys['customerid'] = 'table:innowork_directory_companies:companyname:integer';
        $this->mKeys['estimatedenddate'] = 'timestamp';
        $this->mKeys['estimatedcost'] = 'text';
        $this->mKeys['estimatedrevenue'] = 'text';
        $this->mKeys['responsible'] = 'integer';
        $this->mKeys['done'] = 'boolean';
        $this->mKeys['status'] = 'table:innowork_projects_fields_values:fieldvalue:integer';
        $this->mKeys['priority'] = 'table:innowork_projects_fields_values:fieldvalue:integer';
        $this->mKeys['type'] = 'table:innowork_projects_fields_values:fieldvalue:integer';
        $this->mKeys['estimatedstartdate'] = 'timestamp';
        $this->mKeys['realstartdate'] = 'timestamp';
        $this->mKeys['realenddate'] = 'timestamp';
        $this->mKeys['estimatedtime'] = 'integer';
        $this->mKeys['realtime'] = 'integer';
        $this->mKeys['realcost'] = 'text';
        $this->mKeys['realrevenue'] = 'text';

        $this->mSearchResultKeys[] = 'name';
        $this->mSearchResultKeys[] = 'description';
        $this->mSearchResultKeys[] = 'customerid';
        $this->mSearchResultKeys[] = 'responsible';
        $this->mSearchResultKeys[] = 'type';
        $this->mSearchResultKeys[] = 'status';
        $this->mSearchResultKeys[] = 'priority';
        $this->mSearchResultKeys[] = 'done';
        $this->mSearchResultKeys[] = 'estimatedenddate';
        $this->mSearchResultKeys[] = 'estimatedcost';
        $this->mSearchResultKeys[] = 'estimatedrevenue';
        $this->mSearchResultKeys[] = 'estimatedstartdate';
        $this->mSearchResultKeys[] = 'realstartdate';
        $this->mSearchResultKeys[] = 'realenddate';
        $this->mSearchResultKeys[] = 'estimatedtime';
        $this->mSearchResultKeys[] = 'realtime';
        $this->mSearchResultKeys[] = 'realcost';
        $this->mSearchResultKeys[] = 'realrevenue';

        $this->mViewableSearchResultKeys[] = 'name';
        $this->mViewableSearchResultKeys[] = 'description';
        $this->mViewableSearchResultKeys[] = 'customerid';
        $this->mViewableSearchResultKeys[] = 'estimatedenddate';
        $this->mViewableSearchResultKeys[] = 'estimatedcost';
        $this->mViewableSearchResultKeys[] = 'estimatedrevenue';

        $this->mSearchOrderBy = 'name';

        $this->mShowDispatcher = 'view';
        $this->mShowEvent = 'showproject';

        $this->mRelatedItemsFields[] = 'projectid';
    }

    function doCreate(
        $params,
        $userId
        )
    {
        $result = false;

            if ( $params['done'] == 'true' ) $params['done'] = $this->mrDomainDA->fmttrue;
            else $params['done'] = $this->mrDomainDA->fmtfalse;

            if (
                !isset($params['customerid'] )
                or !strlen( $params['customerid'] )
                ) $params['customerid'] = '0';

            if (
                !isset($params['responsible'] )
                or !strlen( $params['responsible'] )
                ) $params['responsible'] = '0';

            if (
                !isset($params['status'] )
                or !strlen( $params['status'] )
                ) $params['status'] = '0';

            if (
                !isset($params['priority'] )
                or !strlen( $params['priority'] )
                ) $params['priority'] = '0';

            if (
                !isset($params['type'] )
                or !strlen( $params['type'] )
                ) $params['type'] = '0';

            if (
                !isset($params['estimatedtime'] )
                or !strlen( $params['estimatedtime'] )
                ) $params['estimatedtime'] = '0';

            if (
                !isset($params['realtime'] )
                or !strlen( $params['realtime'] )
                ) $params['realtime'] = '0';

        if ( count( $params ) )
        {
            

            $item_id = $this->mrDomainDA->getNextSequenceValue( $this->mTable.'_id_seq' );

            $key_pre = $value_pre = $keys = $values = '';

            $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );

            while ( list( $key, $val ) = each( $params ) )
            {
                $key_pre = ',';
                $value_pre = ',';

                switch ( $key )
                {
                case 'name':
                case 'description':
                case 'estimatedcost':
                case 'realcost':
                case 'estimatedrevenue':
                case 'realrevenue':
                case 'done':
                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$this->mrDomainDA->formatText( $val );
                    break;

                case 'estimatedstartdate':
                case 'realstartdate':
                case 'estimatedenddate':
                case 'realenddate':
                    $date_array = $country->getDateArrayFromShortDateStamp( $val );
                    $val = $this->mrDomainDA->getTimestampFromDateArray( $date_array );

                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$this->mrDomainDA->formatText( $val );
                    break;

                case 'customerid':
                case 'responsible':
                case 'status':
                case 'priority':
                case 'type':
                case 'estimatedtime':
                case 'realtime':
                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$val;
                    break;

                default:
                    break;
                }
            }

            if ( strlen( $values ) )
            {
                if ( $this->mrDomainDA->Execute( 'INSERT INTO '.$this->mTable.' '.
                                               '(id,ownerid'.$keys.') '.
                                               'VALUES ('.$item_id.','.
                                               $userId.
                                               $values.')' ) ) $result = $item_id;
            }
        }

        return $result;
    }

    function doEdit(
        $params,
        $userId
        )
    {
        $result = FALSE;

        if ( $this->mItemId )
        {
            if ( count( $params ) )
            {
                $start = 1;
                $update_str = '';

                
                $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );

                if ( isset($params['done'] ) )
                {
                    if ( $params['done'] == 'true' ) $params['done'] = $this->mrDomainDA->fmttrue;
                    else $params['done'] = $this->mrDomainDA->fmtfalse;
                }

                while ( list( $field, $value ) = each( $params ) )
                {
                    if ( $field != 'id' )
                    {
                        if ( !$start ) $update_str .= ',';

                        switch ( $field )
                        {
                        case 'name':
                        case 'description':
                        case 'estimatedcost':
                        case 'realcost':
                        case 'estimatedrevenue':
                        case 'realrevenue':
                        case 'done':
                            $update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
                            break;

                        case 'estimatedstartdate':
                        case 'realstartdate':
                        case 'estimatedenddate':
                        case 'realenddate':
                            $date_array = $country->getDateArrayFromShortDateStamp( $value );
                            $value = $this->mrDomainDA->getTimestampFromDateArray( $date_array );

                            $update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
                            break;

                        case 'customerid':
                        case 'responsible':
                        case 'status':
                        case 'priority':
                        case 'type':
                        case 'estimatedtime':
                        case 'realtime':
                            $update_str .= $field.'='.$value;
                            break;

                        default:
                            break;
                        }

                        $start = 0;
                    }
                }

                $query = &$this->mrDomainDA->Execute(
                    'UPDATE '.$this->mTable.' '.
                    'SET '.$update_str.' '.
                    'WHERE id='.$this->mItemId );

                if ( $query ) $result = TRUE;
            }
        }

        return $result;
    }

    function doRemove( $userId )
    {
        $result = FALSE;

        $result = $this->mrDomainDA->Execute( 'DELETE FROM '.$this->mTable.' '.
                                           'WHERE id='.$this->mItemId );

        return $result;
    }

    function doGetItem( $userId )
    {
        $result = FALSE;

        $item_query = &$this->mrDomainDA->Execute( 'SELECT * '.
                                                'FROM '.$this->mTable.' '.
                                                'WHERE id='.$this->mItemId );

        if ( is_object( $item_query ) and $item_query->getNumberRows() )
        {
            $result = $item_query->getFields();
        }

        return $result;
    }

    function doGetSummary()
    {
        $result = false;

        

        $search_result = $this->Search(
            array(
                'responsible' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId(),
                'done' => $this->mrDomainDA->fmtfalse
                ),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
            );

        if ( is_array( $search_result ) )
        {
            $result = '<grid><name>projecthgroup</name>
  <children>';

            $row = 0;

            while ( list( $id, $fields ) = each( $search_result ) )
            {
                //if ( $fields['done'] == $this->mrDomainDA->fmtfalse )
                //{
                    if ( strlen( $fields['name'] ) > 25 ) $name = substr( $fields['name'], 0, 22 ).'...';
                    else $name = $fields['name'];

                    $result .=
'    <label row="'.$row.'" col="0"><name>projectlabel</name>
      <args>
        <label>- </label>
        <compact>true</compact>
      </args>
    </label>
    <link row="'.$row.'" col="1"><name>projectlink</name>
      <args>
        <label type="encoded">'.urlencode( $name ).'</label>
        <title type="encoded">'.urlencode( $fields['name'] ).'</title>
        <link type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( 'innoworkprojects', array( array(
            'view',
            'showproject',
            array( 'id' => $id )
        ) ) ) ).'</link>
        <compact>true</compact>
      </args>
    </link>';
                    $row++;
                //}
            }

            $result .=
'  </children>
</grid>';
        }

        return $result;
    }
    
    public function getTimesheet() {
    	$result = array();
    	 
    	if ( $this->mItemId )
    	{
    		$timesheet_query = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->Execute(
    				'SELECT * '.
    				'FROM innowork_projects_timesheet '.
    				'WHERE projectid='.$this->mItemId.' '.
    				'ORDER BY activitydate DESC'
    		);
    		 
    		while (!$timesheet_query->eof) {
    			$result[] = array(
    					'id' => $timesheet_query->getFields( 'id' ),
    					'userid' => $timesheet_query->getFields( 'userid' ),
    					'description' => $timesheet_query->getFields( 'description' ),
    					'activitydate' => InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp(
    							$timesheet_query->getFields( 'activitydate' )
    					),
    					'spenttime' => $timesheet_query->getFields( 'spenttime' ),
    					'cost' => $timesheet_query->getFields( 'cost' ),
    					'costtype' => $timesheet_query->getFields( 'costtype' ),
    					'reportingperiod' => $timesheet_query->getFields( 'reportingperiod' ),
    					'consolidated' => $timesheet_query->getFields( 'consolidated' )
    			);
    			 
    			$timesheet_query->moveNext();
    		}
    	}
    	 
    	return $result;
    }
    
    public function addTimesheetRow(
    		$userId,
    		$activityDate,
    		$description,
    		$spentTime,
    		$cost,
    		$costType,
    		$reportingPeriod
    ) {    	 
    	if (!$this->mItemId) {
    		return false;
    	}
    	
    	if (!strlen($costType)) $costType = 0;
    	if (!strlen($userId)) $userId = InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId();
    
    	$timestamp = $this->mrDomainDA->getTimestampFromDateArray( $activityDate );
    	$domainDa = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess();
    
    	$result = $this->mrDomainDA->execute(
    			'INSERT INTO innowork_projects_timesheet VALUES('.
    			$domainDa->getNextSequenceValue( 'innowork_projects_timesheet_id_seq' ).','.
    			$this->mItemId.','.
    			$userId.','.
    			$domainDa->formatText( $timestamp ).','.
    			$domainDa->formatText( $description ).','.
    			$domainDa->formatText( $spentTime ).','.
    			$domainDa->formatText( $cost ).','.
    			$costType.','.
    			$domainDa->formatText( $reportingPeriod ).','.
    			$domainDa->formatText( $domainDa->fmtfalse ).
    			')'
    	);
    
    	if ($result) {
    		require_once('innowork/core/InnoworkItemLog.php');
    		$log = new InnoworkItemLog(
    				$this->mItemType,
    				$this->mItemId
    		);
    			 
    		$log->logChange( InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserName() );
    	}
    	 
    	return $result;
    }
    
    public function changeTimesheetRow(
    		$rowId,
    		$userId,
    		$activityDate,
    		$description,
    		$spentTime,
    		$cost,
    		$costType
    ) {
    	$result = false;
    
    	if ($this->mItemId)
    	{
    		if (!strlen($costType)) $costType = 0;
    		if (!strlen($userId)) $userId = InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId();
    
    		$timestamp = $this->mrDomainDA->getTimestampFromDateArray( $activityDate );
    		$domainDa = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess();
    
    		$result = $this->mrDomainDA->execute(
    				'UPDATE innowork_projects_timesheet SET '.
    				'userid = '.$userId.', '.
    				'activitydate = '.$domainDa->formatText( $timestamp ).', '.
    				'description = '.$domainDa->formatText( $description ).', '.
    				'spenttime = '.$domainDa->formatText( $spentTime ).', '.
    				'cost = '.$domainDa->formatText( $cost ).', '.
    				'costtype = '.$costType.' '.
    				'WHERE id='.$rowId.' AND projectid='.$this->mItemId
    		);
    
    		if ($result) {
    			require_once('innowork/core/InnoworkItemLog.php');
    			$log = new InnoworkItemLog(
    					$this->mItemType,
    					$this->mItemId
    			);
    
    			$log->LogChange( InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserName() );
    		}
    	}
    
    	return $result;
    }
    
    public function consolidateTimesheetRow($rowId) {
    	$result = false;
    	$rowId = (int)$rowId;
    
    	if ( $rowId )
    	{
    		$result = $this->mrDomainDA->Execute(
    				'UPDATE innowork_projects_timesheet '.
    				'SET consolidated = '.$this->mrDomainDA->formatText( $this->mrDomainDA->fmttrue ).' '.
    				'WHERE id='.$rowId
    		);
    	}
    
    	return $result;
    }
    
    public function unconsolidateTimesheetRow($rowId) {
    	$result = false;
    	$rowId = (int)$rowId;
    
    	if ($rowId) {
    		$result = $this->mrDomainDA->execute(
    				'UPDATE innowork_projects_timesheet '.
    				'SET consolidated = '.$this->mrDomainDA->formatText( $this->mrDomainDA->fmtfalse ).' '.
    				'WHERE id='.$rowId
    		);
    	}
    
    	return $result;
    }
    
    public function deleteTimesheetRow($rowId) {
    	$result = false;
    	$rowId = (int)$rowId;
    	 
    	if ($rowId) {
    		$result = $this->mrDomainDA->execute(
    				'DELETE FROM innowork_projects_timesheet '.
    				'WHERE id='.$rowId
    		);
    		 
    		if ( $result )
    		{
    			require_once('innowork/core/InnoworkItemLog.php');
    			$log = new InnoworkItemLog(
    					$this->mItemType,
    					$this->mItemId
    			);
    			 
    			$log->logChange(InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserName());
    		}
    	}
    	 
    	return $result;
    }
    
    public static function getTimesheetUsers() {
    	return InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->Execute(
    			'SELECT domain_users.id AS id,fname,lname,username '.
    			'FROM domain_users '.
    			'WHERE username<>'.InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->formatText(User::getAdminUsername(InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId())).' '.
    			'ORDER BY lname,fname' );
    }
    
    public static function getElencoCodiciImponibili() {
    	return array(
    			0 => '',
    			1 => 'Imponibile',
    			2 => 'Non imponibile ex art. 7',
    			3 => 'Non imponibile ex art. 15'
    	);
    }
}

?>
