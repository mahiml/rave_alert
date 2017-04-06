<?php

namespace Drupal\rave_alert;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Created by PhpStorm.
 * User: mahim
 * Date: 3/23/17
 * Time: 12:44 PM
 */
class AlertFeed
{
    protected $database;

    public function __construct(Connection $database)
    {
        $this->database = $database;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('database')
        );
    }

    public $mock_data = "Mock Data from AlertFeed";


    /*
		Display the campus alert
	*/
    public function DisplayCampusAlert()
    {
        //feed_alert, feed_description
        $this->SaveFeed();
        $arrData = $this->GetAlert();
        $messageDisplay = "";
        //echo("Display Level: " .$arrData["feed_level"] . "<br />");
        if (isset($arrData[0]->feed_level)) {
            switch ($arrData[0]->feed_level) {
                case 1:
                    //Level 1 Alert:
                    $messageDisplay = "<div id=\"rave-alert-01\">"
                        . "    <div class=\"row\">"
                        . "        <div class=\"large-12 columns\">"
                        . "        <h1><i class=\"icon-warning-sign icon-right-padding\"></i>ALERT!</h1>"
                        . "        <div class=\"rave-time\">Updated " .$arrData[0]->feed_pubdate . "</div>"
                        . "           <p class=\"rave-msg\">" . $arrData[0]->feed_description . "</p>"
                        . "        </div>"
                        . "    </div>"
                        . "</div>";
                    break;

                case 2:
                    //Level 2 Alert:
                    $messageDisplay = "<div id=\"rave-alert-02\">"
                        . "	   <div class=\"row\">"
                        . "        <div class=\"large-12 columns\">"
                        . "        <p class=\"rave-msg\"><i class=\"icon-warning-sign icon-right-padding\"></i>" . $arrData[0]->feed_description . " <span class=\"rave-time\">" . $arrData[0]->feed_pubdate . "</span></p>"
                        . "        </div>"
                        . "    </div>"
                        . "</div>";
                    break;
            }
        }
        return array(
            '#markup' => $messageDisplay
        );

    }

    /*
        retrieve data from Rave and write to database
    */
    public function SaveFeed()
    {
        $xmlFeed = "/Users/mahim/Desktop/mock_rave_data.xml";
        $myfeed = simplexml_load_file($xmlFeed);
        $description = $myfeed->channel[0]->item[0]->description;
        $pubDate = $myfeed->channel[0]->item[0]->pubDate;
        $title = $myfeed->channel[0]->item[0]->title;

        if (!$this->PubDatesMatch($pubDate)) {

            $feedLevel = $this->FeedLevel($title);
            $cleanedDescription = $this->CleanDescription($description);
            $query = "UPDATE campusalerts SET feed_description = '{$cleanedDescription}', " . "feed_pubdate = '{$pubDate}', feed_level = {$feedLevel};";
            $this->database->query($query)->execute();

        }


    }

    private function GetAlert()
    {
        $arrAlertData = array();
        if ($this->ShowFeed()) {
            $query = "SELECT feed_description, feed_level, feed_pubdate FROM campusalerts;";
            $results = $this->database->query($query)->fetchAll();
            if ($results) {
                foreach ($results as $key => $value) {
                    $arrAlertData[$key] = $value;
                }
            }

        }
        return $arrAlertData;

    }

    private function CleanDescription($description)
    {
        $dirt = array("[Central] All Clear", "Level 1:", "Level 2:");

        $cleaned = ltrim(str_replace($dirt, "", $description));
        //echo("Cleaned: {$cleaned}<br />");
        return $cleaned;

    }

    private function FeedLevel($title)
    {
        $level = 0;
        $uTitle = strtoupper($title);
        $posValOne = strpos($uTitle, "CENTRAL RSS LEVEL 1");
        $posValTwo = strpos($uTitle, "CENTRAL RSS LEVEL 2");
        //echo("<br />{$uTitle} :: 1= {$posValOne} :: 2 = {$posValTwo}<br /> ");
        if (strpos($uTitle, "CENTRAL RSS LEVEL 1") !== false) {
            $level = 1;
        } else if (strpos($uTitle, "CENTRAL RSS LEVEL 2") !== false) {
            $level = 2;
        }

        return $level;

    }

    private function ShowFeed()
    {
        $boolShowFeed = false;
        $query = "SELECT COUNT(*) AS showfeed  FROM campusalerts WHERE feed_level > 0;";
        $results = $this->database->query($query)->fetchAll();
        if ($results[0]) {
            if ($results[0]->showfeed == 1) {
                $boolShowFeed = true;
            }
        }
        return $boolShowFeed;
    }

    private function PubDatesMatch($pubdate)
    {
        $boolMatch = false;
        $query = "SELECT COUNT(*) AS 'current_alerts' FROM campusalerts WHERE feed_pubdate LIKE '{$pubdate}';";
        $results = $this->database->query($query)->fetch();
        if ($results) {
            $row = $results->current_alerts;
            if ($row[0] == 1) {
                $boolMatch = true;
            }
        }
        return $boolMatch;
    }
}