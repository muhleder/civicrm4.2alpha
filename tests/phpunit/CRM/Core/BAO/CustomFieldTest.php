<?php

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';
require_once 'CiviTest/Custom.php';

class CRM_Core_BAO_CustomFieldTest extends CiviUnitTestCase 
{
      function get_info( ) 
    {
        return array(
                     'name'        => 'Custom Field BAOs',
                     'description' => 'Test all Core_BAO_CustomField methods.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }

    function setUp()
    {
        parent::setUp();
    }    
   
    function testCreateCustomfield()
    {
        $customGroup = Custom::createGroup( array(), 'Individual' );
        $fields = array(
                        'label'            => 'testFld',
                        'data_type'        => 'String',
                        'html_type'        => 'Text',
                        'custom_group_id'  => $customGroup->id,
                        );
        $customField = CRM_Core_BAO_CustomField::create( $fields );
        $customFieldID = $this->assertDBNotNull( 'CRM_Core_DAO_CustomField',  $customGroup->id , 'id','custom_group_id' ,
                                           'Database check for created CustomField.' );
        $fields = array(
                        'id'               => $customFieldID,
                        'label'            => 'editTestFld',
                        'is_active'        => 1,
                        'data_type'        => 'String',
                        'html_type'        => 'Text',
                        'custom_group_id'  => $customGroup->id,
                        );
           
        $customField = CRM_Core_BAO_CustomField::create( $fields );
        $this->assertDBNotNull( 'CRM_Core_DAO_CustomField',1 , 'id','is_active' ,'Database check for edited CustomField.' );
        $this->assertDBNotNull( 'CRM_Core_DAO_CustomField' ,$fields['label'], 'id','label' ,'Database check for edited CustomField.' );
        
        Custom::deleteGroup( $customGroup );
    }
    
    function testGetFields()
    {
        $customGroup = Custom::createGroup( array(), 'Individual' );
        $fields = array(
                        'label'            => 'testFld1',
                        'data_type'        => 'String',
                        'html_type'        => 'Text',
                        'is_active'        => 1,
                        'custom_group_id'  => $customGroup->id,
                        );
        $customField1 = CRM_Core_BAO_CustomField::create( $fields );
        $customFieldID1 = $this->assertDBNotNull( 'CRM_Core_DAO_CustomField',  $customGroup->id , 'id','custom_group_id' ,
                                           'Database check for created CustomField.' );
        $fields = array(
                        'label'            => 'testFld2',
                        'data_type'        => 'String',
                        'html_type'        => 'Text',
                        'is_active'        => 1,
                        'custom_group_id'  => $customGroup->id,
                        );
        $customField2 = CRM_Core_BAO_CustomField::create( $fields );
        $customFieldID2 = $this->assertDBNotNull( 'CRM_Core_DAO_CustomField',  $customGroup->id , 'id','custom_group_id' ,
                                           'Database check for created CustomField.' );
        $getCustomFields=array();
        $getCustomFields = CRM_Core_BAO_CustomField::getFields('Individual', true, true);
        //CRM_Core_Error::debug('fdf',$getCustomFields);
        //$this->assertEquals( 'testFld1',  $getCustomFields[$customFieldID1][0], 'Confirm First Custom field label' );
        //$this->assertEquals( 'testFld2',  $getCustomFields[$customFieldID2][0], 'Confirm Second Custom field label' );
        
       
        Custom::deleteGroup( $customGroup );
    }
    
    function testGetDisplayedValues()
    {
        $customGroup = Custom::createGroup( array(), 'Individual' );
        $fields = array(
                        'label'            => 'testCountryFld1',
                        'data_type'        => 'Country',
                        'html_type'        => 'Select Country',
                        'is_active'        => 1,
                        'default_value'    => 1228,
                        'custom_group_id'  => $customGroup->id,
                        );
        $customField1 = CRM_Core_BAO_CustomField::create( $fields );
        $customFieldID1 = $this->assertDBNotNull( 'CRM_Core_DAO_CustomField',  $customGroup->id , 'id','custom_group_id' ,
                                           'Database check for created CustomField.' );
        $options=array();
        $options[$customFieldID1]['attributes']=  array(
                        'label'            => 'testCountryFld1',
                        'data_type'        => 'Country',
                        'html_type'        => 'Select Country',
                        );
        $display = CRM_Core_BAO_CustomField::getDisplayValue($fields['default_value'], $customFieldID1,$options );
       
        $this->assertEquals( 'United States',  $display, 'Confirm Country display Name' );
              
        Custom::deleteGroup( $customGroup );
    }
    function testDeleteCustomfield()
    {
        $customGroup = Custom::createGroup( array(), 'Individual' );
        $fields      = array (
                              'groupId'  => $customGroup->id,
                              'dataType' => 'Memo',
                              'htmlType' => 'TextArea'
                              );
                              
        $customField = Custom::createField( array(), $fields );
        $this->assertNotNull( $customField );
        CRM_Core_BAO_CustomField::deleteField($customField );
        $this->assertDBNull( 'CRM_Core_DAO_CustomField', $customGroup->id, 'id', 
                                             'custom_group_id', 'Database check for deleted Custom Field.' );
        Custom::deleteGroup( $customGroup );
    }
    
    /**
     * Move a custom field from $groupA to $groupB. Make sure that data records are
     * correctly matched and created.
     */
    function testMoveField() {
        $countriesByName = array_flip(CRM_Core_PseudoConstant::country(FALSE,FALSE));
        $this->assertTrue($countriesByName['Andorra'] > 0);
        $groups = array(
            'A' => Custom::createGroup(array(
                           'title'       => 'Test_Group A',
                           'name'        => 'test_group_a',
                           'extends'     => array('Individual'),
                           'style'       => 'Inline',
                           'is_multiple' => 0,
                           'is_active'   => 1,
                           'version'     => 3
            )),
            'B' => Custom::createGroup(array(
                           'title'       => 'Test_Group B',
                           'name'        => 'test_group_b',
                           'extends'     => array('Individual'),
                           'style'       => 'Inline',
                           'is_multiple' => 0,
                           'is_active'   => 1,
                           'version'     => 3
            )),
        );
        $fields = array(
            'countryA' => Custom::createField(array(), array(
                'groupId' => $groups['A']->id,
                'label' => 'Country A',
                'dataType' => 'country',
                'htmlType' => 'Select Country',
            )),
            'countryB' => Custom::createField(array(), array(
                'groupId' => $groups['A']->id,
                'label' => 'Country B',
                'dataType' => 'country',
                'htmlType' => 'Select Country',
            )),
            'countryC' => Custom::createField(array(), array(
                'groupId' => $groups['B']->id,
                'label' => 'Country C',
                'dataType' => 'country',
                'htmlType' => 'Select Country',
            )),
        );
        $contacts = array(
            'alice' => Contact::createIndividual(array(
                'first_name' => 'Alice',
                'last_name' => 'Albertson',
                'custom_' . $fields['countryA']->id => $countriesByName['Andorra'],
                'custom_' . $fields['countryB']->id => $countriesByName['Barbados'],
            )),
            'bob' => Contact::createIndividual(array(
                'first_name' => 'Bob',
                'last_name' => 'Roberts',
                'custom_' . $fields['countryA']->id => $countriesByName['Austria'],
                'custom_' . $fields['countryB']->id => $countriesByName['Bermuda'],
                'custom_' . $fields['countryC']->id => $countriesByName['Chad'],
            )),
            'carol' => Contact::createIndividual(array(
                'first_name' => 'Carol',
                'last_name' => 'Carolson',
                'custom_' . $fields['countryC']->id => $countriesByName['Cambodia'],
            )),
        );
        
        // Move!
        CRM_Core_BAO_CustomField::moveField($fields['countryB']->id, $groups['B']->id);
        
        // Group[A] no longer has fields[countryB]
        try {
            $this->assertDBQuery(1, "SELECT {$fields['countryB']->column_name} FROM {$groups['A']->table_name}");
            $this->fail('Expected exception when querying column on wrong table');
        } catch (PEAR_Exception $e) {
        }
        
        // Alice: Group[B] has fields[countryB], but fields[countryC] did not exist before
        $this->assertDBQuery(1, 
            "SELECT count(*) FROM {$groups['B']->table_name} 
            WHERE entity_id = %1 
            AND {$fields['countryB']->column_name} = %3
            AND {$fields['countryC']->column_name} is null",
            array(
                1 => array($contacts['alice'], 'Integer'),
                3 => array($countriesByName['Barbados'], 'Integer'),
            ));

        // Bob: Group[B] has merged fields[countryB] and fields[countryC] on the same record
        $this->assertDBQuery(1, 
            "SELECT count(*) FROM {$groups['B']->table_name} 
            WHERE entity_id = %1 
            AND {$fields['countryB']->column_name} = %3
            AND {$fields['countryC']->column_name} = %4",
            array(
                1 => array($contacts['bob'], 'Integer'),
                3 => array($countriesByName['Bermuda'], 'Integer'),
                4 => array($countriesByName['Chad'], 'Integer'),
            ));
            
        // Carol: Group[B] still has fields[countryC] but did not get fields[countryB]
        $this->assertDBQuery(1, 
            "SELECT count(*) FROM {$groups['B']->table_name} 
            WHERE entity_id = %1 
            AND {$fields['countryB']->column_name} is null
            AND {$fields['countryC']->column_name} = %4",
            array(
                1 => array($contacts['carol'], 'Integer'),
                4 => array($countriesByName['Cambodia'], 'Integer'),
            ));

        Custom::deleteGroup($groups['A']);
        Custom::deleteGroup($groups['B']);
    }
    
}
?>