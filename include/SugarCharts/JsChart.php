<?php
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




require_once("include/SugarCharts/SugarChart.php");

class JsChart extends SugarChart {
	protected $ss;
	var $xmlFile;
	var $jsonFilename;
	var $chartId;
	var $width;
	var $height;
	var $chartType;

	function __construct() {
		parent::__construct();
	}

	function isSupported($chartType) {
		$charts = array(
			"stacked group by chart",
			"group by chart",
			"bar chart",
			"horizontal group by chart",
			"horizontal",
			"horizontal bar chart",
			"pie chart",
			"gauge chart",
			"funnel chart 3D",
			"line chart",
		);

		if(in_array($chartType,$charts)) {
			return true;
		} else {
			return false;
		}

	}

	function tab($str, $depth){
       // $str = preg_replace('/(<\w+>)(.*)(<\/\w+>)/e', "'\\1'.htmlentities(from_html('\\2')).'\\3'", $str);
        return str_repeat("\t", $depth) . $str . "\n";
	}

	function display($name, $xmlFile, $width='320', $height='480', $resize=false) {


		$this->chartId = $name;
		$this->height = $height;
		$this->width = $width;
		$this->xmlFile = $xmlFile;
		$this->chartType = $this->chart_properties['type'];

		$style = array();
		$chartConfig = array();
        try {
		    $xmlStr = $this->processXML($this->xmlFile);
		    $json = $this->buildJson($xmlStr);
        }
        catch(Exception $e) {
            $GLOBALS['log']->fatal("Unable to return chart data, invalid xml for file {$this->xmlFile}");
            return '';
        }
		$this->saveJsonFile($json);
		$this->ss->assign("chartId", $this->chartId);
		$this->ss->assign("filename", $this->jsonFilename);


		$dimensions = $this->getChartDimensions($xmlStr);
		$this->ss->assign("width", $dimensions['width']);
		$this->ss->assign("height", $dimensions['height']);
		$config = $this->getConfigProperties();
		$style['gridLineColor'] = str_replace("0x","#",$config->gridLines);
		$style['font-family'] = $config->labelFontFamily;
		$style['color'] = str_replace("0x","#",$config->labelFontColor);
		$this->ss->assign("css", $style);
		foreach($this->getChartConfigParams($xmlStr) as $key => $value) {
			$chartConfig[$key] = $value;
		}
		$chartConfig['imageExportType'] = $this->image_export_type;
		$this->ss->assign("config", $chartConfig);
		if($json == "No Data") {
			$this->ss->assign("error", "No Data");
		}

		if(!$this->isSupported($this->chartType)) {
			$this->ss->assign("error", "Unsupported Chart Type");
		}

	}


	function getDashletScript($id,$xmlFile="") {

		global $sugar_config, $current_user, $current_language;
		$this->id = $id;
		$this->chartId = $id;
		$this->xmlFile = (!$xmlFile) ? $sugar_config['tmp_dir']. $current_user->id . '_' . $this->id . '.xml' : $xmlFile;


		$style = array();
		$chartConfig = array();
		$this->ss->assign("chartId", $this->chartId);
		$this->ss->assign("filename", str_replace(".xml",".js",$this->xmlFile));
		$config = $this->getConfigProperties();
		$style['gridLineColor'] = str_replace("0x","#",$config->gridLines);
		$style['font-family'] = $config->labelFontFamily;
		$style['color'] = str_replace("0x","#",$config->labelFontColor);
		$this->ss->assign("css", $style);
		$xmlStr = $this->processXML($this->xmlFile);
		foreach($this->getChartConfigParams($xmlStr) as $key => $value) {
			$chartConfig[$key] = $value;
		}
		
		$chartConfig['imageExportType'] = $this->image_export_type;
		$this->ss->assign("config", $chartConfig);

	}

	function chartArray($chartsArray) {

		$customChartsArray = array();
		$style = array();
		$chartConfig = array();
		foreach($chartsArray as $id => $data) {
			$customChartsArray[$id] = array();
			$customChartsArray[$id]['chartId'] = $id;
			$customChartsArray[$id]['filename'] = str_replace(".xml",".js",$data['xmlFile']);
			$customChartsArray[$id]['width'] = $data['width'];
			$customChartsArray[$id]['height'] = $data['height'];

			$config = $this->getConfigProperties();
			$style['gridLineColor'] = str_replace("0x","#",$config->gridLines);
			$style['font-family'] = (string)$config->labelFontFamily;
			$style['color'] = str_replace("0x","#",$config->labelFontColor);
			$customChartsArray[$id]['css'] = $style;
			$xmlStr = $this->processXML($data['xmlFile']);
			$xml = new SimpleXMLElement($xmlStr);
			$params = $this->getChartConfigParams($xmlStr);
			$customChartsArray[$id]['supported'] = ($this->isSupported($xml->properties->type)) ? "true" : "false";
			foreach($params as $key => $value) {
				$chartConfig[$key] = $value;
			}
			$chartConfig['imageExportType'] = $this->image_export_type;
			$customChartsArray[$id]['chartConfig'] = $chartConfig;
		}

		return $customChartsArray;
	}

	function getChartConfigParams($xmlStr) {

		$xml = new SimpleXMLElement($xmlStr);

		$chartType = $xml->properties->type;
		if($chartType == "pie chart") {
			return array ("pieType" => "basic","tip" => "name","chartType" => "pieChart");
		} elseif($chartType == "line chart") {
			return array ("lineType" => "basic","tip" => "name","chartType" => "lineChart");
		} elseif($chartType == "funnel chart 3D") {
			return array ("funnelType" => "basic","tip" => "name","chartType" => "funnelChart");
		} elseif($chartType == "gauge chart") {
			return array ("gaugeType" => "basic","tip" => "name","chartType" => "gaugeChart");
		} elseif($chartType == "stacked group by chart") {
			return array ("orientation" => "vertical","barType" => "stacked","tip" => "name","chartType" => "barChart");
		} elseif($chartType == "group by chart") {
			return array("orientation" => "vertical", "barType" => "grouped", "tip" => "title","chartType" => "barChart");
		} elseif($chartType == "bar chart") {
			return array("orientation" => "vertical", "barType" => "basic", "tip" => "label","chartType" => "barChart");
		} elseif ($chartType == "horizontal group by chart") {
			return array("orientation" => "horizontal", "barType" => "stacked", "tip" => "name","chartType" => "barChart");
		} elseif ($chartType == "horizontal bar chart" || "horizontal") {
			return array("orientation" => "horizontal","barType" => "basic","tip" => "label","chartType" => "barChart");
		} else {
			return array("orientation" => "vertical","barType" => "stacked","tip" => "name","chartType" => "barChart");
		}
	}
	function getChartDimensions($xmlStr) {
		if($this->getNumNodes($xmlStr) > 9 && $this->chartType != "pie chart") {
			if($this->chartType == "horizontal group by chart" || $this->chartType == "horizontal bar chart") {
				$height = ($this->getNumNodes($xmlStr) * 60) + 100;
				return array("width"=>$this->width, "height"=>($height));
			} else {
				return array("width"=>($this->width * 2), "height"=>$this->height);
			}
		} else {
			return array("width"=>"100%", "height"=>$this->height);
		}
	}

	function checkData($xmlstr) {
        $xml = new SimpleXMLElement($xmlstr);
        if(sizeof($xml->data->group) > 0) {
			return true;
		} else {
			return false;
		}
	}

	function getNumNodes($xmlstr) {
		$xml = new SimpleXMLElement($xmlstr);
		return sizeof($xml->data->group);
	}

  	function buildProperties($xmlstr) {
		$content = $this->tab("'properties': [\n",1);
		$properties = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->properties->children() as $property) {
			$properties[] = $this->tab("'".$property->getName()."':"."'".$this->processSpecialChars($property)."'",2);
		}
		$content .= $this->tab("{\n",1);
		$content .= join(",\n",$properties)."\n";
		$content .= $this->tab("}\n",1);
		$content .= $this->tab("],\n",1);
		return $content;
	}

  	function buildLabelsBarChartStacked($xmlstr) {
		$content = $this->tab("'label': [\n",1);
		$labels = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group[0]->subgroups->group as $group) {
			$labels[] = $this->tab("'".$this->processSpecialChars($group->title)."'",2);
		}
		$content .= join(",\n",$labels)."\n";
		$content .= $this->tab("],\n",1);
		return $content;
	}

  	function buildLabelsBarChart($xmlstr) {
		$content = $this->tab("'label': [\n",1);
		$labels = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$labels[] = $this->tab("'".$this->processSpecialChars($group->title)."'",2);
		}
		$labelStr = join(",\n",$labels)."\n";
		$content .= $labelStr;
		$content .= $this->tab("],\n",1);
		return $content;
	}

	function buildDataBarChartStacked($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$groupcontent = $this->tab("{\n",1);
			$groupcontent .= $this->tab("'label': '".$this->processSpecialChars($group->title)."',\n",2);
			$groupcontent .= $this->tab("'gvalue': '{$group->value}',\n",2);
			$groupcontent .= $this->tab("'gvaluelabel': '{$group->label}',\n",2);
			$subgroupValues = array();
			$subgroupValueLabels = array();
			$subgroupLinks = array();
			foreach($group->subgroups->group as $subgroups) {
				$subgroupValues[] = $this->tab(($subgroups->value == "NULL") ? 0 : $subgroups->value,3);
				$subgroupValueLabels[] = $this->tab("'".$this->processSpecialChars($subgroups->label)."'",3);
				$subgroupLinks[] = $this->tab("'".$subgroups->link."'",3);
			}
			$subgroupValuesStr = join(",\n",$subgroupValues)."\n";
			$subgroupValueLabelsStr = join(",\n",$subgroupValueLabels)."\n";
			$subgroupLinksStr = join(",\n",$subgroupLinks)."\n";

			$groupcontent .= $this->tab("'values': [\n".$subgroupValuesStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'valuelabels': [\n".$subgroupValueLabelsStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'links': [\n".$subgroupLinksStr,2);
			$groupcontent .= $this->tab("]\n",2);
			$groupcontent .= $this->tab("}",1);
			$data[] = $groupcontent;
		}
		$content .= join(",\n",$data)."\n";
		$content .= $this->tab("]",1);
		return $content;
	}

	function buildDataBarChartGrouped($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$groupcontent = $this->tab("{\n",1);
			$groupcontent .= $this->tab("'label': '".$this->processSpecialChars($group->title)."',\n",2);
			$groupcontent .= $this->tab("'gvalue': '{$group->value}',\n",2);
			$groupcontent .= $this->tab("'gvaluelabel': '{$group->label}',\n",2);
			$subgroupValues = array();
			$subgroupValueLabels = array();
			$subgroupLinks = array();
			$subgroupTitles = array();
			foreach($group->subgroups->group as $subgroups) {
				$subgroupValues[] = $this->tab(($subgroups->value == "NULL") ? 0 : $subgroups->value,3);
				$subgroupValueLabels[] = $this->tab("'".$subgroups->label."'",3);
				$subgroupLinks[] = $this->tab("'".$subgroups->link."'",3);
				$subgroupTitles[] = $this->tab("'".$this->processSpecialChars($subgroups->title)."'",3);
			}
			$subgroupValuesStr = join(",\n",$subgroupValues)."\n";
			$subgroupValueLabelsStr = join(",\n",$subgroupValueLabels)."\n";
			$subgroupLinksStr = join(",\n",$subgroupLinks)."\n";
			$subgroupTitlesStr = join(",\n",$subgroupTitles)."\n";

			$groupcontent .= $this->tab("'values': [\n".$subgroupValuesStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'valuelabels': [\n".$subgroupValueLabelsStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'links': [\n".$subgroupLinksStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'titles': [\n".$subgroupTitlesStr,2);
			$groupcontent .= $this->tab("]\n",2);
			$groupcontent .= $this->tab("}",1);
			$data[] = $groupcontent;
		}
		$content .= join(",\n",$data)."\n";
		$content .= $this->tab("]",1);
		return $content;
	}

	function buildDataBarChart($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		$groupcontent = "";
		$groupcontentArr = array();

		foreach($xml->data->group as $group) {
		$groupcontent = $this->tab("{\n",1);
		$groupcontent .= $this->tab("'label': [\n",2);
		$groupcontent .= $this->tab("'".$this->processSpecialChars($group->title)."'\n",3);
		$groupcontent .= $this->tab("],\n",2);
		$groupcontent .= $this->tab("'values': [\n",2);
		$groupcontent .= $this->tab(($group->value == "NULL") ? 0 : $group->value."\n",3);
		$groupcontent .= $this->tab("],\n",2);
		if($group->label) {
			$groupcontent .= $this->tab("'valuelabels': [\n",2);
			$groupcontent .= $this->tab("'{$group->label}'\n",3);
			$groupcontent .= $this->tab("],\n",2);
		}
		$groupcontent .= $this->tab("'links': [\n",2);
		$groupcontent .= $this->tab("'{$group->link}'\n",3);
		$groupcontent .= $this->tab("]\n",2);
		$groupcontent .= $this->tab("}",1);
		$groupcontentArr[] = $groupcontent;
		}
		$content .= join(",\n",$groupcontentArr)."\n";
		$content .= $this->tab("]",1);
		return $content;
	}

	  function buildLabelsPieChart($xmlstr) {
		$content = $this->tab("'label': [\n",1);
		$labels = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$labels[] = $this->tab("'".$this->processSpecialChars($group->title)."'",2);
		}
		$labelStr = join(",\n",$labels)."\n";
		$content .= $labelStr;
		$content .= $this->tab("],\n",1);
		return $content;
	}


	function buildDataPieChart($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		$groupcontent = "";
		$groupcontentArr = array();

		foreach($xml->data->group as $group) {
		$groupcontent = $this->tab("{\n",1);
		$groupcontent .= $this->tab("'label': [\n",2);
		$groupcontent .= $this->tab("'".$this->processSpecialChars($group->title)."'\n",3);
		$groupcontent .= $this->tab("],\n",2);
		$groupcontent .= $this->tab("'values': [\n",2);
		$groupcontent .= $this->tab("{$group->value}\n",3);
		$groupcontent .= $this->tab("],\n",2);
		$groupcontent .= $this->tab("'valuelabels': [\n",2);
		$groupcontent .= $this->tab("'{$group->label}'\n",3);
		$groupcontent .= $this->tab("],\n",2);
		$groupcontent .= $this->tab("'links': [\n",2);
		$groupcontent .= $this->tab("'{$group->link}'\n",3);
		$groupcontent .= $this->tab("]\n",2);
		$groupcontent .= $this->tab("}",1);
		$groupcontentArr[] = $groupcontent;
		}


		$content .= join(",\n",$groupcontentArr)."\n";
		$content .= $this->tab("\n]",1);
		return $content;
	}

	function buildLabelsGaugeChart($xmlstr) {
		$content = $this->tab("'label': [\n",1);
		$labels = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$labels[] = $this->tab("'".$this->processSpecialChars($group->title)."'",2);
		}
		$labelStr = join(",\n",$labels)."\n";
		$content .= $labelStr;
		$content .= $this->tab("],\n",1);
		return $content;
	}

	function buildDataGaugeChart($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$groupcontent = $this->tab("{\n",1);
			$groupcontent .= $this->tab("'label': '".$this->processSpecialChars($group->title)."',\n",2);
			$groupcontent .= $this->tab("'gvalue': '{$group->value}',\n",2);
			$finalComma = ($group->title != "GaugePosition") ? "," : "";
			$groupcontent .= $this->tab("'gvaluelabel': '{$group->label}'{$finalComma}\n",2);
			$subgroupTitles = array();
			$subgroupValues = array();
			$subgroupValueLabels = array();
			$subgroupLinks = array();

			if(is_object($group->subgroups->group)) {
				foreach($group->subgroups->group as $subgroups) {
					$subgroupTitles[] = $this->tab("'".$subgroups->title."'",3);
					//$subgroupValues[] = $this->tab($subgroups->value,3);
					$subgroupValues[] = $subgroups->value;
					$subgroupValueLabels[] = $this->tab("'".$subgroups->label."'",3);
					$subgroupLinks[] = $this->tab("'".$subgroups->link."'",3);
				}
				$subgroupTitlesStr = join(",\n",$subgroupTitles)."\n";
				$subgroupValuesStr = join(",\n",$subgroupValues)."\n";
				$subgroupValueLabelsStr = join(",\n",$subgroupValueLabels)."\n";
				$subgroupLinksStr = join(",\n",$subgroupLinks)."\n";

				//$groupcontent .= $this->tab("'labels': [\n".$subgroupTitlesStr,2);
				//$groupcontent .= $this->tab("],\n",2);
				$val = ((int)$subgroupValues[1] == (int)$subgroupValues[0]) ? $this->tab($subgroupValues[1],3)."\n" : $this->tab($subgroupValues[1] - $subgroupValues[0],3)."\n";

				$groupcontent .= $this->tab("'values': [\n".$val,2);
				$groupcontent .= $this->tab("],\n",2);
				$groupcontent .= $this->tab("'valuelabels': [\n".$subgroupValueLabelsStr,2);
				$groupcontent .= $this->tab("]\n",2);
				//$groupcontent .= $this->tab("'links': [\n".$subgroupLinksStr,2);
				//$groupcontent .= $this->tab("]\n",2);

			}

				$groupcontent .= $this->tab("}",1);
				$data[] = $groupcontent;

		}

		$content .= join(",\n",$data)."\n";


		$content .= $this->tab("]",1);
		return $content;
	}


	function getConfigProperties() {
		$path = SugarThemeRegistry::current()->getImageURL('sugarColors.xml',false);

		if(!file_exists($path)) {
			$GLOBALS['log']->debug("Cannot open file ($path)");
		}
		$xmlstr = file_get_contents($path);
		$xml = new SimpleXMLElement($xmlstr);
		return $xml->charts;
	}

	function buildChartColors() {

		$content = $this->tab("'color': [\n",1);
		$colorArr = array();
		$xml = $this->getConfigProperties();
		$colors = ($this->chartType == "gauge chart") ? $xml->gaugeChartElementColors->color : $xml->chartElementColors->color;
		foreach($colors as $color) {
			$colorArr[] = $this->tab("'".str_replace("0x","#",$color)."'",2);
		}
		$content .= join(",\n",$colorArr)."\n";
		$content .= $this->tab("],\n",1);

		return $content;

	}

	function buildJson($xmlstr){
		if($this->checkData($xmlstr)) {
			$content = "{\n";
			if ($this->chartType == "pie chart" || $this->chartType == "funnel chart 3D") {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsPieChart($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataPieChart($xmlstr);
			}
			elseif ($this->chartType == "gauge chart") {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsGaugeChart($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataGaugeChart($xmlstr);
			}
			elseif ($this->chartType == "horizontal bar chart" || $this->chartType == "bar chart") {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsBarChart($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataBarChart($xmlstr);
			}
			elseif ($this->chartType == "group by chart") {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsBarChartStacked($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataBarChartGrouped($xmlstr);
			} else {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsBarChartStacked($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataBarChartStacked($xmlstr);
			}
			$content .= "\n}";
			return $content;
		} else {
			return "No Data";
		}
	}

	function buildHTMLLegend($xmlFile) {
		$xmlstr = $this->processXML($xmlFile);
		$xml = new SimpleXMLElement($xmlstr);
		$this->chartType = $xml->properties->type;
		$html = "<table align=\"left\" cellpadding=\"2\" cellspacing=\"2\">";

		if ($this->chartType == "group by chart" || $this->chartType == "horizontal group by chart") {
			$groups = $xml->data->group[0]->subgroups->group;
			$items = (sizeof($xml->data->group[0]->subgroups->group) <= 5) ? 5 : sizeof($xml->data->group[0]->subgroups->group);
		} else {
			$groups = $xml->data->group;
			$items = (sizeof($xml->data->group) <= 5) ? 5 : sizeof($xml->data->group);
		}

		$rows = ceil($items/5);
		$fullItems = $rows * 5;
		$remainder = ($items < $fullItems) ? $fullItems - $items : 0;
		$i = 0;
		$x = 0;


		$colorArr = array();
		$xmlColors = $this->getConfigProperties();
		$colors = ($this->chartType == "gauge chart") ? $xmlColors->gaugeChartElementColors->color : $xmlColors->chartElementColors->color;

		foreach($colors as $color) {
			$colorArr[] = str_replace("0x","#",$color);
		}


		foreach($groups as $group) {
			if($i == 5) {$i = 0;}
			$html .= ($i == 0) ? "<tr>" : "";
			$html .= "<td width=\"50\">";
			$html .= "<div style=\"background-color:".$colorArr[$x].";\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>";
			$html .= "</td>";
			$html .= "<td>";
			$html .= $group->title;
			$html .= "</td>";
			$html .= ($x+1 == $items) ? "<td colspan=".($remainder*2)."></td>" : "";
			$html .= ($i == 4) ? "</tr>" : "";
			$x++;
			$i++;
		}


		$html .= "</table>";
		return $html;
	}

	function saveJsonFile($jsonContents) {

		$this->jsonFilename = str_replace(".xml",".js",$this->xmlFile);
		//$jsonContents = mb_convert_encoding($jsonContents, 'UTF-16LE', 'UTF-8');

		// open file
		if (!$fh = sugar_fopen($this->jsonFilename, 'w')) {
			$GLOBALS['log']->debug("Cannot open file ($this->jsonFilename)");
			return;
		}

		// write the contents to the file
		if (fwrite($fh,$jsonContents) === FALSE) {
			$GLOBALS['log']->debug("Cannot write to file ($this->jsonFilename)");
			return false;
		}

		$GLOBALS['log']->debug("Success, wrote ($jsonContents) to file ($this->jsonFilename)");

		fclose($fh);
		return true;
	}

	function get_image_cache_file_name ($xmlFile,$ext = ".png") {
		$filename = str_replace("/xml/","/images/",str_replace(".xml",$ext,$xmlFile));

		return $filename;
	}


	function getXMLChartProperties($xmlStr) {
		$props = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->properties->children() as $properties) {
			$props[$properties->getName()] = $properties;
		}
		return $props;
	}
	
	function processSpecialChars($str) {
		return addslashes(html_entity_decode($str,ENT_QUOTES));
	}
	
	function processXML($xmlFile) {

		if(!file_exists($xmlFile)) {
			$GLOBALS['log']->debug("Cannot open file ($xmlFile)");
		}

		$pattern = array();
		$replacement = array();
		$content = file_get_contents($xmlFile);
		$content = mb_convert_encoding($content, 'UTF-8','UTF-16LE' );
		$pattern[] = '/\<link\>([a-zA-Z0-9#?&%.;\[\]\/=+_-\s]+)\<\/link\>/e';
		$replacement[] = "'<link>'.urlencode(\"$1\").'</link>'";
//		$pattern[] = '/NULL/e';
//		$replacement[] = "";
		return preg_replace($pattern,$replacement, $content);
	}


}

?>
