<?
class IPS_SonoffSwitch extends IPSModule {

  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{D806E782-7A08-4BB5-BA8C-1F20A40C1C9D}");
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyString("Topic","");
      //99 Geräte können pro Konfirgurationsform angelegt werden
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{D806E782-7A08-4BB5-BA8C-1F20A40C1C9D}");
      //Setze Filter für ReceiveData
      $topic = $this->ReadPropertyString("Topic");
      $this->SetReceiveDataFilter(".*".$topic.".*");
    }
    public function ReceiveData($JSONString) {
      $this->SendDebug("JSON", $JSONString,0);
      $data = json_decode($JSONString);

      // Buffer decodieren und in eine Variable schreiben
      $Buffer = utf8_decode($data->Buffer);
      // Und Diese dann wieder dekodieren
      IPS_LogMessage("SonoffSwitch",$data->Buffer->POWER);
    }

  public function Destroy() {
  }
}
?>
