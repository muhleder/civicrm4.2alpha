<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  File for the CiviTestSuite class
 *
 *  (PHP 5)
 *  
 *   @copyright Copyright CiviCRM LLC (C) 2009
 *   @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 *   @version   $Id: CiviTestSuite.php 32795 2011-03-02 14:57:02Z shot $
 *   @package   CiviCRM
 *
 *   This file is part of CiviCRM
 *
 *   CiviCRM is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Affero General Public License
 *   as published by the Free Software Foundation; either version 3 of
 *   the License, or (at your option) any later version.
 *
 *   CiviCRM is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public
 *   License along with this program.  If not, see
 *   <http://www.gnu.org/licenses/>.
 */

/**
 *  Include parent class definition
 */
require_once 'PHPUnit/Framework/TestSuite.php';

/**
 *  Parent class for test suites
 *
 *  @package   CiviCRM
 */
class CiviTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     *  Test suite setup
     */
    protected function setUp()
    {
        //print __METHOD__ . "\n";
    }
 
    /**
     *  Test suite teardown
     */
    protected function tearDown()
    {
        //print __METHOD__ . "\n";
    }

    /**
     *  suppress failed test error issued by phpunit when it finds
     *  a test suite with no tests
     */
    function testNothing()
    {
    }

    /**
     *
     */
    protected function implSuite( $myfile )
    {
        //echo get_class($this)."::implSuite($myfile)\n";
        $suite = new PHPUnit_Framework_TestSuite( get_class( $this ) );
        $this->addAllTests( $suite, $myfile,
                            new SplFileInfo( dirname( $myfile ) ) );
        return $suite;
    }

    /**
     *  Add all test classes *Test and all test suites *Tests in subdirectories
     *
     *  @param  &object Test suite object to add tests to
     *  @param  object  Directory to scan
     *  @return Test suite has been updated
     */
    protected function addAllTests( PHPUnit_Framework_TestSuite &$suite,
                                    $myfile, SplFileInfo $dirInfo )
    {
        //echo get_class($this)."::addAllTests($myfile,".$dirInfo->getRealPath().")\n";
        if ( !$dirInfo->isReadable( )
            || !$dirInfo->isDir( ) ) {
            return;
        }
        
        //  Pass 1:  Check all *Tests.php files
        //echo "start Pass 1 on {$dirInfo->getRealPath()}\n";
        $dir = new DirectoryIterator( $dirInfo->getRealPath( ) );
        foreach ( $dir as $fileInfo ) {
            if ( $fileInfo->isReadable( ) && $fileInfo->isFile( )
                 && preg_match( '/Tests.php$/',
                                $fileInfo->getFilename( ) ) ) {
                if ( $fileInfo->getRealPath( ) == $myfile ) {
                    //  Don't create an infinite loop
                    //echo "ignoring {$fileInfo->getRealPath()}\n";
                    continue;
                }
                //echo "checking file ".$fileInfo->getRealPath( )."\n";
                //  This is a file with a name ending in 'Tests.php'.
                //  Get all classes defined in the file and add those
                //  with a class name ending in 'Test' to the test suite
                $oldClassNames = get_declared_classes();
                require_once $fileInfo->getRealPath( );
                $newClassNames = get_declared_classes();
                foreach( array_diff( $newClassNames,
                                     $oldClassNames ) as $name ) {
                    if ( preg_match( '/Tests$/', $name ) ) {
                        //echo "adding test $name\n";
                        $suite->addTest( call_user_func( $name . '::suite'));
                    }
                }
            }
        }

        //  Pass 2:  Scan all subdirectories
        $dir = new DirectoryIterator( $dirInfo->getRealPath( ) );
        //echo "start Pass 2 on {$dirInfo->getRealPath()}\n";
        foreach ( $dir as $fileInfo ) {
            if ( $fileInfo->isDir( )
                && ( substr( $fileInfo->getFilename( ), 0, 1 ) != '.' ) ) {

                //  This is a directory that may contain tests so scan it
                //echo "descending into {$fileInfo->getRealPath()}\n";
                $this->addAllTests( $suite, $myfile, $fileInfo );
            }
        }

        //  Pass 3:  Check all *Test.php files in this directory
        //echo "start Pass 3 on {$dirInfo->getRealPath()}\n";
        $dir = new DirectoryIterator( $dirInfo->getRealPath( ) );
        foreach ( $dir as $fileInfo ) {
            if ( $fileInfo->isReadable( ) && $fileInfo->isFile( )
                 && preg_match( '/Test.php$/',
                                $fileInfo->getFilename( ) ) ) {
                //echo "checking file ".$fileInfo->getRealPath( )."\n";
                //  This is a file with a name ending in 'Tests?.php'.
                //  Get all classes defined in the file and add those
                //  with a class name ending in 'Test' to the test suite
                $oldClassNames = get_declared_classes();
                require_once $fileInfo->getRealPath( );
                $newClassNames = get_declared_classes();
                foreach( array_diff( $newClassNames,
                                     $oldClassNames ) as $name ) {
                    if ( preg_match( '/Test$/', $name ) ) {
                        //echo "adding suite $name\n";
                        $suite->addTestSuite( $name );
                    } 
                }
            }
        }
    }
}

// -- set Emacs parameters --
// Local variables:
// mode: php;
// tab-width: 4
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
