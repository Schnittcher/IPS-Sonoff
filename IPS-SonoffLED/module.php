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

      $this->createVariabenProfiles();

      $this->RegisterVariableBoolean("SonoffLED_Fade", "Fade","Switch");
      $this->RegisterVariableInteger("SonoffLED_Speed", "Speed","SonoffLED.Speed");
      $this->RegisterVariableInteger("SonoffLED_Scheme", "Scheme","SonoffLED.Scheme");
      $this->RegisterVariableInteger("SonoffLED_Color", "Color","HexColor");
      $this->EnableAction("SonoffLED_Speed");
      $this->EnableAction("SonoffLED_Fade");
      $this->EnableAction("SonoffLED_Scheme");
      $this->EnableAction("SonoffLED_Color");
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
       if (fnmatch("*Speed*", $Buffer->MSG)) {
          $this->SendDebug("Speed Topic", $Buffer->TOPIC,0);
          $this->SendDebug("Speed MSG", $Buffer->MSG,0);
          $MSG = json_decode($Buffer->MSG);
          SetValue($this->GetIDForIdent("SonoffLED_Speed"), $MSG->Speed);
        }
      if (fnmatch("*Scheme*", $Buffer->MSG)) {
         $this->SendDebug("Scheme Topic", $Buffer->TOPIC,0);
         $this->SendDebug("Scheme MSG", $Buffer->MSG,0);
         $MSG = json_decode($Buffer->MSG);
         SetValue($this->GetIDForIdent("SonoffLED_Scheme"), $MSG->Scheme);
       }
       if (fnmatch("*Color*", $Buffer->MSG)) {
          $this->SendDebug("Color Topic", $Buffer->TOPIC,0);
          $this->SendDebug("Color MSG", $Buffer->MSG,0);
          $MSG = json_decode($Buffer->MSG);
          SetValue($this->GetIDForIdent("SonoffLED_Color"), $MSG->Color);
        }
      if (fnmatch("*Fade*", $Buffer->MSG)) {
         $this->SendDebug("Speed Topic", $Buffer->TOPIC,0);
         $this->SendDebug("Speed MSG", $Buffer->MSG,0);
         $MSG = json_decode($Buffer->MSG);
         if ($MSG->Fade == "ON") {
            SetValue($this->GetIDForIdent("SonoffLED_Fade"), true);
         } else {
            SetValue($this->GetIDForIdent("SonoffLED_Fade"), false);
         }
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
     $msg = $msg;

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

  public function setDimmer($value) {
    $command = "Dimmer";
    $msg = $value;
    $BufferJSON = $this->MQTTCommand($command,$msg);
    $this->SendDebug("setDimmer", $BufferJSON,0);
    $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
  }

  public function setColorHex($color) {
    $command = "Color";
    $msg = $color;
    $BufferJSON = $this->MQTTCommand($command,$msg);
    $this->SendDebug("setColorHex", $BufferJSON,0);
    $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
  }

  public function setFade($value) {
    $command = "Fade";
    $msg = $value;
    $BufferJSON = $this->MQTTCommand($command,$msg);
    $this->SendDebug("setFade", $BufferJSON,0);
    $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
  }

  public function setSpeed($value) {
    $command = "Speed";
    $msg = $value;
    $BufferJSON = $this->MQTTCommand($command,$msg);
    $this->SendDebug("setSpeed", $BufferJSON,0);
    $this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
  }

  public function RequestAction($Ident, $Value) {
    switch ($Ident) {
      case 'SonoffLED_Speed':
        $this->setSpeed($Value);
        break;
      case 'SonoffLED_Fade':
        $this->setFade(intval($Value));
        break;
      case 'SonoffLED_Scheme':
        $this->setScheme($Value);
        break;
      case 'SonoffLED_Color':
        $this->setColorHex("#".dechex($Value));
        break;

      default:
        # code...
        break;
    }
  }

  private function createVariabenProfiles() {
    //Speed Profile
    $this->RegisterProfileInteger("SonoffLED.Speed","Speedo","","",1,20,1);

    //Scheme Profile
    $this->RegisterProfileIntegerEx("SonoffLED.Scheme", "Shuffle", "", "", Array(
                                        Array(0, "Default",  "", -1),
                                        Array(1, "Wake up",  "", -1),
                                        Array(2, "RGB Cycle", "", -1),
                                        Array(3, "RBG Cycle", "", -1),
                                        Array(4, "Random cycle", "", -1),
                                        Array(5, "Clock", "", -1),
                                        Array(6, "Incandescent pattern", "", -1),
                                        Array(7, "RGB Pattern", "", -1),
                                        Array(8, "Christmas", "", -1),
                                        Array(9, "Hanukkah", "", -1),
                                        Array(10, "Kwanzaa", "", -1),
                                        Array(11, "Rainbow", "", -1),
                                        Array(12, "Fire", "", -1)
                                    ));


  }


  protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {

    if(!IPS_VariableProfileExists($Name)) {
        IPS_CreateVariableProfile($Name, 1);
    } else {
        $profile = IPS_GetVariableProfile($Name);
        if($profile['ProfileType'] != 1)
        throw new Exception("Variable profile type does not match for profile ".$Name);
    }

    IPS_SetVariableProfileIcon($Name, $Icon);
    IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
    IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
  }

  protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
      if ( sizeof($Associations) === 0 ){
          $MinValue = 0;
          $MaxValue = 0;
      } else {
          $MinValue = $Associations[0][0];
          $MaxValue = $Associations[sizeof($Associations)-1][0];
      }

      $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

      foreach($Associations as $Association) {
          IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
      }
  }
}
?>
