<?php

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Shared\Wui;

require_once('innowork/projects/InnoworkTask.php');
require_once('innowork/projects/InnoworkTaskField.php');
require_once('innowork/projects/InnoworkProject.php');

class InnoworktasksprefsPanelViews extends \Innomatic\Desktop\Panel\PanelViews
{
    public $pageTitle;
    public $toolbars;
    public $pageStatus;
    public $innoworkCore;
    public $xml;
    protected $localeCatalog;

    public function update($observable, $arg = '')
    {
        switch ($arg) {
            case 'status':
                $this->pageStatus = $this->_controller->getAction()->status;
                break;
        }
    }

    public function beginHelper()
    {
        $this->localeCatalog = new LocaleCatalog(
            'innowork-tasks::tasks_domain_prefs',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
       );

        $this->innoworkCore = InnoworkCore::instance('innoworkcore',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
       );
        
$this->pageTitle = $this->localeCatalog->getStr('preferences.title');
$this->toolbars['prefs'] = array(
    'prefs' => array(
        'label' => $this->localeCatalog->getStr('preferences.toolbar'),
        'themeimage' => 'settings1',
        'horiz' => 'true',
        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworktasksprefs', array( array(
            'view',
            'default',
            '' ) ) )
        ),
    'newfield' => array(
        'label' => $this->localeCatalog->getStr('newfield.toolbar'),
        'themeimage' => 'filenew',
        'horiz' => 'true',
        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworktasksprefs', array( array(
            'view',
            'newfield',
            '' ) ) )
        )    );
    }

    public function endHelper()
    {
        $this->_wuiContainer->addChild(new WuiInnomaticPage('page', array(
    'pagetitle' => $this->pageTitle,
    'icon' => 'settings1',
    'toolbars' => array(
        new WuiInnomaticToolbar(
            'view',
            array(
                'toolbars' => $this->toolbars, 'toolbar' => 'true'
              )),
        new WuiInnomaticToolBar(
            'core',
            array(
                'toolbars' => $this->innoworkCore->getMainToolBar(), 'toolbar' => 'true'
              ))
          ),
    'maincontent' => new WuiXml(
        'page', array(
            'definition' => $this->xml
          )),
    'status' => $this->pageStatus
  )));
    }

    public function viewdefault($eventData)
    {
        $tabs[0]['label'] = $this->localeCatalog->getStr('status.tab');
        $tabs[1]['label'] = $this->localeCatalog->getStr('priority.tab');
        $tabs[2]['label'] = $this->localeCatalog->getStr('type.tab');
        $tabs[3]['label'] = $this->localeCatalog->getStr('source.tab');
        $tabs[4]['label'] = $this->localeCatalog->getStr('resolution.tab');
    
        $headers[0]['label'] = $this->localeCatalog->getStr('fieldvalue.header');
    
        $this->xml =
        '<vertgroup><name>settings</name>
  <children>
    
    <label><name>fields</name>
      <args>
        <bold>true</bold>
        <label type="encoded">'.urlencode($this->localeCatalog->getStr('fieldvalues.label')).'</label>
      </args>
    </label>
    
    <tab><name>fieldsvalues</name>
      <args>
        <tabs type="array">'.WuiXml::encode($tabs).'</tabs>
        <tabactionfunction>\\fields_tab_action_builder</tabactionfunction>
        <activetab>'.(isset($eventData['tab']) ? $eventData['tab'] : '').'</activetab>
      </args>
      <children>';
    
        $this->xml .=
        '        <table><name>types</name>
          <args>
            <headers type="array">'.WuiXml::encode($headers).'</headers>
          </args>
          <children>';
    
        $row = 0;
        $statuses = InnoworkTaskField::getFields(InnoworkTaskField::TYPE_STATUS);
        while (list($id, $field) = each($statuses))
        {
            $this->xml .=
            '<label row="'.$row.'" col="0"><name>field</name>
  <args>
    <label type="encoded">'.urlencode($field).'</label>
  </args>
</label>
<innomatictoolbar row="'.$row.'" col="1"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode(array(
                'view' => array(
                        'show' => array(
                                'label' => $this->localeCatalog->getStr('editfield.button'),
                                'themeimage' => 'pencil',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
                                        'view',
                                        'editfield',
                                        array('id' => $id))))
                       ),
                        'remove' => array(
                                'label' => $this->localeCatalog->getStr('removefield.button'),
                                'themeimage' => 'trash',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'needconfirm' => 'true',
                                'confirmmessage' => $this->localeCatalog->getStr('removefield.confirm'),
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                                        array(
                                                'view',
                                                'default',
                                                ''
                                       ),
                                        array(
                                                'action',
                                                'removefield',
                                                array(
                                                        'id' => $id,
                                                        'fieldtype' => InnoworkTaskField::TYPE_STATUS
                                               ))))
           )))).'</toolbars>
  </args>
</innomatictoolbar>';
    
            $row++;
        }
    
        $this->xml .=
        '          </children>
        </table>';
    
        $this->xml .=
        '        <table><name>types</name>
          <args>
            <headers type="array">'.WuiXml::encode($headers).'</headers>
          </args>
          <children>';
    
        $row = 0;
        $priorities = InnoworkTaskField::getFields(InnoworkTaskField::TYPE_PRIORITY);
        while (list($id, $field) = each($priorities))
        {
            $this->xml .=
            '<label row="'.$row.'" col="0"><name>priority</name>
  <args>
    <label type="encoded">'.urlencode($field).'</label>
  </args>
</label>
<innomatictoolbar row="'.$row.'" col="1"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode(array(
                'view' => array(
                        'show' => array(
                                'label' => $this->localeCatalog->getStr('editfield.button'),
                                'themeimage' => 'pencil',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
                                        'view',
                                        'editfield',
                                        array('id' => $id))))
                       ),
                        'remove' => array(
                                'label' => $this->localeCatalog->getStr('removefield.button'),
                                'themeimage' => 'trash',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'needconfirm' => 'true',
                                'confirmmessage' => $this->localeCatalog->getStr('removefield.confirm'),
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                                        array(
                                                'view',
                                                'default',
                                                ''
                                       ),
                                        array(
                                                'action',
                                                'removefield',
                                                array(
                                                        'id' => $id,
                                                        'fieldtype' => InnoworkTaskField::TYPE_PRIORITY
                                               ))))
           )))).'</toolbars>
  </args>
</innomatictoolbar>';
    
            $row++;
        }
    
        $this->xml .=
        '          </children>
        </table>';
    
        $this->xml .=
        '        <table><name>types</name>
          <args>
            <headers type="array">'.WuiXml::encode($headers).'</headers>
          </args>
          <children>';
    
        $row = 0;
        $types = InnoworkTaskField::getFields(InnoworkTaskField::TYPE_SEVERITY);
        while (list($id, $field) = each($types))
        {
            $this->xml .=
            '<label row="'.$row.'" col="0"><name>field</name>
  <args>
    <label type="encoded">'.urlencode($field).'</label>
  </args>
</label>
<innomatictoolbar row="'.$row.'" col="1"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode(array(
                'view' => array(
                    'show' => array(
                            'label' => $this->localeCatalog->getStr('editfield.button'),
                            'themeimage' => 'pencil',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
                                        'view',
                                        'editfield',
                                        array('id' => $id))))
                       ),
                        'remove' => array(
                                'label' => $this->localeCatalog->getStr('removefield.button'),
                                'themeimage' => 'trash',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'needconfirm' => 'true',
                                'confirmmessage' => $this->localeCatalog->getStr('removefield.confirm'),
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                                        array(
                                                'view',
                                                'default',
                                                ''
                                       ),
                                        array(
                                                'action',
                                                'removefield',
                                                array(
                                                        'id' => $id,
                                                        'fieldtype' => InnoworkTaskField::TYPE_SEVERITY
                                               ))))
           )))).'</toolbars>
  </args>
</innomatictoolbar>';
    
            $row++;
        }
    
        $this->xml .=
        '          </children>
        </table>';
    
        $this->xml .=
        '        <table><name>sources</name>
          <args>
            <headers type="array">'.WuiXml::encode($headers).'</headers>
          </args>
          <children>';
    
        $row = 0;
        $sources = InnoworkTaskField::getFields(InnoworkTaskField::TYPE_SOURCE);
        while (list($id, $field) = each($sources)) {
            $this->xml .=
            '<label row="'.$row.'" col="0"><name>source</name>
  <args>
    <label type="encoded">'.urlencode($field).'</label>
  </args>
</label>
<innomatictoolbar row="'.$row.'" col="1"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode(array(
                'view' => array(
                        'show' => array(
                                'label' => $this->localeCatalog->getStr('editfield.button'),
                                'themeimage' => 'pencil',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
                                        'view',
                                        'editfield',
                                        array('id' => $id))))
                       ),
                        'remove' => array(
                                'label' => $this->localeCatalog->getStr('removefield.button'),
                                'themeimage' => 'trash',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'needconfirm' => 'true',
                                'confirmmessage' => $this->localeCatalog->getStr('removefield.confirm'),
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                                        array(
                                                'view',
                                                'default',
                                                ''
                                       ),
                                        array(
                                                'action',
                                                'removefield',
                                                array(
                                                        'id' => $id,
                                                        'fieldtype' => InnoworkTaskField::TYPE_SOURCE
                                               ))))
           )))).'</toolbars>
  </args>
</innomatictoolbar>';
    
            $row++;
        }
    
        $this->xml .=
        '          </children>
        </table>';
    
        $this->xml .=
        '        <table><name>resolutions</name>
          <args>
            <headers type="array">'.WuiXml::encode($headers).'</headers>
          </args>
          <children>';
    
        $row = 0;
        $sources = InnoworkTaskField::getFields(InnoworkTaskField::TYPE_RESOLUTION);
        while (list($id, $field) = each($sources))
        {
            $this->xml .=
            '<label row="'.$row.'" col="0"><name>resolution</name>
  <args>
    <label type="encoded">'.urlencode($field).'</label>
  </args>
</label>
<innomatictoolbar row="'.$row.'" col="1"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode(array(
                'view' => array(
                        'show' => array(
                                'label' => $this->localeCatalog->getStr('editfield.button'),
                                'themeimage' => 'pencil',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
                                        'view',
                                        'editfield',
                                        array('id' => $id))))
                       ),
                        'remove' => array(
                                'label' => $this->localeCatalog->getStr('removefield.button'),
                                'themeimage' => 'trash',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'needconfirm' => 'true',
                                'confirmmessage' => $this->localeCatalog->getStr('removefield.confirm'),
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                                        array(
                                                'view',
                                                'default',
                                                ''
                                       ),
                                        array(
                                                'action',
                                                'removefield',
                                                array(
                                                        'id' => $id,
                                                        'fieldtype' => InnoworkTaskField::TYPE_RESOLUTION
                                               ))))
           )))).'</toolbars>
  </args>
</innomatictoolbar>';
    
            $row++;
        }
    
        $this->xml .=
        '          </children>
        </table>';
    
        $this->xml .=
        '      </children>
    </tab>
  </children>
</vertgroup>';
    }
    
    public function viewnewfield($eventData)
    {
        $field_types[InnoworkTaskField::TYPE_STATUS] = $this->localeCatalog->getStr('field_status.label');
        $field_types[InnoworkTaskField::TYPE_PRIORITY] = $this->localeCatalog->getStr('field_priority.label');
        $field_types[InnoworkTaskField::TYPE_SEVERITY] = $this->localeCatalog->getStr('field_type.label');
        $field_types[InnoworkTaskField::TYPE_SOURCE] = $this->localeCatalog->getStr('field_source.label');
        $field_types[InnoworkTaskField::TYPE_RESOLUTION] = $this->localeCatalog->getStr('field_resolution.label');
    
        $this->xml .=
        '<vertgroup><name>newfield</name>
  <children>
    
    <table><name>field</name>
      <args>
        <headers type="array">'.WuiXml::encode(
                    array('0' => array(
                            'label' => $this->localeCatalog->getStr('newfield.label')
                   ))).'</headers>
      </args>
      <children>
    
    <form row="0" col="0"><name>field</name>
      <args>
        <method>post</method>
        <action type="encoded">'.urlencode(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                array(
                    'view',
                    'default',
                    ''
                   ),
                array(
                    'action',
                    'addfield',
                    '')
           ))).'</action>
      </args>
      <children>
    
            <grid><name>field</name>
              <children>
    
                <label row="0" col="0"><name>type</name>
                  <args>
                    <label type="encoded">'.urlencode($this->localeCatalog->getStr('fieldtype.label')).'</label>
                  </args>
                </label>
    
                <combobox row="0" col="1"><name>fieldtype</name>
                  <args>
                    <disp>action</disp>
                    <elements type="array">'.WuiXml::encode($field_types).'</elements>
                  </args>
                </combobox>
    
                <label row="1" col="0"><name>value</name>
                  <args>
                    <label type="encoded">'.urlencode($this->localeCatalog->getStr('fieldvalue.label')).'</label>
                  </args>
                </label>
    
                <string row="1" col="1"><name>value</name>
                  <args>
                    <disp>action</disp>
                    <size>30</size>
                  </args>
                </string>
    
              </children>
            </grid>
    
          </children>
        </form>
    
            <button row="1" col="0"><name>apply</name>
              <args>
                <themeimage>buttonok</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'.urlencode(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                        array(
                            'view',
                            'default',
                            ''
                           ),
                        array(
                            'action',
                            'addfield',
                            '')
                   ))).'</action>
                <label type="encoded">'.urlencode($this->localeCatalog->getStr('newfield.submit')).'</label>
                <formsubmit>field</formsubmit>
              </args>
            </button>
    
          </children>
        </table>
      </children>
    </vertgroup>';
    }
    
    public function vieweditfield($eventData)
    {
        $field_types[InnoworkTaskField::TYPE_STATUS] = $this->localeCatalog->getStr('field_status.label');
        $field_types[InnoworkTaskField::TYPE_PRIORITY] = $this->localeCatalog->getStr('field_priority.label');
        $field_types[InnoworkTaskField::TYPE_SEVERITY] = $this->localeCatalog->getStr('field_type.label');
        $field_types[InnoworkTaskField::TYPE_SOURCE] = $this->localeCatalog->getStr('field_source.label');
        $field_types[InnoworkTaskField::TYPE_RESOLUTION] = $this->localeCatalog->getStr('field_resolution.label');
    
        $field = new InnoworkTaskField(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            '',
            $eventData['id']
           );
    
        $this->xml .=
    '<vertgroup><name>editfield</name>
      <children>
    
        <table><name>field</name>
          <args>
            <headers type="array">'.WuiXml::encode(
                array('0' => array(
                    'label' => $this->localeCatalog->getStr('editfield.label')
                   ))).'</headers>
          </args>
          <children>
    
        <form row="0" col="0"><name>field</name>
          <args>
            <method>post</method>
            <action type="encoded">'.urlencode(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                    array(
                        'view',
                        'default',
                        ''
                       ),
                    array(
                        'action',
                        'editfield',
                        array('id' => $eventData['id']))
               ))).'</action>
          </args>
          <children>
    
            <grid><name>field</name>
              <children>
    
                <label row="0" col="0"><name>type</name>
                  <args>
                    <label type="encoded">'.urlencode($this->localeCatalog->getStr('fieldtype.label')).'</label>
                  </args>
                </label>
    
                <combobox row="0" col="1"><name>fieldtype</name>
                  <args>
                    <disp>action</disp>
                    <elements type="array">'.WuiXml::encode($field_types).'</elements>
                    <default>'.$field->mFieldType.'</default>
                  </args>
                </combobox>
    
                <label row="1" col="0"><name>value</name>
                  <args>
                    <label type="encoded">'.urlencode($this->localeCatalog->getStr('fieldvalue.label')).'</label>
                  </args>
                </label>
    
                <string row="1" col="1"><name>value</name>
                  <args>
                    <disp>action</disp>
                    <size>30</size>
                    <value type="encoded">'.urlencode($field->mFieldValue).'</value>
                  </args>
                </string>
    
              </children>
            </grid>
    
          </children>
        </form>
    
            <button row="1" col="0"><name>apply</name>
              <args>
                <themeimage>buttonok</themeimage>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'.urlencode(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                        array(
                            'view',
                            'default',
                            ''
                           ),
                        array(
                            'action',
                            'editfield',
                            array('id' => $eventData['id'] ))
                   ))).'</action>
                <label type="encoded">'.urlencode($this->localeCatalog->getStr('editfield.submit')).'</label>
                <formsubmit>field</formsubmit>
              </args>
            </button>
    
          </children>
        </table>
      </children>
    </vertgroup>';
    }
    
}

function fields_tab_action_builder($tab)
{
	return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
			'view',
			'default',
			array('tab' => $tab)
	)));
}
