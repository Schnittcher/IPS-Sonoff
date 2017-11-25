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

  }

  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{EE0D345A-CF31-428A-A613-33CE98E752DD}");
      //Setze Filter fÃ¼r ReceiveData
      $topic = $this->ReadPropertyString("Topic");
      $this->SetReceiveDataFilter(".*".$topic.".*");
    }

    public function setLED($LED, $color) {

    $FullTopic = explode("/",$this->ReadPropertyString("FullTopic"));
    $PrefixIndex = array_search("%prefix%",$FullTopic);
    $TopicIndex = array_search("%topic%",$FullTopic);

    $SetCommandArr = $FullTopic;
    $index = count($SetCommandArr);

    $SetCommandArr[$PrefixIndex] = "cmnd";
    $SetCommandArr[$TopicIndex] = $this->ReadPropertyString("Topic");
    $SetCommandArr[$index] = "LED".$LED;

    $topic = implode("/",$SetCommandArr);
  	$msg = $color;

  	$Buffer["Topic"] = $topic;
  	$Buffer["MSG"] = $msg;
  	$BufferJSON = json_encode($Buffer);
  	//MQTT_Publish(33877 /*[MQTT Client]*/, $topic,$msg,0,0);
  	$this->SendDebug("setLED", $BufferJSON,0);
    $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
  }

    public function setScheme($schemeID) {
      $FullTopic = explode("/",$this->ReadPropertyString("FullTopic"));
      $PrefixIndex = array_search("%prefix%",$FullTopic);
      $TopicIndex = array_search("%topic%",$FullTopic);

      $SetCommandArr = $FullTopic;
      $index = count($SetCommandArr);

      $SetCommandArr[$PrefixIndex] = "cmnd";
      $SetCommandArr[$TopicIndex] = $this->ReadPropertyString("Topic");
      $SetCommandArr[$index] = "Scheme";

      $topic = implode("/",$SetCommandArr);
      $msg = $schemeID;

      $Buffer["Topic"] = $topic;
      $Buffer["MSG"] = $msg;
      $BufferJSON = json_encode($Buffer);

      $this->SendDebug("setScheme", $BufferJSON,0);
      $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));

    }

    public function setPixel($count) {

    $FullTopic = explode("/",$this->ReadPropertyString("FullTopic"));
    $PrefixIndex = array_search("%prefix%",$FullTopic);
    $TopicIndex = array_search("%topic%",$FullTopic);

    $SetCommandArr = $FullTopic;
    $index = count($SetCommandArr);

    $SetCommandArr[$PrefixIndex] = "cmnd";
    $SetCommandArr[$TopicIndex] = $this->ReadPropertyString("Topic");
    $SetCommandArr[$index] = "Pixels";

    $topic = implode("/",$SetCommandArr);
    $msg = $count;

    $Buffer["Topic"] = $topic;
    $Buffer["MSG"] = $msg;
    $BufferJSON = json_encode($Buffer);

    $this->SendDebug("setPixel", $BufferJSON,0);
    $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
    }



}

?>
