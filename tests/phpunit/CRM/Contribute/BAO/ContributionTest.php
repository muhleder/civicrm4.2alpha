<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 | at info'AT'civicrm'DOT'org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';
require_once 'CiviTest/Custom.php';

class CRM_Contribute_BAO_ContributionTest extends CiviUnitTestCase 
{
    
    function get_info( ) 
    {
        return array(
                     'name'        => 'Contribution BAOs',
                     'description' => 'Test all Contribute_BAO_Contribution methods.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    function setUp( ) 
    {
        parent::setUp();
        $this->contributionTypeCreate();
    }
    
    function teardown( ) 
    {
        $this->contributionTypeDelete();
    }
 
    /**
     * create() method (create and update modes)
     */
    function testCreate( )
    {
        $contactId = Contact::createIndividual( );
        $ids = array ('contribution' => null );

        $params = array (
                         'contact_id'             => $contactId,
                         'currency'               => 'USD',
                         'contribution_type_id'   => 1,
                         'contribution_status_id' => 1,
                         'payment_instrument_id'  => 1,
                         'source'                 => 'STUDENT',
                         'receive_date'           => '20080522000000',
                         'receipt_date'           => '20080522000000',
                         'id'                     => null,
                         'non_deductible_amount'  => 0.00,
                         'total_amount'           => 200.00,
                         'fee_amount'             => 5,
                         'net_amount'             => 195,
                         'trxn_id'                => '22ereerwww444444',
                         'invoice_id'             => '86ed39c9e9ee6ef6031621ce0eafe7eb81',
                         'thankyou_date'          => '20080522'
                         );

        require_once 'CRM/Contribute/BAO/Contribution.php';
        $contribution = CRM_Contribute_BAO_Contribution::create( $params ,$ids );
        
        $this->assertEquals( $params['trxn_id'], $contribution->trxn_id, 'Check for transcation id creation.' );
        $this->assertEquals( $contactId, $contribution->contact_id, 'Check for contact id  creation.' );
        
        //update contribution amount 
        $ids = array ('contribution' => $contribution->id );
        $params['fee_amount'] = 10;
        $params['net_amount'] = 190;
        
        $contribution = CRM_Contribute_BAO_Contribution::create( $params ,$ids );
        
        $this->assertEquals( $params['trxn_id'], $contribution->trxn_id, 'Check for transcation id .' );
        $this->assertEquals( $params['net_amount'],$contribution->net_amount, 'Check for Amount updation.' );

        //Delete Contribution
        $this->contributionDelete( $contribution->id );
        
        //Delete Contact
        Contact::delete( $contactId );
    }
    
    /**
     * create() method with custom data
     */
    function testCreateWithCustomData( )
    {
        $contactId = Contact::createIndividual( );
        $ids = array ('contribution' => null );
        
        //create custom data
        $customGroup = Custom::createGroup( array(), 'Contribution' );
        $fields = array(
                        'label'            => 'testFld',
                        'data_type'        => 'String',
                        'html_type'        => 'Text',
                        'is_active'        => 1,
                        'custom_group_id'  => $customGroup->id,
                        );
        $customField = CRM_Core_BAO_CustomField::create( $fields );
        
        $params = array (
                         'contact_id'             => $contactId,
                         'currency'               => 'USD',
                         'contribution_type_id'   => 1,
                         'contribution_status_id' => 1,
                         'payment_instrument_id'  => 1,
                         'source'                 => 'STUDENT',
                         'receive_date'           => '20080522000000',
                         'receipt_date'           => '20080522000000',
                         'id'                     => null,
                         'non_deductible_amount'  => 0.00,
                         'total_amount'           => 200.00,
                         'fee_amount'             => 5,
                         'net_amount'             => 195,
                         'trxn_id'                => '22ereerwww322323',
                         'invoice_id'             => '22ed39c9e9ee6ef6031621ce0eafe6da70',
                         'thankyou_date'          => '20080522'
                         );
        
        
        $params['custom']= array(
                                 $customField->id => array(
                                                           -1 => array(
                                                                       'value'           => 'Test custom value',
                                                                       'type'            => 'String',
                                                                       'custom_field_id' => $customField->id,
                                                                       'custom_group_id' => $customGroup->id,
                                                                       'table_name'      => $customGroup->table_name,
                                                                       'column_name'     => $customField->column_name,
                                                                       'file_id'         => null
                                                                       )
                                                           )
                                 );
        
        
        require_once 'CRM/Contribute/BAO/Contribution.php';
        $contribution = CRM_Contribute_BAO_Contribution::create( $params ,$ids );
        
        // Check that the custom field value is saved
        $customValueParams = array( 'entityID'                  => $contribution->id,
                                    'custom_'.$customField->id  => 1 
                                    );
        $values = CRM_Core_BAO_CustomValueTable::getValues( $customValueParams );
        $this->assertEquals( 'Test custom value', $values['custom_'.$customField->id], 'Check the custom field value');
        
        $this->assertEquals( $params['trxn_id'], $contribution->trxn_id, 'Check for transcation id creation.' );
        $this->assertEquals( $contactId, $contribution->contact_id, 'Check for contact id for Conribution.' );
        
        $this->contributionDelete( $contribution->id );
        Custom::deleteField( $customField ); 
        Custom::deleteGroup( $customGroup );
        Contact::delete( $contactId );
    } 
    
    /**
     * deleteContribution() method
     */

    function testDeleteContribution( )
    {
        $contactId = Contact::createIndividual( );
        $ids = array ('contribution' => null );

        $params = array (
                         'contact_id'             => $contactId,
                         'currency'               => 'USD',
                         'contribution_type_id'   => 1,
                         'contribution_status_id' => 1,
                         'payment_instrument_id'  => 1,
                         'source'                 => 'STUDENT',
                         'receive_date'           => '20080522000000',
                         'receipt_date'           => '20080522000000',
                         'id'                     => null,
                         'non_deductible_amount'  => 0.00,
                         'total_amount'           => 200.00,
                         'fee_amount'             => 5,
                         'net_amount'             => 195,
                         'trxn_id'                => '33ereerwww322323',
                         'invoice_id'             => '33ed39c9e9ee6ef6031621ce0eafe6da70',
                         'thankyou_date'          => '20080522'
                         );

        require_once 'CRM/Contribute/BAO/Contribution.php';
        $contribution = CRM_Contribute_BAO_Contribution::create( $params ,$ids );
        
        $this->assertEquals( $params['trxn_id'], $contribution->trxn_id, 'Check for transcation id creation.' );
        $this->assertEquals( $contactId, $contribution->contact_id, 'Check for contact id  creation.' );
        
        $contributiondelete = CRM_Contribute_BAO_Contribution::deleteContribution( $contribution->id );
        
        $this->assertDBNull( 'CRM_Contribute_DAO_Contribution',$contribution->trxn_id, 
                             'id','trxn_id','Database check for deleted Contribution.' );
        Contact::delete( $contactId );
    }

    /**
     * create honor-contact method 
     * createHonorContact();
     */
    
    function testcreateAndGetHonorContact( ) 
    {
        $firstName = 'John_' .substr(sha1(rand()), 0, 7);
        $lastName  = 'Smith_'.substr(sha1(rand()), 0, 7);
        $email     = "{$firstName}.{$lastName}@example.com"; 
        
        $honorId = null;
        $params  = array (
                          'honor_type_id'    => 1,
                          'honor_prefix_id'  => 3,
                          'honor_first_name' => $firstName,
                          'honor_last_name'  => $lastName,
                          'honor_email'      => $email
                          );
        require_once 'CRM/Contribute/BAO/Contribution.php';
        $contact = CRM_Contribute_BAO_Contribution::createHonorContact( $params, $honorId );
        
        $this->assertDBCompareValue( 'CRM_Contact_DAO_Contact', $contact , 'first_name', 'id', $firstName,
                                     'Database check for created honor contact record.' );
        //create contribution on behalf of honary.

        $contactId = Contact::createIndividual( );

        $ids = array ('contribution' => null );
        $param = array (
                        'contact_id'             => $contactId,
                        'currency'               => 'USD',
                        'contribution_type_id'   => 4,
                        'contribution_status_id' => 1,
                        'receive_date'           => date('Ymd'),
                        'total_amount'           => 66,
                        'honor_type_id'          => 1,
                        'honor_contact_id'       => $contact
                        );

        require_once 'CRM/Contribute/BAO/Contribution.php';
        $contribution = CRM_Contribute_BAO_Contribution::create( $param ,$ids );
        $id = $contribution->id;
        $this->assertDBCompareValue('CRM_Contribute_DAO_Contribution',  $id, 'honor_contact_id', 
                                    'id', $contact, 'Check DB for honor contact of the contribution'); 
        //get honory information
        $getHonorContact = CRM_Contribute_BAO_Contribution::getHonorContacts( $contact );

        $this->assertDBCompareValue( 'CRM_Contact_DAO_Contact', $contact , 'first_name', 'id', $firstName,
                                     'Database check for created honor contact record.' );
        
        //get annual contribution information
        $annual = CRM_Contribute_BAO_Contribution::annual( $contactId );
        require_once 'CRM/Core/DAO.php';
        
        $config = CRM_Core_Config::singleton();
        $currencySymbol = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Currency',$config->defaultCurrency,'symbol','name') ;
        $this->assertDBCompareValue('CRM_Contribute_DAO_Contribution',  $id, 'total_amount', 
                                    'id', ltrim( $annual[2], $currencySymbol ), 'Check DB for total amount of the contribution'); 
        
        //Delete honor contact
        Contact::delete( $contact );

        //Delete Contribution record
        $this->contributionDelete( $contribution->id );

        //Delete contributor contact
        Contact::delete( $contactId );
    }
    /**
     * display sort name during 
     * contribution batch update through profile 
     * sortName();
     */
    function testsortName( ) 
    {
        $params    =  array( 'first_name'   => 'Shane',     
                             'last_name'    => 'Whatson',
                             'contact_type' => 'Individual'
                             );
        
        require_once 'CRM/Contact/BAO/Contact.php';
        $contact = CRM_Contact_BAO_Contact::add( $params );
        
        //Now check $contact is object of contact DAO..
        $this->assertType( 'CRM_Contact_DAO_Contact', $contact, 'Check for created object' );
        
        $contactId = $contact->id;
       
        $ids = array ('contribution' => null );

        $param = array (
                        'contact_id'             => $contactId,
                        'currency'               => 'USD',
                        'contribution_type_id'   => 1,
                        'contribution_status_id' => 1,
                        'payment_instrument_id'  => 1,
                        'source'                 => 'STUDENT',
                        'receive_date'           => '20080522000000',
                        'receipt_date'           => '20080522000000',
                        'id'                     => null,
                        'non_deductible_amount'  => 0.00,
                        'total_amount'           => 300.00,
                        'fee_amount'             => 5,
                        'net_amount'             => 295,
                        'trxn_id'                => '22ereerwww323',
                        'invoice_id'             => '22ed39c9e9ee621ce0eafe6da70',
                        'thankyou_date'          => '20080522'
                        );

        require_once 'CRM/Contribute/BAO/Contribution.php';
        $contribution = CRM_Contribute_BAO_Contribution::create( $param ,$ids );
        
        $this->assertEquals( $param['trxn_id'], $contribution->trxn_id, 'Check for transcation id creation.' );
        $this->assertEquals( $contactId, $contribution->contact_id, 'Check for contact id  creation.' );

        //display sort name during batch update
        $sortName = CRM_Contribute_BAO_Contribution::sortName( $contribution->id );

        $this->assertEquals( 'Whatson, Shane', $sortName, 'Check for sort name.' );
        
        //Delete Contribution
        $this->contributionDelete( $contribution->id );
        //Delete Contact
        Contact::delete( $contactId );
    }
    /**
     * Add premium during online Contribution
     * 
     * AddPremium();
     */
    function testAddPremium( ) 
    {
        $contactId = Contact::createIndividual( );

        $ids = array(
                     'premium' => null
                     );


        $params = array(
                        'name'             => 'TEST Premium',
                        'sku'              => 111,
                        'imageOption'      => 'noImage',
                        'MAX_FILE_SIZE'    => 2097152,
                        'price'            => 100.00,
                        'cost'             => 90.00,
                        'min_contribution' => 100,
                        'is_active'        => 1
                        );
        require_once 'CRM/Contribute/BAO/ManagePremiums.php';
       $premium = CRM_Contribute_BAO_ManagePremiums::add( $params,$ids );

       $this->assertEquals( 'TEST Premium', $premium->name, 'Check for premium  name.' );

       $ids = array ('contribution' => null );

       $param = array (
                       'contact_id'             => $contactId,
                       'currency'               => 'USD',
                       'contribution_type_id'   => 1,
                       'contribution_status_id' => 1,
                       'payment_instrument_id'  => 1,
                       'source'                 => 'STUDENT',
                       'receive_date'           => '20080522000000',
                       'receipt_date'           => '20080522000000',
                       'id'                     => null,
                       'non_deductible_amount'  => 0.00,
                       'total_amount'           => 300.00,
                       'fee_amount'             => 5,
                       'net_amount'             => 295,
                       'trxn_id'                => '33erdfrwvw434',
                       'invoice_id'             => '98ed34f7u9hh672ce0eafe8fb92',
                       'thankyou_date'          => '20080522'
                       );

       require_once 'CRM/Contribute/BAO/Contribution.php';
       $contribution = CRM_Contribute_BAO_Contribution::create( $param ,$ids );

       $this->assertEquals( $param['trxn_id'], $contribution->trxn_id, 'Check for transcation id creation.' );
       $this->assertEquals( $contactId, $contribution->contact_id, 'Check for contact id  creation.' );

       //parameter for adding premium to contribution
       $data = array(
                     'product_id'      => $premium->id,
                     'contribution_id' => $contribution->id,
                     'product_option'  => null,
                     'quantity'        => 1,
                     );
       $contributionProduct = CRM_Contribute_BAO_Contribution::addPremium( $data );
       $this->assertEquals( $contributionProduct->product_id, $premium->id, 'Check for Product id .' );

       //Delete Product
       CRM_Contribute_BAO_ManagePremiums::del( $premium->id);
       $this->assertDBNull( 'CRM_Contribute_DAO_Product',$premium->name, 
                             'id','name','Database check for deleted Product.' );

       //Delete Contribution
       $this->contributionDelete( $contribution->id );
       //Delete Contact
       Contact::delete( $contactId );
    }

    /**
     * Check duplicate contribution id 
     * during the contribution import
     * checkDuplicateIds();
     */
    function testcheckDuplicateIds( )
    {
        $contactId = Contact::createIndividual( );

        $ids = array ('contribution' => null );

        $param = array (
                        'contact_id'             => $contactId,
                        'currency'               => 'USD',
                        'contribution_type_id'   => 1,
                        'contribution_status_id' => 1,
                        'payment_instrument_id'  => 1,
                        'source'                 => 'STUDENT',
                        'receive_date'           => '20080522000000',
                        'receipt_date'           => '20080522000000',
                        'id'                     => null,
                        'non_deductible_amount'  => 0.00,
                        'total_amount'           => 300.00,
                        'fee_amount'             => 5,
                        'net_amount'             => 295,
                        'trxn_id'                => '76ereeswww835',
                        'invoice_id'             => '93ed39a9e9hd621bs0eafe3da82',
                        'thankyou_date'          => '20080522'
                       );

        require_once 'CRM/Contribute/BAO/Contribution.php';
        $contribution = CRM_Contribute_BAO_Contribution::create( $param ,$ids );
        
        $this->assertEquals( $param['trxn_id'], $contribution->trxn_id, 'Check for transcation id creation.' );
        $this->assertEquals( $contactId, $contribution->contact_id, 'Check for contact id  creation.' );
        $data = array(
                      'id'         => $contribution->id,
                      'trxn_id'    => $contribution->trxn_id,
                      'invoice_id' => $contribution->invoice_id
                      );
        $contributionID = CRM_Contribute_BAO_Contribution::checkDuplicateIds( $data );
        $this->assertEquals( $contributionID, $contribution->id, 'Check for duplicate transcation id .' );
        
        // Delete Contribution
        $this->contributionDelete( $contribution->id );
        // Delete Contact
        Contact::delete( $contactId );
    }
}
?>
