<?
class FingerPrintControl extends IPSModule {
	public function Create(){
        //Never delete this line!
        parent::Create();
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterVariableInteger("One", "Dieses", "", 1);
        $this->RegisterVariableInteger("Two", "Modul", "", 2);
        $this->RegisterVariableInteger("Three", "kann", "", 3);
        $this->RegisterVariableInteger("Four", "nix", "", 4);
      }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();
    }
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();
    }
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        // $this->LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
    }
}
