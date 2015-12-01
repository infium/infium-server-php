<?php
class UserInterface
{
	private $userInterfaceObject = NULL;
	
	function __construct()
	{
		$this->userInterfaceObject['Response'] = 'UI';
	}
		
	public function setTitle($newTitle)
	{
		$this->userInterfaceObject['Data']['Title'] = $newTitle;
	}
	
	public function setWindow($newWindow)
	{
		$this->userInterfaceObject['Data']['Window'] = $newWindow;
	}
	

	public function setMethod($newMethod)
	{
		$this->userInterfaceObject['Data']['Method'] = $newMethod;
	}
	
	public function setUrl($newUrl)
	{
		$this->userInterfaceObject['Data']['Url'] = $newUrl;
	}
	
	public function setTitleBarColorNewWindow($newTitleBarColorNewWindow)
	{
		$this->userInterfaceObject['Data']['TitleBarColorNewWindow'] = $newTitleBarColorNewWindow;
	}
	
	public function setButtonLabel($newButtonLabel)
	{
		$this->userInterfaceObject['Data']['ButtonLabel'] = $newButtonLabel;
	}

	public function setVisibleData($newValue)
	{
		$this->userInterfaceObject['Data']['VisibleData'] = $newValue;
	}

	public function setVisibleDataDescription($newValue)
	{
		$this->userInterfaceObject['Data']['VisibleDataDescription'] = $newValue;
	}

	public function setHiddenData($newHiddenData)
	{
		$this->userInterfaceObject['Data']['HiddenData'] = $newHiddenData;
	}
	
	public function addButton($icon, $label, $window, $question, $method, $url, $body, $titleBarColorNewWindow)
	{
		$newExtraButton['Icon'] = $icon;
		$newExtraButton['Label'] = $label;
		$newExtraButton['Window'] = $window;
		
		if ($question != NULL){
			$newExtraButton['Question'] = $question;
		}
		
		$newExtraButton['Method'] = $method;
		$newExtraButton['Url'] = $url;
		
		if ($body != NULL){
			$newExtraButton['Body'] = $body;
		}
		
		if ($titleBarColorNewWindow != NULL){
			$newExtraButton['TitleBarColorNewWindow'] = $titleBarColorNewWindow;
		}
		
		$this->userInterfaceObject['Data']['Buttons'][] = $newExtraButton;
	}
	
	public function addLabelHeader($newLabel)
	{
		$newLabelHeader['Type'] = 'LabelHeader';
		$newLabelHeader['Label'] = $newLabel;
		
		$this->userInterfaceObject['Data']['Structure'][] = $newLabelHeader;
	}
	
	public function addLabelWithLink($newLabel,$newMethod,$newUrl,$newIndent,$newRefresh = NULL)
	{
		syslog(LOG_WARNING, 'Depreciated function addLabelWithLink has been used.');
		
		$newLabelWithLink['Type'] = 'LabelWithLink';
		$newLabelWithLink['Label'] = $newLabel;
		$newLabelWithLink['Method'] = $newMethod;
		$newLabelWithLink['Url'] = $newUrl;
		$newLabelWithLink['Indent'] = $newIndent;
		
		if ($newRefresh != NULL){
			$newLabelWithLink['Refresh'] = $newRefresh;
		}
		
		$this->userInterfaceObject['Data']['Structure'][] = $newLabelWithLink;
	}
	
	public function addLabelValueLink($label, $value = NULL, $method = NULL, $url = NULL, $body = NULL, $titleBarColorNewWindow = NULL, $indent = NULL, $iconUnicode = NULL, $iconColor = NULL)
	{
		$newLabelValueLink['Type'] = 'LabelValueLink';
		$newLabelValueLink['Label'] = $label;
		
		if ($value != NULL){
			$newLabelValueLink['Value'] = $value;
		}
		
		if ($method != NULL){
			$newLabelValueLink['Method'] = $method;
		}
		
		if ($url != NULL){
			$newLabelValueLink['Url'] = $url;
		}
		
		if ($body != NULL){
			$newLabelValueLink['Body'] = $body;
		}
		
		if ($titleBarColorNewWindow != NULL){
			$newLabelValueLink['TitleBarColorNewWindow'] = $titleBarColorNewWindow;
		}
		
		if ($indent != NULL){
			$newLabelValueLink['Indent'] = $indent;
		}
		
		if ($iconUnicode != NULL){
			$newLabelValueLink['IconUnicode'] = json_decode('"'.'\u'.$iconUnicode.'"');
		}
		
		if ($iconColor != NULL){
			$newLabelValueLink['IconColor'] = $iconColor;
		}
		
		
		$this->userInterfaceObject['Data']['Structure'][] = $newLabelValueLink;
	}
	
	public function addLabelValue($label, $value)
	{
		$this->addLabelValueLink($label, $value);
	}
	
	public function addField($newName,$newParent,$newLabel, $keyboardType = 'Normal')
	{
		$newField['Type'] = 'Field';
		$newField['Name'] = $newName;
		$newField['Label'] = $newLabel;
		$newField['KeyboardType'] = $keyboardType;
		
		if ($newParent == NULL){
			$this->userInterfaceObject['Data']['Structure'][] = $newField;
		}else{
			$NumberOfElements = count($this->userInterfaceObject['Data']['Structure']);
			for ($i = 0; $i < $NumberOfElements; $i++){
				if ($this->userInterfaceObject['Data']['Structure'][$i]['Name'] == $newParent){
					$this->userInterfaceObject['Data']['Structure'][$i]['Column'][] = $newField;
				}
			}
		}
		
	}
	
	public function addTable($newName, $newLabel = NULL)
	{
		$newTable['Type'] = 'Table';
		$newTable['Name'] = $newName;
		if ($newLabel != NULL){
			$newTable['Label'] = $newLabel;
			
		}
		
		$this->userInterfaceObject['Data']['Structure'][] = $newTable;
	}
	
	public function addImageUpload($newName,$newMaxImageWidth,$newMaxImageHeight,$newImageQuality)
	{
		$newImageUpload['Type'] = 'ImageUpload';
		$newImageUpload['Name'] = $newName;
		$newImageUpload['MaxImageWidth'] = $newMaxImageWidth;
		$newImageUpload['MaxImageHeight'] = $newMaxImageHeight;
		$newImageUpload['ImageQuality'] = $newImageQuality;
		
		$this->userInterfaceObject['Data']['Structure'][] = $newImageUpload;
	}
	
	public function addSearchSelection($newName,$newLabel,$newSearchUrl, $parentTable = NULL)
	{
		$newField['Type'] = 'SearchSelection';
		$newField['Name'] = $newName;
		$newField['Label'] = $newLabel;
		$newField['SearchUrl'] = $newSearchUrl;
		
		if ($parentTable == NULL){
			$this->userInterfaceObject['Data']['Structure'][] = $newField;
		}else{
			$NumberOfElements = count($this->userInterfaceObject['Data']['Structure']);
			for ($i = 0; $i < $NumberOfElements; $i++){
				if ($this->userInterfaceObject['Data']['Structure'][$i]['Name'] == $parentTable){
					$this->userInterfaceObject['Data']['Structure'][$i]['Column'][] = $newField;
				}
			}
		}
	}
	
	public function addLabelTrueFalse($newName, $newLabel)
	{
		$newLabelTrueFalse['Type'] = 'LabelTrueFalse';
		$newLabelTrueFalse['Name'] = $newName;
		$newLabelTrueFalse['Label'] = $newLabel;
		
		$this->userInterfaceObject['Data']['Structure'][] = $newLabelTrueFalse;
	}
	
	public function getObjectAsJSONString()
	{
		header('Content-type: application/json');
		$this->userInterfaceObject['Debug'] = 'Debug not yet implemented';
		return json_encode($this->userInterfaceObject,JSON_PRETTY_PRINT);
	}
}

?>