<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/
/*
* Copyright (C) 2010 Tech To The People
* Licensed to CiviCRM under the Academic Free License version 3.0.
*
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


/**
 * This class print the name badges for the participants
 * It isn't supposed to be called directly, but is the parent class of the classes in CRM/Event/Badges/XXX.php
 * 
 */
class CRM_Event_Badge {
    
     function __construct() {
        $this->style=array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '2,2', 'color' => array(0, 0, 200));
        $this->format = '5160';
        $this->imgExtension = 'png';
        $this->imgRes = 300;
        $this->event = null;
        $this->setDebug(false); 
     }
     
     function setDebug($debug=true) {
       if (!$debug){
         $this->debug=false; 
         $this->border = 0;
       } else {
         $this->debug=true; 
         $this->border = "LTRB";
       }
     }
     /**
      * function to create the labels (pdf)
      * It assumes the participants are from the same event
      *
      * @param   array    $participants
      * @return  null      
      * @access  public
      */
    public function run ( &$participants )
    {
        $participant = reset ($participants); //fetch the 1st participant, and take her event to retrieve its attributes
        $eventID = $participant['event_id'];
        $this->event= self::retrieveEvent ($eventID);
        //call function to create labels
        self::createLabels($participants);
        CRM_Utils_System::civiExit( 1 );
    }
    
   protected function retrieveEvent($eventID) {
       $bao = new CRM_Event_BAO_Event ();
       if ($bao->get('id',$eventID)) {
          return $bao;
       }
       return null;
   }

  function getImageFileName ($eventID,$img=false) {
    global $civicrm_root;
    $path = "CRM/Event/Badge";
    if ($img == false) {
      return false;
    }
    if ($img == true)  {
      $img = get_class($this).".".$this->imgExtension ;
    }

    $config = CRM_Core_Config::singleton( );
    $imgFile = $config->customTemplateDir."/$path/$eventID/$img"; 
    if (file_exists($imgFile)) return $imgFile;
    $imgFile = $config->customTemplateDir."/$path/$img"; 
    if (file_exists($imgFile)) return $imgFile;

    $imgFile = "$civicrm_root/templates/$path/$eventID/$img"; 
    if (file_exists($imgFile)) return $imgFile;
    $imgFile = "$civicrm_root/templates/$path/$img";
    if (!file_exists($imgFile) && !$this->debug) return false;
     
    return $imgFile; // not sure it exists, but at least will display a meaniful fatal error in debug mode
  }

  function printBackground ($img=false) {
    $x = $this->pdf->GetAbsX();
    $y = $this->pdf->GetY();
    if ($this->debug) {
      $this->pdf->Rect( $x, $y, $this->pdf->width, $this->pdf->height, 'D', array ('all'=>array('width' => 1, 'cap' => 'round', 'join' => 'round', 'dash' => '2,10', 'color' => array(255, 0, 0))));
    }
    $img = $this->getImageFileName($this->event->id,$img);
    if ($img) {
     $imgsize = getimagesize($img);
     $f = $this->imgRes / 25.4;//mm
     $w= $imgsize[0] / $f;
     $h= $imgsize[1] / $f;
      $this->pdf->Image($img,  $this->pdf->GetAbsX(), $this->pdf->GetY(), $w,$h, strtoupper($this->imgExtension), '', '', false, 72, '', false, false, $this->debug, false, false, false);
    }
    $this->pdf->SetXY($x,$y);
  }

   /**
   * this is supposed to be overrided 
   **/
   public function generateLabel($participant) {
     $txt = "{$this->event['title']}
{$participant['first_name']} {$participant['last_name']}
{$participant['current_employer']}";

     $this->pdf->MultiCell ($this->pdf->width, $this->pdf->lineHeight, $txt);
   }

   function pdfExtraFormat() {
   }

     /**
      * function to create labels (pdf)
      *
      * @param   array    $contactRows   assciated array of contact data
      * @param   string   $format   format in which labels needs to be printed
      *
      * @return  null      
      * @access  public
      */
    function createLabels( &$participants )
    {
        
        $this->pdf = new CRM_Utils_PDF_Label( $this->format, 'mm' );
        $this->pdfExtraFormat();
        $this->pdf->Open();
        $this->pdf->setPrintHeader( false );
	 $this->pdf->setPrintFooter( false );
        $this->pdf->AddPage();
        $this->pdf->AddFont( 'DejaVu Sans', '', 'DejaVuSans.php' );
        $this->pdf->SetFont( 'DejaVu Sans' );
        $this->pdf->SetGenerator( $this, "generateLabel" );
       
        foreach ( $participants as $participant ) {
          $this->pdf->AddPdfLabel( $participant );
        }
        $this->pdf->Output( $this->event->title.'.pdf', 'D' );
    }
    
}
