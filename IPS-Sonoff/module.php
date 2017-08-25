<?
class IPS_Sonoff extends IPSModule {

  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{EE0D345A-CF31-428A-A613-33CE98E752DD}");
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyString("Topic","");
      $this->RegisterPropertyString("On","1");
      $this->RegisterPropertyString("Off","0");
      $this->RegisterPropertyString("FullTopic","%prefix%/%topic%");

      $variablenID = $this->RegisterVariableFloat("SonoffRSSI", "RSSI");

	  //Debug Optionen
	  $this->RegisterPropertyBoolean("Sensoren", false);
	  $this->RegisterPropertyBoolean("State", false);
	  $this->RegisterPropertyBoolean("Pow", false);
      //$this->EnableAction("SonoffStatus");

  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{EE0D345A-CF31-428A-A613-33CE98E752DD}");
      //Setze Filter fÃ¼r ReceiveData
      $topic = $this->ReadPropertyString("Topic");
      $this->SetReceiveDataFilter(".*".$topic.".*");
    }

	private function find_parent($array, $needle, $parent = null) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $pass = $parent;
            if (is_string($key)) {
                $pass = $key;
            }
            $found = $this->find_parent($value, $needle, $pass);
            if ($found !== false) {
                return $found;
            }
        } else if ($value === $needle) {
            return $parent;
        }
    }

    return false;
}

    private function traverseArray($array, $GesamtArray)
    {
      foreach($array as $key=>$value)
    	{
    		if(is_array($value)) {
				$this->traverseArray($value, $GesamtArray);
    		}else{
				$ParentKey = $this->find_parent($GesamtArray,$value);
				$this->Debug("Rekursion Sonoff ".$ParentKey."_".$key,"$key = $value","Sensoren");
				//$this->SendDebug("Rekursion Sonoff ".$ParentKey."_".$key, "$key beinhaltet $value",0);
				if (is_int($value) or is_float($value)){
				  switch ($key) {
					  case 'Temperature':
						$variablenID = $this->RegisterVariableFloat("Sonoff_".$ParentKey."_".$key, $ParentKey." Temperatur","~Temperature");
						SetValue($this->GetIDForIdent("Sonoff_".$ParentKey."_".$key), $value);
						break;
					  case 'Humidity':
						$variablenID = $this->RegisterVariableFloat("Sonoff_".$ParentKey."_".$key, $ParentKey." Feuchte","~Humidity.F");
						SetValue($this->GetIDForIdent("Sonoff_".$ParentKey."_".$key), $value);
						break;
            default:
     				$variablenID = $this->RegisterVariableFloat("Sonoff_".$ParentKey."_".$key, $ParentKey." ".$key);
     				SetValue($this->GetIDForIdent("Sonoff_".$ParentKey."_".$key), $value);
					}

				}
			}
		}
	}

    public function ReceiveData($JSONString) {
		if (!empty($this->ReadPropertyString("Topic"))) {
			  $this->SendDebug("ReceiveData JSON", $JSONString,0);
			  $data = json_decode($JSONString);
			  $off = $this->ReadPropertyString("Off");
			  $on = $this->ReadPropertyString("On");

			  // Buffer decodieren und in eine Variable schreiben
			  $Buffer = utf8_decode($data->Buffer);
			  // Und Diese dann wieder dekodieren
			  IPS_LogMessage("SonoffSwitch",$data->Buffer);
			  $Buffer = json_decode($data->Buffer);

			//Power Vairablen checken
			if (fnmatch("*POWER*", $Buffer->TOPIC)) {
				$this->SendDebug("Power Topic",$Buffer->TOPIC,0);
				$this->SendDebug("Power", $Buffer->MSG,0);

				$power = explode("/", $Buffer->TOPIC);
				end($power);
				$lastKey = key($power);
				//$this->SendDebug("Power", "Sonoff_".$power[$lastKey],0);
				if ($power[$lastKey] <> "POWER1") {
					$this->RegisterVariableBoolean("Sonoff_".$power[$lastKey], $power[$lastKey],"~Switch");
					$this->EnableAction("Sonoff_".$power[$lastKey]);
				  switch ($Buffer->MSG) {
					case $off:
					  SetValue($this->GetIDForIdent("Sonoff_".$power[$lastKey]), 0);
					  break;
					case $on:
					  SetValue($this->GetIDForIdent("Sonoff_".$power[$lastKey]), 1);
					  break;
				  }
				}
			}
			//State checken
			if (fnmatch("*STATE", $Buffer->TOPIC)) {
				$myBuffer = json_decode($Buffer->MSG);
				$this->Debug("State MSG", $Buffer->MSG,"State");
				$this->Debug("State RSSI", $myBuffer->Wifi->RSSI,"State");
				SetValue($this->GetIDForIdent("SonoffRSSI"), $myBuffer->Wifi->RSSI);
			}
			//Sensor Variablen checken
			if (fnmatch("*SENSOR", $Buffer->TOPIC)) {
			  $this->Debug("Sensor MSG", $Buffer->MSG,"Sensoren");
			  $myBuffer = json_decode($Buffer->MSG,true);
			  $this->traverseArray($myBuffer, $myBuffer);
			}
			  $this->Debug("Sensor Topic", $Buffer->TOPIC,"Sensoren");

			//POW Variablen
			//{"DataID":"{018EF6B5-AB94-40C6-AA53-46943E824ACF}","Buffer":"{\"TOPIC\":\"tele\\/sonoff50\\/ENERGY\",\"MSG\":\"{\\\"Time\\\":\\\"2017-08-17T15:41:00\\\", \\\"Total\\\":74.969, \\\"Yesterday\\\":1.895, \\\"Today\\\":1.529, \\\"Period\\\":0, \\\"Power\\\":325, \\\"Factor\\\":1.00, \\\"Voltage\\\":228, \\\"Current\\\":1.418}\",\"SENDER\":\"MQTT_GET_PAYLOAD\"}"}
			if (fnmatch("*ENERGY", $Buffer->TOPIC)) {
				$myBuffer = json_decode($Buffer->MSG);
				$this->Debug("ENERGY MSG", $Buffer->MSG,"Pow");
				$this->RegisterVariableFloat("Sonoff_POWTotal", "Total", "~Electricity");
				$this->RegisterVariableFloat("Sonoff_POWYesterday", "Yesterday", "~Electricity");
				$this->RegisterVariableFloat("Sonoff_POWToday", "Today", "~Electricity");
				$this->RegisterVariableFloat("Sonoff_POWPower", "Power", "~Watt.3680");
				$this->RegisterVariableFloat("Sonoff_POWFactor", "Factor");
				$this->RegisterVariableFloat("Sonoff_POWVoltage", "Voltage", "~Volt");
				$this->RegisterVariableFloat("Sonoff_POWCurrent", "Current", "~Ampere");

				SetValue($this->GetIDForIdent("Sonoff_POWPower"), $myBuffer->Power);
				SetValue($this->GetIDForIdent("Sonoff_POWTotal"), $myBuffer->Total);
				SetValue($this->GetIDForIdent("Sonoff_POWToday"), $myBuffer->Today);
				SetValue($this->GetIDForIdent("Sonoff_POWYesterday"), $myBuffer->Yesterday);
				SetValue($this->GetIDForIdent("Sonoff_POWCurrent"), $myBuffer->Current);
				SetValue($this->GetIDForIdent("Sonoff_POWVoltage"), $myBuffer->Voltage);
				SetValue($this->GetIDForIdent("Sonoff_POWFactor"), $myBuffer->Factor);
			}
		  }
  }
	private function Debug($Meldungsname, $Daten, $Category) {
		if ($this->ReadPropertyBoolean($Category) == true) {
			$this->SendDebug($Meldungsname, $Daten,0);
		}
	}

  public function setPower(int $variableID ,string $Value) {
	$ident = IPS_GetObject($variableID)["ObjectIdent"];

	$power = explode("_", $ident);
	end($power);
	$powerTopic = $power[key($power)];

  SetValue($variableID, $Value);

  $FullTopic = explode("/",$this->ReadPropertyString("FullTopic"));
  $PrefixIndex = array_search("%prefix%",$FullTopic);
  $TopicIndex = array_search("%topic%",$FullTopic);

  $SetCommandArr = $FullTopic;
  $index = count($SetCommandArr);

  $SetCommandArr[$PrefixIndex] = "cmnd";
  $SetCommandArr[$TopicIndex] = $this->ReadPropertyString("Topic");
  $SetCommandArr[$index] = $powerTopic;

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
      //switch ($Ident) {
        //case "SonoffStatus":
		$this->SendDebug("RequestAction Ident", $Ident,0);
		$this->SendDebug("RequestAction Value", $Value,0);
          $result = $this->setPower($Ident, $Value);
          //break;
        //default:
          //throw new Exception("Invalid ident");
      //}
    }

	public function restart() {
		$FullTopic = explode("/",$this->ReadPropertyString("FullTopic"));
		$PrefixIndex = array_search("%prefix%",$FullTopic);
		$TopicIndex = array_search("%topic%",$FullTopic);

		$SetCommandArr = $FullTopic;
		$index = count($SetCommandArr);


		$SetCommandArr[$PrefixIndex] = "cmnd";
		$SetCommandArr[$TopicIndex] = $this->ReadPropertyString("Topic");
		$SetCommandArr[$index] = "restart";

		$topic = implode("/",$SetCommandArr);


		$Buffer["Topic"] = $topic;
		$Buffer["MSG"] = 1;

		$BufferJSON = json_encode($Buffer);
		$this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Action" => "Publish", "Buffer" => $BufferJSON)));
	}
}
?>
