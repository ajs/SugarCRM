{* <!--
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

-->


*}
<div class="dashletPanelMenu">
<div class="hd"><div class="tl"></div><div class="hd-center"></div><div class="tr"></div></div>
<div class="bd">
		<div class="ml"></div>
		<div class="bd-center">
		<div class="screen">
		
{$MY_FRAME}
{foreach  from=$ADMIN_GROUP_HEADER key=j item=val1}
   
   {if isset($GROUP_HEADER[$j][1])}
   <p>{$GROUP_HEADER[$j][0]}{$GROUP_HEADER[$j][1]}
   <table class="other view">
   
   {else}
   <p>{$GROUP_HEADER[$j][0]}{$GROUP_HEADER[$j][2]}
   <table class="other view">
   {/if}
      
    {assign var='i' value=0}
    {foreach  from=$VALUES_3_TAB[$j] key=link_idx item=admin_option}
    {if isset($COLNUM[$j][$i])}
    <tr> 
            <td width="20%" scope="row">{$ITEM_HEADER_IMAGE[$j][$i]}&nbsp;<a href='{$ITEM_URL[$j][$i]}' class="tabDetailViewDL2Link">{$ITEM_HEADER_LABEL[$j][$i]}</a></td>
            <td width="30%">{$ITEM_DESCRIPTION[$j][$i]}</td>  
              
            {assign var='i' value=$i+1}
            {if $COLNUM[$j][$i] == '0'}                           
                    <td width="20%" scope="row">{$ITEM_HEADER_IMAGE[$j][$i]}&nbsp;<a href='{$ITEM_URL[$j][$i]}' class="tabDetailViewDL2Link">{$ITEM_HEADER_LABEL[$j][$i]}</a></td>
                    <td width="30%">{$ITEM_DESCRIPTION[$j][$i]}</td>
              
            {else}
            <td width="20%" scope="row">&nbsp;</td>
            <td width="30%">&nbsp;</td>
            {/if}
   </tr>
   {/if}
   {assign var='i' value=$i+1}
   {/foreach}
           
</table>
<p/>
{/foreach}

</div>
</div>
			<div class="mr"></div>
</div>
<div class="ft"><div class="bl"></div><div class="ft-center"></div><div class="br"></div></div>
</div>