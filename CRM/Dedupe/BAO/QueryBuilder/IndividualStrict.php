<?php


class CRM_Dedupe_BAO_QueryBuilder_IndividualStrict extends CRM_Dedupe_BAO_QueryBuilder {

    static function record($rg) {
        $civicrm_email = CRM_Utils_Array::value('civicrm_email', $rg->params, array());

        $params = array(
              1 => array(CRM_Utils_Array::value('email',$civicrm_email,''), 'String')
          );

        return array(
            "civicrm_contact.{$rg->name}.{$rg->threshold}" => CRM_Core_DAO::composeQuery("
                SELECT contact.id as id1, {$rg->threshold} as weight
                FROM civicrm_contact as contact
                  JOIN civicrm_email as email ON email.contact_id=contact.id
                WHERE contact_type = 'Individual'
                  AND email = %1", $params, true)
        );
    }

    static function internal($rg) {
        $query = "
            SELECT contact1.id as id1, contact2.id as id2, {$rg->threshold} as weight
            FROM civicrm_contact as contact1
              JOIN civicrm_email as email1 ON email1.contact_id=contact1.id
              JOIN civicrm_contact as contact2
              JOIN civicrm_email as email2 ON
                email2.contact_id=contact2.id AND
                email1.email=email2.email
            WHERE contact1.contact_type = 'Individual'
              AND ".self::internalFilters($rg);
        return array("civicrm_contact.{$rg->name}.{$rg->threshold}" => $query);
    }

    /**
     * An alternative version which might perform a lot better
     * than the above. Will need to do some testing
     */
    static function internalOptimized( $rg ) {
        $sql = "
CREATE TEMPORARY TABLE emails (
                               email varchar(255),
                               contact_id1 int,
                               contact_id2 int,
                               INDEX(contact_id1),
                               INDEX(contact_id2)
                              ) ENGINE=MyISAM
";
        CRM_Core_DAO::executeQuery( $sql );

        $sql = "
INSERT INTO emails
    SELECT email1.email as email, email1.contact_id as contact_id1, email2.contact_id as contact_id2
    FROM civicrm_email as email1
    JOIN civicrm_email as email2 USING (email)
    WHERE email1.contact_id < email2.contact_id
";
        CRM_Core_DAO::executeQuery( $sql );

        $query = "
SELECT contact_id1, contact_id2, email
FROM   emails
JOIN   civicrm_contact as contact1 on contact1.id=contact_id1
JOIN   civicrm_contact as contact2 on contact2.id=contact_id2
WHERE  contact1.contact_type='Individual' 
AND    contact2.contact_type='Individual'
AND    " . self::internalFilters($rg);
        return array("civicrm_contact.{$rg->name}.{$rg->threshold}" => $query);
    }

};

?>
