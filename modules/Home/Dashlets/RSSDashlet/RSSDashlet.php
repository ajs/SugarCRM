<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2011 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


require_once('include/Dashlets/Dashlet.php');
require_once('include/Sugar_Smarty.php');

class RSSDashlet extends Dashlet 
{
    protected $url = 'http://www.sugarcrm.com/crm/aggregator/rss/1';
    protected $height = '200'; // height of the pad
    protected $images_dir = 'modules/Home/Dashlets/RSSDashlet/images';

    /**
     * Constructor 
     * 
     * @global string current language
     * @param guid $id id for the current dashlet (assigned from Home module)
     * @param array $def options saved for this dashlet
     */
    public function __construct($id, $def) 
    {
        $this->loadLanguage('RSSDashlet', 'modules/Home/Dashlets/'); // load the language strings here
            
        if(!empty($def['height'])) // set a default height if none is set
            $this->height = $def['height'];
            
        if(!empty($def['url']))
            $this->url = $def['url'];
        
        if(!empty($def['title']))
            $this->title = $def['title'];
        else
            $this->title = $this->dashletStrings['LBL_TITLE'];
        
        if(isset($def['autoRefresh'])) $this->autoRefresh = $def['autoRefresh'];
        
        parent::Dashlet($id); // call parent constructor
         
        $this->isConfigurable = true; // dashlet is configurable
        $this->hasScript = false;  // dashlet has javascript attached to it
    }

    /**
     * Displays the dashlet
     * 
     * @return string html to display dashlet
     */
    public function display() 
    {
        $ss = new Sugar_Smarty();
        $ss->assign('saving', $this->dashletStrings['LBL_SAVING']);
        $ss->assign('saved', $this->dashletStrings['LBL_SAVED']);
        $ss->assign('id', $this->id);
        $ss->assign('height', $this->height);
        $ss->assign('rss_output', $this->getRSSOutput($this->url)); 
        $str = $ss->fetch('modules/Home/Dashlets/RSSDashlet/RSSDashlet.tpl');
        return parent::display($this->dashletStrings['LBL_DBLCLICK_HELP']) . $str; // return parent::display for title and such
    }
    
    /**
     * Displays the configuration form for the dashlet
     * 
     * @return string html to display form
     */
    public function displayOptions() {
        global $app_strings, $sugar_version, $sugar_config;
        
        $ss = new Sugar_Smarty();
        $ss->assign('titleLbl', $this->dashletStrings['LBL_CONFIGURE_TITLE']);
        $ss->assign('heightLbl', $this->dashletStrings['LBL_CONFIGURE_HEIGHT']);
        $ss->assign('rssUrlLbl', $this->dashletStrings['LBL_CONFIGURE_RSSURL']);
        $ss->assign('saveLbl', $app_strings['LBL_SAVE_BUTTON_LABEL']);
        $ss->assign('title', $this->title);
        $ss->assign('height', $this->height);
        $ss->assign('url', $this->url);
        $ss->assign('id', $this->id);
        if($this->isAutoRefreshable()) {
       		$ss->assign('isRefreshable', true);
			$ss->assign('autoRefresh', $GLOBALS['app_strings']['LBL_DASHLET_CONFIGURE_AUTOREFRESH']);
			$ss->assign('autoRefreshOptions', $this->getAutoRefreshOptions());
			$ss->assign('autoRefreshSelect', $this->autoRefresh);
		}
        
        return parent::displayOptions() . $ss->fetch('modules/Home/Dashlets/RSSDashlet/RSSDashletOptions.tpl');
    }  

    /**
     * called to filter out $_REQUEST object when the user submits the configure dropdown
     * 
     * @param array $req $_REQUEST
     * @return array filtered options to save
     */  
    public function saveOptions(
        array $req
        ) 
    {
        $options = array();
        $options['title'] = $req['title'];
        $options['url'] = $req['url'];
        $options['height'] = $req['height'];
        $options['autoRefresh'] = empty($req['autoRefresh']) ? '0' : $req['autoRefresh'];
        
        return $options;
    }
    
    protected function getRSSOutput(
        $url
        )
    {
        // suppress XML errors
        libxml_use_internal_errors(true);
        $rssdoc = simplexml_load_file($url);
        // return back the error message if the loading wasn't successful
        if (!$rssdoc)
            return $this->dashletStrings['ERR_LOADING_FEED'];
        
        $output = "<table class='edit view'>";
        if ( isset($rssdoc->channel) ) {
            foreach ( $rssdoc->channel as $channel ) {
                if ( isset($channel->item ) ) {
                    foreach ( $channel->item as $item ) {
                        $output .= <<<EOHTML
<tr>
<td>
    <h3><a href="{$item->link}" target="_child">{$item->title}</a></h3>
    {$item->description}
</td>
</tr>
EOHTML;
                    }
                }
            }
        }
        else {
            foreach ( $rssdoc->entry as $entry ) {
                $output .= <<<EOHTML
<tr>
<td>
    <h3><a href="{$entry->link}" target="_child">{$entry->title}</a></h3>
    {$entry->summary}
</td>
</tr>
EOHTML;
            }
        }
        $output .= "</table>";
        
        return $output;
    }
}
