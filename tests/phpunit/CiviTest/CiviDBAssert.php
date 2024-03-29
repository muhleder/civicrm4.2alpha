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

class CiviDBAssert {

    /** 
    * Generic function to compare expected values after an api call to retrieved
    * DB values.
    * 
    * @daoName  string   DAO Name of object we're evaluating.
    * @id       int      Id of object
    * @match    array    Associative array of field name => expected value. Empty if asserting 
    *                      that a DELETE occurred
    * @delete   boolean  True if we're checking that a DELETE action occurred.
    */
    function assertDBState( &$testCase, $daoName, $id, $match, $delete = false ) {
        if ( empty( $id ) ) {
            // adding this here since developers forget to check for an id
            // and hence we get the first value in the db
            $testCase->fail( 'ID not populated. Please fix your assertDBState usage!!!' );
        }
        
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $daoName) . ".php");
        eval( '$object   = new ' . $daoName . '( );' );
        $object->id =  $id;
        $verifiedCount = 0;
        
        // If we're asserting successful record deletion, make sure object is NOT found.
        if ( $delete ) {
            if ( $object->find( true ) ) {
                $testCase->fail("Object not deleted by delete operation: $daoName, $id");
            }
            return;
        }

        // Otherwise check matches of DAO field values against expected values in $match.
        if ( $object->find( true ) ) {
            $fields =& $object->fields( );
            foreach ( $fields as $name => $value ) {
                  $dbName = $value['name'];
                  if ( isset( $match[$name] ) ) {
                    $verifiedCount++;
                    $testCase->assertEquals( $object->$dbName, $match[$name] );
                  } 
                  else if ( isset( $match[$dbName] ) ) {
                    $verifiedCount++;
                    $testCase->assertEquals( $object->$dbName, $match[$dbName] );
                  }
            }
        } else {
            $testCase->fail("Could not retrieve object: $daoName, $id");
        }
        $object->free( );
        $matchSize = count( $match );
        if ( $verifiedCount != $matchSize ) {
            $testCase->fail("Did not verify all fields in match array: $daoName, $id. Verified count = $verifiedCount. Match array size = $matchSize");
        }
    }

    // Request a record from the DB by seachColumn+searchValue. Success if a record is found. 
    function assertDBNotNull( &$testCase, $daoName, $searchValue, $returnColumn, $searchColumn, $message  ) 
    {
        if(empty($searchValue)){
           $testCase->fail("empty value passed to assertDBNotNull");
        }
        $value = CRM_Core_DAO::getFieldValue( $daoName, $searchValue, $returnColumn, $searchColumn );
        $testCase->assertNotNull( $value, $message );
        
        return $value;
    }

    // Request a record from the DB by seachColumn+searchValue. Success if returnColumn value is NULL. 
    function assertDBNull( &$testCase, $daoName, $searchValue, $returnColumn, $searchColumn, $message  ) 
    {
        $value = CRM_Core_DAO::getFieldValue( $daoName, $searchValue, $returnColumn, $searchColumn );
        $testCase->assertNull(  $value, $message );
    }

    // Request a record from the DB by id. Success if row not found. 
    function assertDBRowNotExist( &$testCase, $daoName, $id, $message  ) 
    {
        $value = CRM_Core_DAO::getFieldValue( $daoName, $id, 'id', 'id' );
        $testCase->assertNull(  $value, $message );
    }

    // Compare a single column value in a retrieved DB record to an expected value
    function assertDBCompareValue( &$testCase, $daoName, $searchValue, $returnColumn, $searchColumn,
                                   $expectedValue, $message  ) 
    {
        $value = CRM_Core_DAO::getFieldValue( $daoName, $searchValue, $returnColumn, $searchColumn );
        $testCase->assertEquals(  $value, $expectedValue, $message );
    }

    // Compare all values in a single retrieved DB record to an array of expected values
    function assertDBCompareValues( &$testCase, $daoName, $searchParams, $expectedValues )  
    {
        //get the values from db 
        $dbValues = array( );
        CRM_Core_DAO::commonRetrieve( $daoName, $searchParams, $dbValues );
        

        // compare db values with expected values
        self::assertAttributesEquals( $testCase, $expectedValues, $dbValues);
    }

    function assertAttributesEquals( &$testCase, &$expectedValues, &$actualValues ) 
    {
        foreach( $expectedValues as $paramName => $paramValue ) {
            if ( isset( $actualValues[$paramName] ) ) {
                $testCase->assertEquals( $paramValue, $actualValues[$paramName] );
            } else {
                $testCase->fail( "Attribute '$paramName' not present in actual array." );
            }
        }        
    }

}


