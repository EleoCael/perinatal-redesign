document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];

    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.setAttribute('max', today);
    });
    
    const referralDateInput = document.getElementById('newborn_screening_referral');
    const dateDoneInput = document.getElementById('newborn_screening_done');
    const cpabDateInput = document.getElementById('cpab_tt_date');
    const assessedDateInput = document.getElementById('cpab_tt_date_assessed');
    const bcgDateInput = document.getElementById('bcg_date');
    const hepaBDateInput = document.getElementById('hepaB_date');
    const pentavalentDateInput = document.getElementById('pentavalent_date');
    const opvDateInput = document.getElementById('opv_date');
    const ipvDateInput = document.getElementById('ipv_date');
    const mcvDateInput = document.getElementById('mcv_date');
    const ficDateInput = document.getElementById('fic_date');
    const rvvDateInput = document.getElementById('rvv_date');
    const pcvDateInput = document.getElementById('pcv_date');

    const vitaminDateInput = document.getElementById('vitamin_date');
    const ironDateInput = document.getElementById('iron_date');
    const mnpDateInput = document.getElementById('mnp_date');
    const dewormingDateInput = document.getElementById('deworming_date');
    
    if (referralDateInput) {
        referralDateInput.addEventListener('change', validateReferralDateInput);
    }  
    if (dateDoneInput) {
        dateDoneInput.addEventListener('change', validateDateDoneInput);
    }
    if (cpabDateInput) {
        cpabDateInput.addEventListener('change', validateCpabDateInput);
    }
    if (assessedDateInput) {
        assessedDateInput.addEventListener('change', validateAssessedDateInput);
    }
    if (bcgDateInput) {
        bcgDateInput.addEventListener('change', validateBcgDateInput);
    }
    if (hepaBDateInput) {
        hepaBDateInput.addEventListener('change', validateHepaBDateInput);
    }
    if (pentavalentDateInput) {
        pentavalentDateInput.addEventListener('change', validatePentavalentDateInput);
    }
    if (opvDateInput) {
        opvDateInput.addEventListener('change', validateOpvDateInput);
    }
    if (ipvDateInput) {
        ipvDateInput.addEventListener('change', validateIpvDateInput);
    }
    if (mcvDateInput) {
        mcvDateInput.addEventListener('change', validateMcvDateInput);
    }
    if (ficDateInput) {
        ficDateInput.addEventListener('change', validateFicDateInput);
    }
    if (rvvDateInput) {
        rvvDateInput.addEventListener('change', validateRvvDateInput);
    }
    if (pcvDateInput) {
        pcvDateInput.addEventListener('change', validatePcvDateInput);
    }
    if (vitaminDateInput) {
        vitaminDateInput.addEventListener('change', validateVitaminDateInput);
    }
    if (ironDateInput) {
        ironDateInput.addEventListener('change', validateIronDateInput);
    }
    if (mnpDateInput) {
        mnpDateInput.addEventListener('change', validateMnpDateInput);
    }
    if (dewormingDateInput) {
        dewormingDateInput.addEventListener('change', validateDewormingDateInput);
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

function validateDateDoneInput() {
    const input = document.getElementById('newborn_screening_done');
    const errorSpan = document.getElementById('error_newborn_screening_done');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Date Done cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateReferralDateInput() {
    const input = document.getElementById('newborn_screening_referral');
    const errorSpan = document.getElementById('error_newborn_screening_referral');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Referral date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateCpabDateInput() {
    const input = document.getElementById('cpab_tt_date');
    const errorSpan = document.getElementById('error_cpab_tt_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateAssessedDateInput() {
    const input = document.getElementById('cpab_tt_date_assessed');
    const errorSpan = document.getElementById('error_cpab_tt_date_assessed');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Date Assessed cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateBcgDateInput() {
    const input = document.getElementById('bcg_date');
    const errorSpan = document.getElementById('error_bcg_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'BCG Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateHepaBDateInput() {
    const input = document.getElementById('hepaB_date');
    const errorSpan = document.getElementById('error_hepaB_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Hepa B1 Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatePentavalentDateInput() {
    const input = document.getElementById('pentavalent_date');
    const errorSpan = document.getElementById('error_pentavalent_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Pentavalent Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateOpvDateInput() {
    const input = document.getElementById('opv_date');
    const errorSpan = document.getElementById('error_opv_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'OPV Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateIpvDateInput() {
    const input = document.getElementById('ipv_date');
    const errorSpan = document.getElementById('error_ipv_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'IPV Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateMcvDateInput() {
    const input = document.getElementById('mcv_date');
    const errorSpan = document.getElementById('error_mcv_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'MCV Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateFicDateInput() {
    const input = document.getElementById('fic_date');
    const errorSpan = document.getElementById('error_fic_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'FIC Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateFicDateInput() {
    const input = document.getElementById('fic_date');
    const errorSpan = document.getElementById('error_fic_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'FIC Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateRvvDateInput() {
    const input = document.getElementById('rvv_date');
    const errorSpan = document.getElementById('error_rvv_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'FIC Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validatePcvDateInput() {
    const input = document.getElementById('pcv_date');
    const errorSpan = document.getElementById('error_pcv_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'FIC Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateVitaminDateInput() {
    const input = document.getElementById('vitamin_date');
    const errorSpan = document.getElementById('error_vitamin_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Vitmain A Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateIronDateInput() {
    const input = document.getElementById('iron_date');
    const errorSpan = document.getElementById('error_iron_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Iron Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateMnpDateInput() {
    const input = document.getElementById('mnp_date');
    const errorSpan = document.getElementById('error_mnp_date');
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (selectedDate > today) {
        errorSpan.textContent = 'Mnp Date cannot be in the future!';
        input.classList.add('is-invalid');
        return false;
    } else {
        errorSpan.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }
}

function validateDewormingDateInput() {
    const input = document.getElementById('deworming_date');
    const errorSpan = document.getElementById('error_deworming_date');
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


function validateAllDates() {
    const referralDateValid = validateReferralDateInput();
    const dateDoneValid = validateDateDoneInput();
    const cpabDateValid = validateCpabDateInput();
    const assessedDateVallid = validateAssessedDateInput();
    const bcgDateValid = validateBcgDateInput();
    const hepaBDateValid = validateHepaBDateInput();
    const pentavalentDateValid = validatePentavalentDateInput();
    const opvDateValid = validateOpvDateInput();
    const ipvDateValid = validateIpvDateInput();
    const mcvDateValid = validateMcvDateInput();
    const ficDateValid = validateFicDateInput();
    const rvvDateValid = validateRvvDateInput();
    const pcvDateValid = validatePcvDateInput();

    const vitaminDateValid = validateVitaminDateInput();
    const ironDateValid = validateIronDateInput();
    const mnpDateValid = validateMnpDateInput();
    const dewormingDateValid = validateDewormingDateInput();




    return referralDateValid && dateDoneValid && cpabDateValid && assessedDateVallid && bcgDateValid && hepaBDateValid
    && pentavalentDateValid && opvDateValid && ipvDateValid && mcvDateValid && ficDateValid && rvvDateValid && pcvDateValid
    && vitaminDateValid && ironDateValid && mnpDateValid && dewormingDateValid;
}
