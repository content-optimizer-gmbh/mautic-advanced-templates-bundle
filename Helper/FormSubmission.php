<?php

namespace MauticPlugin\MauticAdvancedTemplatesBundle\Helper;

class FormSubmission
{

    /**
     * FormSubmission constructor.
     *
     */
    public function __construct($dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Try to retrieve the current form values of the active lead 
     * 
     * @param integer $leadId  
     * @param integer $emailId
     */
    public function getFormData($leadId)
    {
        
        if(!$leadId)
        {
           return array();
        }        
        
        $formData = array();
        $connection = $this->dbal;

        $stmt       =  $connection->executeQuery(
            "SELECT max(id) as id,form_id from form_submissions fs
             WHERE
                fs.lead_id = $leadId
                group by form_id"
        );
        $stmt->execute();

        $formSubmissions = $stmt->fetchAll();
        if (!$formSubmissions) {
            return array();
        }

        //search form submissions
        $formId = false;
        foreach ($formSubmissions as $submission) 
        {
            $formId = (int) $submission['form_id'];
            if (!$formId) {
                continue;
            }
    
            // build name for form result table
            $stmt       =  $connection->executeQuery(
                'SELECT f.alias from forms f where f.`id` =' . $formId
            );
            $stmt->execute();
    
            $formRecord = $stmt->fetchAll();
            if (!$formRecord) {
                continue;
            }
    
            //try to fetch the form data
            $tableName = 'form_results_' . $formId . '_' . $formRecord[0]['alias'];
    
            $stmt       =  $connection->executeQuery(
                'select * from ' . $tableName . ' where submission_id = ' . $submission['id']
            );
            $stmt->execute();
    
            $postData = $stmt->fetchAll();

            if (is_array($postData) && count($postData) > 0) {
                if( !isset($formData[ $submission['form_id'] ] )){
                    $formData[ $submission['form_id'] ] = $postData[0];
                    $formData[ $formRecord[0]['alias'] ] = $postData[0];
                }
            }            
        }

        return $formData;
    }    

}
