INSERT INTO patient(
    user_id,
    registered_by_midwife_id,
    patient_type,
    mother_id,
    date_of_registration,
    family_serial_number,
    first_name,
    middle_name,
    last_name,
    name_of_mother,
    address,
    age_bracket,
    birth_date,
    age,
    socio_economic_status,
    contact_number,
    email,
    health_center_id
) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);