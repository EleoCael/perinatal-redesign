document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];

    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.setAttribute('max', today);
    });
    
    const dateOfRegInput = document.getElementById('date_of_registration');
    const birthDateInput = document.getElementById('birth_date');
    const lmpDateInput = document.getElementById('lmp');
    const dateTerminatedInput = document.getElementById('date_terminated');

    const prenatalCheckupInput = document.getElementById('checkup_date');
    const dewormingInput = document.getElementById('deworming_date_given');
    const syphilistDateInput = document.getElementById('syphilis_date');
    const hepaBDateInput = document.getElementById('hepatitisB_date');
    const hivDateInput = document.getElementById('hiv_date');
    const gestationalDateInput = document.getElementById('gestational_diabetes_date');
    const cbcDateInput = document.getElementById('cbc_hgb_hct_date');
    const ironDateInput = document.getElementById('given_iron_date');
    const immunizationDateInput = document.getElementById('immunization_date');
    const iodineDateInput = document.getElementById('date_iodine');
    
    const postDeliveryDateInput = document.getElementById('post_delivery_date');
    const postCheckupDateInput = document.getElementById('post_checkup_date');
    const dateBreastfedInput = document.getElementById('breastfeeding_date');
    const postIronDateInput = document.getElementById('iron_folic_date_given');
    const vitaminDateInput = document.getElementById('vitamin_a_date');

    if (dateOfRegInput) {
        dateOfRegInput.addEventListener('change', validateDateOfRegistration);
    }  
    if (birthDateInput) {
        birthDateInput.addEventListener('change', validateBirthDate);
    }
    if (lmpDateInput) {
        lmpDateInput.addEventListener('change', validateLmp); 
    }
    if (dateTerminatedInput) {
        dateTerminatedInput.addEventListener('change', validatedateTerminatedInput);    
    }
    if (prenatalCheckupInput) {
        prenatalCheckupInput.addEventListener('change', validatedatePrenatalCheckupInput);    
    }
    if (dewormingInput) {
        dewormingInput.addEventListener('change', validatedateDewormingInput);    
    }
    if (syphilistDateInput) {
        syphilistDateInput.addEventListener('change', validatedateSyphilistDateInput);    
    }
    if (hepaBDateInput) {
        hepaBDateInput.addEventListener('change', validatedateHepaBDateInput);    
    }
    if (hivDateInput) {
        hivDateInput.addEventListener('change', validatedateHivDateInput);    
    }
    if (gestationalDateInput) {
        gestationalDateInput.addEventListener('change', validatedateGestationalDateInput);    
    }
    if (cbcDateInput) {
        cbcDateInput.addEventListener('change', validatedateCbcDateInput);    
    }
    if (ironDateInput) {
        ironDateInput.addEventListener('change', validatedateIronDateInput);    
    }
    if (immunizationDateInput) {
        immunizationDateInput.addEventListener('change', validatedateImmunizationDateInput);    
    }
    if (iodineDateInput) {
        iodineDateInput.addEventListener('change', validatedateIodineDateInput);    
    }
    if (postDeliveryDateInput) {
        postDeliveryDateInput.addEventListener('change', validatedatePostDeliveryDateInput);    
    }
    if (postCheckupDateInput) {
        postCheckupDateInput.addEventListener('change', validatedatePostCheckupDateInput);    
    }
    if (dateBreastfedInput) {
        dateBreastfedInput.addEventListener('change', validatedateDateBreastfedInput);    
    }
    if (postIronDateInput) {
        postIronDateInput.addEventListener('change', validatedatePostIronDateInput);    
    }
    if (vitaminDateInput) {
        vitaminDateInput.addEventListener('change', validatedateVitaminDateInput);    
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateAllDates()) {
                e.preventDefault();
            }
        });
    }
});

function validateDateOfRegistration() {
    const input = document.getElementById('date_of_registration');
    const errorSpan = document.getElementById('error_date_of_registration');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Date of registration cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateBirthDate() {
    const input = document.getElementById('birth_date');
    const errorSpan = document.getElementById('error_birth_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Birth date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}
function validateLmp() {
    const input = document.getElementById('lmp');
    const errorSpan = document.getElementById('error_lmp');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Last Menstrual Period cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedateTerminatedInput() {
    const input = document.getElementById('date_terminated');
    const errorSpan = document.getElementById('error_date_terminated');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Date Terminated Period cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedatePrenatalCheckupInput() {
    const input = document.getElementById('checkup_date');
    const errorSpan = document.getElementById('error_checkup_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Check-up Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}
function validatedateDewormingInput() {
    const input = document.getElementById('deworming_date_given');
    const errorSpan = document.getElementById('error_deworming_date_given');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Deworming Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}
function validatedateSyphilistDateInput() {
    const input = document.getElementById('syphilis_date');
    const errorSpan = document.getElementById('error_syphilis_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Syphilis Screening Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}
function validatedateHepaBDateInput() {
    const input = document.getElementById('hepatitisB_date');
    const errorSpan = document.getElementById('error_hepatitisB_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Hepatitis B Screening Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}
function validatedateHivDateInput() {
    const input = document.getElementById('hiv_date');
    const errorSpan = document.getElementById('error_hiv_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'HIV Screening Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedateGestationalDateInput() {
    const input = document.getElementById('gestational_diabetes_date');
    const errorSpan = document.getElementById('error_gestational_diabetes_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Gestational Diabetes Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedateCbcDateInput() {
    const input = document.getElementById('cbc_hgb_hct_date');
    const errorSpan = document.getElementById('error_cbc_hgb_hct_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'CBC/Hgb&Hct Date Screened cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedateIronDateInput() {
    const input = document.getElementById('given_iron_date');
    const errorSpan = document.getElementById('error_given_iron_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Given Iron Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedateImmunizationDateInput() {
    const input = document.getElementById('immunization_date');
    const errorSpan = document.getElementById('error_immunization_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Immunization Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedateIodineDateInput() {
    const input = document.getElementById('date_iodine');
    const errorSpan = document.getElementById('error_date_iodine');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Iodine Capsule Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedatePostDeliveryDateInput() {
    const input = document.getElementById('post_delivery_date');
    const errorSpan = document.getElementById('error_post_delivery_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Date of Delivery cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedatePostCheckupDateInput() {
    const input = document.getElementById('post_checkup_date');
    const errorSpan = document.getElementById('error_post_checkup_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Checkup Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedateDateBreastfedInput() {
    const input = document.getElementById('breastfeeding_date');
    const errorSpan = document.getElementById('error_breastfeeding_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Date Breastfed cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedatePostIronDateInput() {
    const input = document.getElementById('iron_folic_date_given');
    const errorSpan = document.getElementById('error_iron_folic_date_given');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Date Given cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatedateVitaminDateInput() {
    const input = document.getElementById('vitamin_a_date');
    const errorSpan = document.getElementById('error_vitamin_a_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Date Given cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}


function validateAllDates() {
    const dateOfRegValid = validateDateOfRegistration();
    const birthDateValid = validateBirthDate();
    const lmpDateValid = validateLmp();
    const dateTerminatedValid = validatedateTerminatedInput();
    const prenatalCheckupValid = validatedatePrenatalCheckupInput();
    const dewormingDateValid = validatedateDewormingInput();
    const syphilisDateValid = validatedateSyphilistDateInput();
    const hepaBDateValid = validatedateHepaBDateInput();
    const hivDateValid = validatedateHivDateInput();
    const gestationalDateValid = validatedateGestationalDateInput();
    const cbcDateValid = validatedateCbcDateInput();
    const ironDateValid = validatedateIronDateInput();
    const immunzationDateValid = validatedateImmunizationDateInput();
    const iodineDateValid = validatedateIodineDateInput();

    const postDeliveryDateValid = validatedatePostDeliveryDateInput();
    const postCheckupDateValid = validatedatePostCheckupDateInput();
    const dateBreastfedValid = validatedateDateBreastfedInput();
    const postIronDateValid = validatedatePostIronDateInput();
    const vitaminDateInput = validatedateVitaminDateInput();
    
    return dateOfRegValid && birthDateValid && lmpDateValid && dateTerminatedValid && prenatalCheckupValid
    && dewormingDateValid && syphilisDateValid && hepaBDateValid && hivDateValid && gestationalDateValid
    && cbcDateValid && ironDateValid && immunzationDateValid && iodineDateValid && postDeliveryDateValid
    && postCheckupDateValid && dateBreastfedValid && postIronDateValid && vitaminDateInput;
}

