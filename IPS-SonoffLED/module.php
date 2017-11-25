<?
class IPS_SonoffLED extends IPSModule {

  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{EE0D345A-CF31-428A-A613-33CE98E752DD}");
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyString("Topic","");
      $this->RegisterPropertyString("FullTopic","%prefix%/%topic%");
      $variablenID = $this->RegisterVariableFloat("SonoffRSSI", "RSSI");
      $this->RegisterVariableInteger("SonoffLED_Pixels", "Pixels");
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
      if (!empty($this->ReadPropertyString("Topic"))) {
        $this->SendDebug("ReceiveData JSON", $JSONString,0);
        $data = json_decode($JSONString);

        // Buffer decodieren und in eine Variable schreiben
        $Buffer = utf8_decode($data->Buffer);
        $Buffer = json_decode($data->Buffer);

        if (fnmatch("*Pixels*", $Buffer->MSG)) {
  		   $this->SendDebug("Pixels Topic", $Buffer->TOPIC,0);
  		   $this->SendDebug("Pixels MSG", $Buffer->MSG,0);
         $MSG = json_decode($Buffer->MSG);
         SetValue($this->GetIDForIdent("SonoffLED_Pixels"), $MSG->Pixels);
       }
       if (fnmatch("*STATE", $Buffer->TOPIC)) {
 				$myBuffer = json_decode($Buffer->MSG);
 				SetValue($this->GetIDForIdent("SonoffRSSI"), $myBuffer->Wifi->RSSI);
 			}
     }
   }

   private function MQTTCommand($command, $msg) {
     $FullTopic = explode("/",$this->ReadPropertyString("FullTopic"));
     $PrefixIndex = array_search("%prefix%",$FullTopic);
     $TopicIndex = array_search("%topic%",$FullTopic);

     $SetCommandArr = $FullTopic;
     $index = count($SetCommandArr);

     $SetCommandArr[$PrefixIndex] = "cmnd";
     $SetCommandArr[$TopicIndex] = $this->ReadPropertyString("Topic");
     $SetCommandArr[$index] = $command;

     $topic = implode("/",$SetCommandArr);
     $msg = $color;

     $Buffer["Topic"] = $topic;
     $Buffer["MSG"] = $msg;
     $BufferJSON = json_encode($Buffer);

     return $BufferJSON;
   }

   public function setLED($LED, $color) {
    $command = "LED".$LED;
    $msg = $color;
  	$BufferJSON = $this->MQTTCommand($command,$color);
  	$this->SendDebug("setLED", $BufferJSON,0);
    $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
  }

  public function setScheme($schemeID) {
    $command = "Scheme";
    $msg = $schemeID;
    $BufferJSON = $this->MQTTCommand($command,$msg);
    $this->SendDebug("setScheme", $BufferJSON,0);
    $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));  }

  public function setPixel($count) {
    $command = "Pixels";
    $msg = $count;
    $BufferJSON = $this->MQTTCommand($command,$msg);
    $this->SendDebug("setPixel", $BufferJSON,0);
    $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
  }
}
?>
