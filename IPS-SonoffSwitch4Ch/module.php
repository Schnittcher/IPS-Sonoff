<?
class IPS_SonoffSwitch4Ch extends IPSModule {

  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{EE0D345A-CF31-428A-A613-33CE98E752DD}");
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyString("Topic","");
      $this->RegisterPropertyString("On","1");
      $this->RegisterPropertyString("Off","0");
      $this->RegisterPropertyString("FullTopic","%prefix%/%topic%");

      $variablenID = $this->RegisterVariableBoolean("SonoffStatus", "Status","~Switch");
      $this->EnableAction("SonoffStatus");

  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{EE0D345A-CF31-428A-A613-33CE98E752DD}");
      //Setze Filter fÃ¼r ReceiveData
      $topic = $this->ReadPropertyString("Topic");
      $this->SetReceiveDataFilter(".*".$topic.".*");
    }

    public function ReceiveData($JSONString) {
      $this->SendDebug("JSON", $JSONString,0);
      $data = json_decode($JSONString);
      $off = $this->ReadPropertyString("Off");
      $on = $this->ReadPropertyString("On");

      // Buffer decodieren und in eine Variable schreiben
      $Buffer = utf8_decode($data->Buffer);
      // Und Diese dann wieder dekodieren
      IPS_LogMessage("SonoffSwitch",$data->Buffer);
	  $Buffer = json_decode($data->Buffer);
	  if (fnmatch("*POWER", $Buffer->TOPIC)) {
		  $this->SendDebug("Power", $Buffer->MSG,0);
      switch ($Buffer->MSG) {
        case $off:
          SetValue($this->GetIDForIdent("SonoffStatus"), 0);
          break;
        case $on:
          SetValue($this->GetIDForIdent("SonoffStatus"), 1);
          break;
      }
	  }
      $this->SendDebug("Buffer", $Buffer->TOPIC,0);
    }

  public function setStatus($Value) {
  SetValue($this->GetIDForIdent("SonoffStatus"), $Value);

  $FullTopic = explode("/",$this->ReadPropertyString("FullTopic"));
  $PrefixIndex = array_search("%prefix%",$FullTopic);
  $TopicIndex = array_search("%topic%",$FullTopic);

  $SetCommandArr = $FullTopic;
  $index = count($SetCommandArr);

  $SetCommandArr[$PrefixIndex] = "cmnd";
  $SetCommandArr[$TopicIndex] = $this->ReadPropertyString("Topic");
  $SetCommandArr[$index] = "power";

  $topic = implode("/",$SetCommandArr);
	$msg = $Value;

	if($msg===false){$msg = 'false';}
	elseif($msg===true){$msg = 'true';}

	$Buffer["Topic"] = $topic;
	$Buffer["MSG"] = $msg;
	$BufferJSON = json_encode($Buffer);
	//MQTT_Publish(33877 /*[MQTT Client]*/, $topic,$msg,0,0);
	$this->SendDebug("setStatus", $BufferJSON,0);
  $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
}

    public function RequestAction($Ident, $Value) {
      switch ($Ident) {
        case "SonoffStatus":
          $result = $this->setStatus($Value);
          break;
        default:
          throw new Exception("Invalid ident");
      }
    }
}
?>
