<?php

namespace Shared\Dashboard;

use \Innomatic\Core\InnomaticContainer;
use \Shared\Wui;
use \Innomatic\Wui\Dispatch;

class InnoworkMyTasksDashboardWidget extends \Innomatic\Desktop\Dashboard\DashboardWidget
{
    public function getWidgetXml()
    {
        $locale_catalog = new \Innomatic\Locale\LocaleCatalog(
            'innowork-projects::tasks_dashboard',
            InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );
    	
    	$locale_country = new \Innomatic\Locale\LocaleCountry(
			InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
        );

    	require_once('innowork/projects/InnoworkTask.php');
    	
		$tasks = new InnoworkTask(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
		);
		
		$tasks->mSearchOrderBy = 'id DESC';
		
		$search_result = $tasks->search(
			array('done' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse, 'assignedto' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
		);
		
        $xml =
        '<vertgroup>
           <children>';
        
        $search_result_count = count($search_result);
        
        switch ($search_result_count) {
        	case 0:
        		$tasks_number_label = $locale_catalog->getStr('no_tasks.label');
        		break;
        		
        	case 1:
        		$tasks_number_label = sprintf($locale_catalog->getStr('task_number.label'), count($search_result));
        		break;
        		
        	default:
        		$tasks_number_label = sprintf($locale_catalog->getStr('tasks_number.label'), count($search_result));
        }
        
        $xml .= '<label>
               <args>
        		 <label>'.WuiXml::cdata($tasks_number_label).'</label>
        	   </args>
        	 </label>';
        
        if ($search_result_count > 0) {
        	$xml .= '<label>
               <args>
        		 <label>'.WuiXml::cdata($locale_catalog->getStr('last_opened_tasks.label')).'</label>
        	   </args>
        	 </label>
        	
        	<grid><children>';
        	
        	$row = 0;
        	foreach ($search_result as $task) {
        		$xml .= '<link row="'.$row.'" col="0" halign="left" valign="top">
               <args>
        		 <label>'.WuiXml::cdata($task['id']).'</label>
        <compact>true</compact>
        <nowrap>false</nowrap>
        <link>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworktasks', array(array('view', 'showtask', array('id' => $task['id']))))).'</link>
        	   </args>
        	 </link>
        	<link row="'.$row.'" col="1" halign="left" valign="top">
               <args>
        		 <label>'.WuiXml::cdata($task['title']).'</label>
        <compact>true</compact>
        <nowrap>false</nowrap>
        <link>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworktasks', array(array('view', 'showtask', array('id' => $task['id']))))).'</link>
        	   </args>
        	 </link>';
        		if (++$row == 5) {
        			break;
        		}
        	}
        	
        	$xml .= '</children></grid>';
        }
        
        $xml .= '<horizbar/>';

        $xml .= '<horizgroup><args><width>0%</width></args><children>';

        if (count($search_result) > 0) {
        	$xml .= '  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>zoom</themeimage>
      <label>'.$locale_catalog->getStr('show_all_my_tasks.button').'</label>
      <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworktasks', array(array('view', 'default', array('filter' => 'true', 'filter_assignedto' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()))))).'</action>
    </args>
  </button>';
        }
        
        $xml .= '
  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>mathadd</themeimage>
      <label>'.$locale_catalog->getStr('new_task.button').'</label>
      <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworktasks', array(array('view', 'newtask', array())))).'</action>
    </args>
  </button>';
    	      		
  $xml .= '</children></horizgroup>
    	      			
           </children>
         </vertgroup>';

        return $xml;
    }

    public function getWidth()
    {
        return 1;
    }

    public function getHeight()
    {
        return 60;
    }
}
