<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
    xmlns:exsl="http://exslt.org/common"
    xmlns:str="http://exslt.org/strings"
    xmlns:date="http://exslt.org/dates-and-times"
    extension-element-prefixes="exsl str date">
<xsl:include href="str.replace.function.xsl"/>
<xsl:output method="html" indent="yes" encoding="US-ASCII"/>
<xsl:decimal-format decimal-separator="." grouping-separator=","/>
<!--
   Copyright 2001-2004 The Apache Software Foundation

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
 -->

<!--

 Sample stylesheet to be used with Phing/PHPUnit2 output.
 Based on JUnit stylesheets from Apache Ant.

 It creates a set of HTML files a la javadoc where you can browse easily
 through all packages and classes.

 @author Michiel Rook <a href="mailto:michiel.rook@gmail.com"/>
 @author Stephane Bailliez <a href="mailto:sbailliez@apache.org"/>
 @author Erik Hatcher <a href="mailto:ehatcher@apache.org"/>
 @author Martijn Kruithof <a href="mailto:martijn@kruithof.xs4all.nl"/>

-->
<xsl:param name="output.dir" select="'.'"/>

<xsl:template match="testsuites">
    <!-- create the index.html -->
   <exsl:document href="efile://{$output.dir}/index.html">
        <xsl:call-template name="index.html"/>
    </exsl:document>

    <!-- create the stylesheet.css -->
    <exsl:document href="efile://{$output.dir}/stylesheet.css">
        <xsl:call-template name="stylesheet.css"/>
    </exsl:document>

    <!-- 
      Create suite-list.html. This will be constantly present in the left
      frame, displaying a link to each test suite in the test suite hierarchy.
      When one of these links is clicked, it loads the results from that
      test suite into the larger right frame.
      -->
    <exsl:document href="efile://{$output.dir}/suite-list.html">
      <html>
        <head>
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
          <title>All Unit Test Suites</title>
          <link rel="stylesheet" type="text/css" title="Style" href="stylesheet.css" />
        </head>
        <body>
          <h2>Test Suites</h2>
          <xsl:apply-templates select="testsuite" mode="all.suites"/>
        </body>
      </html>
    </exsl:document>

    <!--
      Create a report for each test suite
    -->
    <xsl:apply-templates select="testsuite" mode="report"/>

</xsl:template>

<!--
  Create index.html in the output directory.  This defines the frames
  used to navigate and display the reports.
-->
<xsl:template name="index.html">
<html>
    <head>
        <title>Unit Test Results.</title>
    </head>
    <frameset cols="20%,80%"> -->
      <frame src="suite-list.html" name="suiteListFrame"/>
      <xsl:element name="frame">
        <xsl:attribute name="src">
         <xsl:value-of select="concat(/testsuites/testsuite[1]/@name,'.html')" />
        </xsl:attribute>
        <xsl:attribute name="name">
          <xsl:text>reportFrame</xsl:text>
        </xsl:attribute>
      </xsl:element>
      <noframes>
        <h2>Frame Alert</h2>
          <p>
            This document is designed to be viewed using the frames feature.
            If you see this message, you are using a non-frame-capable web client.
          </p>
      </noframes>
    </frameset>
</html>
</xsl:template>

<!-- this is the stylesheet css to use for nearly everything -->
<xsl:template name="stylesheet.css">
body {
    font-family: verdana,arial,helvetica;
    color:#000000;
    font-size: 10px;
}
ul {
    padding-left: 0.5em;
    list-style-type: none;
}
table tr td, table tr th, li, a {
    font-family: verdana,arial,helvetica;
    font-size: 10px;
}
table.details tr th{
    font-family: verdana,arial,helvetica;
    font-weight: bold;
    text-align:left;
    background:#a6caf0;
}
table.details tr td{
    background:#eeeee0;
}

p {
    line-height:1.5em;
    margin-top:0.5em; margin-bottom:1.0em;
    font-size: 10px;
}
h1 {
    margin: 0px 0px 5px;
    font-family: verdana,arial,helvetica;
}
h2 {
    margin-top: 1em; margin-bottom: 0.5em;
    font-family: verdana,arial,helvetica;
}
h3 {
    margin-bottom: 0.5em;
    font-family: verdana,arial,helvetica;
}
h4 {
    margin-bottom: 0.5em;
    font-family: verdana,arial,helvetica;
}
h5 {
    margin-bottom: 0.5em;
    font-family: verdana,arial,helvetica;
}
h6 {
    margin-bottom: 0.5em;
    font-family: verdana,arial,helvetica;
}
.Pass {
    font-weight:bold; color:green;
}
.Error {
    font-weight:bold; color:red;
}
.Failure {
    font-weight:bold; color:purple;
}
.small {
   font-size: 9px;
}
a {
  color: #003399;
}
a:hover {
  color: #888888;
}
</xsl:template>


<!-- =========================================================================
     Create suite-list-frame.html. This stays constantly present in the left
     frame, displaying a link to each test suite in the test suite hierarchy.
     When one of these links is clicked, it loads the results from that
     test suite into the larger right frame.
 ========================================================================= -->
 <xsl:template match="testsuites" mode="all.suites">
    <html>
        <head>
            <title>All Unit Test Suites</title>
            <xsl:call-template name="create.stylesheet.link">
                <xsl:with-param name="package.name"/>
            </xsl:call-template>
        </head>
        <body>
            <h2>Test Suites</h2>
            <table width="100%">
                <xsl:apply-templates select="testsuite" mode="all.suites">
                    <xsl:sort select="@name"/>
                </xsl:apply-templates>
            </table>
        </body>
    </html>
</xsl:template>

<!-- =========================================================================
     Add a test suite to suite-list.html, recursively.
 ========================================================================= -->
<xsl:template match="testsuite" mode="all.suites">
    <xsl:variable name="testsuite.name" select="@name" />
    <ul>
      <li>
        <xsl:element name="a">
            <xsl:attribute name="target">
                <xsl:text>reportFrame</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="href">
                <xsl:value-of select="concat($testsuite.name,'.html')" />
            </xsl:attribute>
            <xsl:attribute name="class">
              <xsl:choose>
                <xsl:when test="@errors &gt; 0">Error</xsl:when>
                <xsl:when test="@failures &gt; 0">Failure</xsl:when>
                <xsl:otherwise>Pass</xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
            <xsl:value-of select="$testsuite.name" />
        </xsl:element>
      </li>
      <xsl:apply-templates select="testsuite" mode="all.suites">
        <xsl:sort select="@name"/>
      </xsl:apply-templates>
    </ul>
</xsl:template>

<!-- =========================================================================
     Output the report for a test suite, recursively
 ========================================================================= -->
<xsl:template match="testsuite" mode="report">
  <xsl:variable name="testsuite.name" select="@name" />
  <exsl:document href="efile://{$output.dir}/{$testsuite.name}.html">
    <html>
      <head>
        <xsl:element name="title">
            <xsl:text>Test Suite </xsl:text>
            <xsl:value-of select="$testsuite.name" />
            <xsl:text> Report</xsl:text>
        </xsl:element>
        <link rel="stylesheet" type="text/css" href="stylesheet.css" />
      </head>
      <body>
        <xsl:attribute name="onload">open('suite-list.html','suiteListFrame')</xsl:attribute>
        <xsl:call-template name="reportHeader">
           <xsl:with-param name="testsuite.name" select="$testsuite.name" />
        </xsl:call-template>

        <!--  Output the report summary  -->
        <h2>Summary</h2>
        <xsl:variable name="successRate" select="(@tests - @failures - @errors) div @tests"/>
        <table class="details" border="0" cellpadding="5" cellspacing="2" width="95%">
        <tr valign="top">
            <th>Tests</th>
            <th>Errors</th>
            <th>Failures</th>
            <th>Success rate</th>
            <th>Time</th>
        </tr>
        <tr valign="top">
            <xsl:attribute name="class">
                <xsl:choose>
                    <xsl:when test="@errors &gt; 0">Error</xsl:when>
                    <xsl:when test="@failures &gt; 0">Failure</xsl:when>
                    <xsl:otherwise>Pass</xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
            <td><xsl:value-of select="@tests"/></td>
            <td><xsl:value-of select="@errors"/></td>
            <td><xsl:value-of select="@failures"/></td>
            <td>
                <xsl:call-template name="display-percent">
                    <xsl:with-param name="value" select="$successRate"/>
                </xsl:call-template>
            </td>
            <td>
                <xsl:call-template name="display-time">
                    <xsl:with-param name="value" select="@time"/>
                </xsl:call-template>
            </td>
        </tr>
        </table>
        <table border="0" width="95%">
        <tr>
        <td style="text-align: justify;">
        Note: <em>failures</em> are anticipated and checked for with assertions while <em>errors</em> are unanticipated.
        </td>
        </tr>
        </table>

        <!--  If this test suite contains any test suites,
              output a summary line for each test suite    -->
        <xsl:if test="count(testsuite)">
          <h2>Test Suites</h2>
          <table class="details" border="0" cellpadding="5" cellspacing="2" width="95%">
            <tr valign="top">
              <th width="80%">Name</th>
              <th>Tests</th>
              <th>Errors</th>
              <th>Failures</th>
              <th nowrap="nowrap">Time(s)</th>
            </tr>
             <xsl:apply-templates select="testsuite" mode="summary">
                <xsl:sort select="@name"/>
             </xsl:apply-templates>
           </table>
         </xsl:if>

        <!--  If this test suite contains any test cases,
              output a report line for each test case    -->
        <xsl:if test="count(testcase)">
          <h2>Test Cases</h2>
          <table class="details" border="0" cellpadding="5" cellspacing="2" width="95%">
            <tr valign="top">
              <th>Name</th>
              <th>Result</th>
              <th width="80%">Error Description</th>
              <th nowrap="nowrap">Time(s)</th>
            </tr>
            <xsl:apply-templates select="testcase" mode="report">
                <xsl:sort select="@name"/>
             </xsl:apply-templates>
          </table>
        </xsl:if>
          <table width="100%">
            <tr><td><hr noshade="yes" size="1"/></td></tr>
            <tr><td class="small">Report generated at <xsl:value-of select="date:date-time()"/></td></tr>
           </table>

        </body>
        </html>
  </exsl:document>

  <!--  Generate a report for any test suites contained in this test suite -->
  <xsl:apply-templates select="testsuite" mode="report">
    <xsl:sort select="@name"/>
  </xsl:apply-templates>

</xsl:template>

<!-- =========================================================================
     Output a table row summary for a test suite
 ========================================================================= -->
<xsl:template match="testsuite" mode="summary">
    <tr>
      <td>
        <xsl:element name="a">
          <xsl:attribute name="href">
            <xsl:value-of select="concat(@name,'.html')"/>
          </xsl:attribute>
          <xsl:attribute name="class">
            <xsl:choose>
              <xsl:when test="@errors &gt; 0">Error</xsl:when>
              <xsl:when test="@failures &gt; 0">Failure</xsl:when>
              <xsl:otherwise>Pass</xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <xsl:value-of select="@name"/>
        </xsl:element>
      </td>
      <td><xsl:value-of select="@tests"/></td>
      <td><xsl:value-of select="@errors"/></td>
      <td><xsl:value-of select="@failures"/></td>
      <td><xsl:value-of select="@time"/></td>
    </tr>
</xsl:template>

<!--
    transform string like a.b.c to ../../../
    @param path the path to transform into a descending directory path
-->
<xsl:template name="path">
    <xsl:param name="path"/>
    <xsl:if test="contains($path,'.')">
        <xsl:text>../</xsl:text>
        <xsl:call-template name="path">
            <xsl:with-param name="path"><xsl:value-of select="substring-after($path,'.')"/></xsl:with-param>
        </xsl:call-template>
    </xsl:if>
    <xsl:if test="not(contains($path,'.')) and not($path = '')">
        <xsl:text>../</xsl:text>
    </xsl:if>
</xsl:template>

<!-- Output Report Header -->
<xsl:template name="reportHeader">
    <xsl:param name="testsuite.name" />
    <xsl:element name="h1">
      <xsl:text>Test Suite </xsl:text>
      <xsl:value-of select="$testsuite.name" />
      <xsl:text> Results Report</xsl:text>
    </xsl:element>
    <table width="100%">
      <tr>
        <td align="left">See also <a href='http://tests.dev.civicrm.org/coverage/'>Coverage Report</a>.</td>
        <td align="right">Designed for use with <a href='http://pear.php.net/package/PHPUnit2'>PHPUnit2</a> and <a href='http://phing.info/'>Phing</a>.</td>
      </tr>
    </table>
    <hr size="1"/>
</xsl:template>

<!--  Output results of one test case  -->
<xsl:template match="testcase" mode="report">
    <tr valign="top">
        <xsl:attribute name="class">
            <xsl:choose>
                <xsl:when test="error">Error</xsl:when>
                <xsl:when test="failure">Failure</xsl:when>
                <xsl:otherwise>TableRowColor</xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <td><xsl:value-of select="@name"/></td>
        <xsl:choose>
            <xsl:when test="failure">
                <td>Failure</td>
                <td><xsl:apply-templates select="failure"/></td>
            </xsl:when>
            <xsl:when test="error">
                <td>Error</td>
                <td><xsl:apply-templates select="error"/></td>
            </xsl:when>
            <xsl:otherwise>
                <td>Success</td>
                <td></td>
            </xsl:otherwise>
        </xsl:choose>
        <td>
            <xsl:call-template name="display-time">
                <xsl:with-param name="value" select="@time"/>
            </xsl:call-template>
        </td>
    </tr>
</xsl:template>


<!-- Note : the below template error and failure are the same style
            so just call the same style store in the toolkit template -->
<xsl:template match="failure">
    <xsl:call-template name="display-failures"/>
</xsl:template>

<xsl:template match="error">
    <xsl:call-template name="display-failures"/>
</xsl:template>

<!-- Style for the error and failure in the testcase template -->
<xsl:template name="display-failures">
    <xsl:choose>
        <xsl:when test="not(@message)">N/A</xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="@message"/>
        </xsl:otherwise>
    </xsl:choose>
    <!-- display the stacktrace -->
    <br/><br/>
    <code>
        <xsl:call-template name="br-replace">
            <xsl:with-param name="word" select="."/>
        </xsl:call-template>
    </code>
</xsl:template>

<!--
    template that will convert a carriage return into a br tag
    @param word the text from which to convert CR to BR tag
-->
<xsl:template name="br-replace">
    <xsl:param name="word"/>
    <xsl:choose>
         <xsl:when test="contains($word,'&#x0A;')">
             <xsl:value-of select="substring-before($word,'&#x0A;')"/>
             <br />
             <xsl:call-template name="br-replace">
                 <xsl:with-param name="word" select="substring-after($word,'&#x0A;')"/>
             </xsl:call-template>
         </xsl:when>
         <xsl:otherwise>
             <xsl:value-of select="$word"/>
         </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template name="display-time">
    <xsl:param name="value"/>
    <xsl:value-of select="format-number($value,'0.000')"/>
</xsl:template>

<xsl:template name="display-percent">
    <xsl:param name="value"/>
    <xsl:value-of select="format-number($value,'0.00%')"/>
</xsl:template>
</xsl:stylesheet>

