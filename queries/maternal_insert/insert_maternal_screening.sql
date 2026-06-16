INSERT INTO maternal_disease_screening(
    pregnancy_id, 
    syphilis_screening, 
    syphilis_date, 
    syphilis_screening_remarks, 
    hepatitis_b_screening, 
    hepatitisB_date, 
    hepatitis_b_screening_remarks, 
    hiv_screening, 
    hiv_date, 
    hiv_screening_remarks,
    gestational_diabetes_screening, 
    gestational_diabetes_date, 
    diabetes_remarks, 
    cbc_hgb_hct_count, 
    cbc_hgb_hct_date, 
    anemia_status, 
    anemia_status_remarks, 
    given_iron, 
    given_iron_date,
    maternal_screening_remark 
    
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);