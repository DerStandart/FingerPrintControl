<?
class FingerPrintControl extends IPSModule {
	public function Create(){
		//Never delete this line!
		parent::Create();
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->CreateVariableProfile("SIB.FCP.Actions", 1, "", 1, 50, 0, 0, "");

		$this->RegisterPropertyInteger("ReaderVariable", 0);
		$this->RegisterPropertyInteger("UserVariable", 0);
		$this->RegisterPropertyInteger("FingerPrintVariable", 0);

		$this->RegisterPropertyString("Actions", "");
		$this->RegisterPropertyString("Fingers", "");

		$this->RegisterVariableString("UnknownFinger", "UnknownFinger", "", 0);
		$this->RegisterVariableInteger("Actions", "Actions", "SIB.FCP.Actions", 0);


		$this->EnableAction("Actions");
	}
	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
	}
	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();
		if ($this->ReadPropertyInteger("FingerPrintVariable") != 0){
			$this->RegisterMessage($this->ReadPropertyInteger("FingerPrintVariable"), 10603 /* VM_UPDATE */);
		}
		else{
			$this->UnregisterMessage($this->ReadPropertyInteger("FingerPrintVariable"), 10603 /* VM_UPDATE */);
		}
	}
	public function RequestAction($Ident, $Value){
      SetValue($this->GetIDForIdent($Ident), $Value);
  }
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data){
		// $this->LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
		switch ($SenderID){
			case $this->ReadPropertyInteger("FingerPrintVariable"):
			  $FingerData = $Data[0];
				$this->LogMessage("MessageSink", "Data: " . $FingerData);
				$this->CheckFinger($FingerData);
			break;
		}
	}
	private function CheckUser(string $User){
    $this->LogMessage("CheckUser", "Data: " . $User);

	}
	private function CheckFinger(string $CheckFinger){
		$Found = FALSE;
		$this->LogMessage("CheckFinger", "This Finger: " . $CheckFinger);
		$Fingers = json_decode($this->ReadPropertyString("Fingers"));

		foreach($Fingers as $Finger => $value){
			$ThisFinger = $value->FingerID;
			$this->LogMessage("CheckFinger", "Finger " . $ThisFinger);
			if (strcmp($ThisFinger, $CheckFinger) == 0){
				$this->LogMessage("CheckFinger", "Finger Found: " . $ThisFinger);
				$Found = TRUE;
				$Action = $value->Action;
				$ActionScript = $this->GetActionScript($Action);
			  if ($ActionScript != 0){
					IPS_RunScript($ActionScript);
					$this->LogMessage("CheckFinger", "RunScript: " . $Action . " with ID " . $ActionScript);
				}
				else{
					$this->LogMessage("CheckFinger", "ActionScript not found");
				}
				return;
			}
  	}
		if (!$Found){
			$this->LogMessage("CheckFinger", "NO FINGER FOUND");
			$this->UnknownFinger($CheckFinger);
		}
		return;
	}
	private function GetActionScript(int $ActionID){
		$Actions = json_decode($this->ReadPropertyString("Actions"));
		foreach($Actions as $Action => $value){
			$ScriptNumber = $value->ScriptNumber;
			if($ActionID == $ScriptNumber){
			  $ScriptID = $value->ScriptID;
				return $ScriptID;
			}
		}
		return 0;
	}
	private function UnknownFinger(string $UnknownFinger){
		$this->LogMessage("UnknownFinger", $UnknownFinger);
		SetValue(IPS_GetObjectIDByIdent("UnknownFinger", $this->InstanceID), $UnknownFinger);
	}
	public function GetConfigurationForm() {
		$formdata = '{
                "elements":
                  [
										{ "type": "Label", "label": "Modul by https://schrader-it.net"},
										{ "type": "Label", "label": "General Settings"},
										{ "type": "SelectVariable", "name": "ReaderVariable", "caption": "Reader"},
										{ "type": "SelectVariable", "name": "UserVariable", "caption": "User"},
										{ "type": "SelectVariable", "name": "FingerPrintVariable", "caption": "FingerPrint"},
										{ "type": "Label", "label": "Scripts with Actions"},
                    {
                    "type": "List",
                    "name": "Actions",
                    "caption": "Actions",
                    "add": true,
                    "delete": true,
                    "sort": {
                        "column": "name",
                        "direction": "ascending"
                    },
                    "columns": [
												{
                        	"label": "ScriptNumber",
                        	"name": "ScriptNumber",
                        	"width": "85px",
                        	"add": 0,
                        	"edit": {
                            "type": "NumberSpinner"
                         	}
                        },
												{
                        "label": "ScriptID",
                        "name": "ScriptID",
                        "width": "75px",
                        "add": 0,
                        "edit": {
                            "type": "SelectScript"
                        }
                    },{
                        "label": "Name",
                        "name": "Name",
                        "width": "auto",
                        "add": "<-- Select Script"
                    }],
                    "values": []
                },
								{ "type": "Label", "label": "Fingers"},
								{
                      "type": "List",
                      "name": "Fingers",
                      "caption": "Fingers",
                      "add": true,
                      "delete": true,
                      "sort": {
                        "column": "Name",
                        "direction": "descending"
                      },
                      "columns": [{
                        "label": "FingerID",
                        "name": "FingerID",
                        "width": "75px",
                        "add": 0,
                        "edit": {
                            "type": "ValidationTextBox"
                        	}
                        },{
                          "label": "Name",
                          "name": "Name",
                          "width": "auto",
                          "add": "",
													"edit": {
                            "type": "ValidationTextBox"
                         	}
                        },
												{
                          "label": "Action",
                          "name": "Action",
                          "width": "auto",
                          "add": "",
													"edit": {
                            "type": "NumberSpinner"
                         	}
                        }
                      ],
                    "values": []
                    }
                ]
                }
                ';
		$formdata = json_decode($formdata);

		if($this->ReadPropertyString("Actions") != "") {
			//Annotate existing elements
			$Actions = json_decode($this->ReadPropertyString("Actions"));
			foreach($Actions as $action) {
				//We only need to add annotations. Remaining data is merged from persistance automatically.
				//Order is determinted by the order of array elements
				if(IPS_ObjectExists($action->ScriptID) && $action->ScriptID !== 0) {
					$formdata->elements[4]->values[] = Array(
						"SkriptID" => $action->ScriptID, "Name" => IPS_GetName($action->ScriptID),
					);
					IPS_SetVariableProfileAssociation("SIB.FCP.Actions", $action->ScriptNumber, IPS_GetName($action->ScriptID), "", 0);
				}
				else{
					$formdata->elements[4]->values[] = Array(
						"SkriptID" => $action->ScriptID, "Name" => "Not found!",
					);
				}
			}
		}
		return json_encode($formdata);
	}
	protected function LogMessage($Sender, $Message){
		$this->SendDebug($Sender, $Message, 0);
	}
	private function CreateVariableProfile($ProfileName, $ProfileType, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Icon) {
		if (!IPS_VariableProfileExists($ProfileName)) {
			IPS_CreateVariableProfile($ProfileName, $ProfileType);
			IPS_SetVariableProfileText($ProfileName, "", $Suffix);
			IPS_SetVariableProfileValues($ProfileName, $MinValue, $MaxValue, $StepSize);
			IPS_SetVariableProfileDigits($ProfileName, $Digits);
			//IPS_SetVariableProfileIcon($ProfileName, $Icon);
    }
  }
}
