<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright (C) 2011 Marty Wright                                    |
 | Licensed to CiviCRM under the Academic Free License version 3.0.   |
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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


/**
 * This class generates form components for PDF Page Format Settings
 * 
 */
class CRM_Admin_Form_PdfFormats extends CRM_Admin_Form
{
    /**
     * PDF Page Format ID
     */
    protected $_id     = null;

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        parent::buildQuickForm( );

        if ($this->_action & CRM_Core_Action::DELETE ) { 
            $formatName = CRM_Core_BAO_PdfFormat::getFieldValue( 'CRM_Core_BAO_PdfFormat', $this->_id, 'name' );
            $this->assign('formatName', $formatName);
            return;
        }

        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Core_BAO_PdfFormat' );
        $this->add( 'text', 'name', ts( 'Name' ), $attributes['name'], true );
        $this->add( 'text', 'description', ts( 'Description' ), array( 'size' => CRM_Utils_Type::HUGE ) );
        $this->add( 'checkbox', 'is_default', ts( 'Is this PDF Page Format the default?' ) );

        $this->add( 'select', 'paper_size', ts( 'Paper Size' ), 
                     array( 0 => ts( '- default -' ) ) + CRM_Core_BAO_PaperSize::getList( true ), false,
                     array('onChange' => "selectPaper( this.value );") );

        $this->add( 'static', 'paper_dimensions', NULL, ts('Width x Height') );
        $this->add( 'select', 'orientation', ts('Orientation'), CRM_Core_BAO_PdfFormat::getPageOrientations(), false,
                     array('onChange' => "updatePaperDimensions();") );
        $this->add( 'select', 'metric', ts('Unit of Measure'), CRM_Core_BAO_PdfFormat::getUnits(), false,
                     array('onChange' => "selectMetric( this.value );") );
        $this->add( 'text', 'margin_left', ts('Left Margin'), array( 'size' => 8, 'maxlength' => 8 ), true );
        $this->add( 'text', 'margin_right', ts('Right Margin'), array( 'size' => 8, 'maxlength' => 8 ), true );
        $this->add( 'text', 'margin_top', ts('Top Margin'), array( 'size' => 8, 'maxlength' => 8 ), true );
        $this->add( 'text', 'margin_bottom', ts('Bottom Margin'), array( 'size' => 8, 'maxlength' => 8 ), true );
        $this->add( 'text', 'weight', ts('Weight'), CRM_Core_DAO::getAttribute('CRM_Core_BAO_PdfFormat', 'weight'), true );

        $this->addRule( 'name', ts('Name already exists in Database.'), 'objectExists', array( 'CRM_Core_BAO_PdfFormat', $this->_id ) );
        $this->addRule( 'margin_left', ts('Margin must be numeric') , 'numeric' );
        $this->addRule( 'margin_right', ts('Margin must be numeric') , 'numeric' );
        $this->addRule( 'margin_top', ts('Margin must be numeric') , 'numeric' );
        $this->addRule( 'margin_bottom', ts('Margin must be numeric') , 'numeric' );
        $this->addRule( 'weight', ts('Weight must be integer') , 'integer' );
    }

    function setDefaultValues( )
    {
        if ($this->_action & CRM_Core_Action::ADD) {
            $defaults['weight'] = CRM_Utils_Array::value( 'weight', CRM_Core_BAO_PdfFormat::getDefaultValues(), 0 );
        } else {
            $defaults = $this->_values;
        }
        return $defaults;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            // delete PDF Page Format
            CRM_Core_BAO_PdfFormat::del( $this->_id );
            CRM_Core_Session::setStatus( ts('Selected PDF Page Format has been deleted.') );
            return;
        }

        $values = $this->controller->exportValues( $this->getName() );
        $values['is_default'] = isset( $values['is_default'] );
        $bao = new CRM_Core_BAO_PdfFormat();
        $bao->savePdfFormat( $values, $this->_id );

        $status = ts('Your new PDF Page Format titled <strong>%1</strong> has been saved.', array(1 => $values['name']));
        if ( $this->_action & CRM_Core_Action::UPDATE ) { 
            $status = ts('Your PDF Page Format titled <strong>%1</strong> has been updated.', array(1 => $values['name']));
        }
        CRM_Core_Session::setStatus( $status );
    }

}
