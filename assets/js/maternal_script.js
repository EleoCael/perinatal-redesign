//for dynamic supplement form
$(document).on("click", "#add_supp_field", function (e) {
  e.preventDefault();
  $(".dynamic_supp").append(`
      <div class="row g-3 mt-2 mb-4 align-items-center dynamic-row">
          <div class="col-md-3 dropdown ">
              <label class="form-label">Supplement</label>
              <select class="form-select" name="supplement_type[]">
                  <option value="" disabled selected>Select supplement type</option>
                  <option value="Iron Sulfate w/Folic Acid">Iron Sulfate w/Folic Acid</option>
                  <option value="Calcium Carbonate">Calcium Carbonate</option>
              </select>
          </div>
          <div class="col-md-3 dropdown">
              <label class="form-label">Trimester</label>
              <select class="form-select" name="supp_trimester[]">
                  <option value="" disabled selected>Select trimester</option>
                  <option value="1st visit (1st Tri)">1st visit(1st tri)</option>
                  <option value="2nd visit (2nd tri)">2nd visit (2nd tri)</option>
                  <option value="3rd visit (3rd tri)">3rd visit (3rd tri)</option>
                  <option value="4th visit (3rd tri)">4th visit (3rd tri)</option>
              </select>
          </div>
          <div class="col-md-3 ">
              <label class="form-label">Date Given</label>
              <input type="date" class="form-control" name="date_supp[]"
               max="<?php echo date('Y-m-d'); ?>" id="date_supp">
                <span id="error_date_supp" class="text-danger"></span>

          </div>
          <div class="col-md-2">
              <label class="form-label">Tablets Given</label>
              <input type="number" class="form-control" min="0" name="supp_tablets_given[]">
          </div>
          <div class="col d-flex align-items-center" style="padding-top: 10px;">
              <button class="btn btn-danger remove-field" type="button">
                  <i class="bi bi-trash-fill text-white"></i>
              </button>
          </div>
      </div>
  `);
});

$(document).on("click", ".remove-field", function (e) {
  e.preventDefault();

  $(this).closest(".dynamic-row").remove();
});


$(document).ready(function() {
    console.log('🚀 DIRECT jQuery Age Calculator Starting...');
    

    $(document).on('change input blur', 'input[name="birth_date"]', function() {
        console.log('🎯 BIRTH DATE CHANGED!');
        
        const birthDateValue = $(this).val();
        console.log('Date value:', birthDateValue);
        
        if (birthDateValue) {
           
            const today = new Date();
            const birthDate = new Date(birthDateValue);
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            console.log('Calculated age:', age);
            
           
            $('input[name="age"], #age').val(age);
            console.log('✓ Age set to:', age);
            
            
            let bracket = '';
            if (age >= 10 && age <= 14) bracket = '10-14';
            else if (age >= 15 && age <= 19) bracket = '15-19';
            else if (age >= 20 && age <= 49) bracket = '20-49';
            
            console.log('Age bracket:', bracket);
            
            
            $('input[name="age_bracket"]').prop('checked', false);
            
            if (bracket) {
                $('input[name="age_bracket"][value="' + bracket + '"]').prop('checked', true);
                console.log('✓ Age bracket checked:', bracket);
                $('#error_age').text('');
            } else {
                $('#error_age').text('Age is outside valid maternal care brackets (10-49 years old)');
                console.log('✗ Age outside valid range');
            }
            
            console.log('=== DONE ===\n');
        }
    });
    
  
    $(document).on('input', 'input[name="age"], #age', function() {
        const age = parseInt($(this).val());
        
        if (age && !isNaN(age)) {
            let bracket = '';
            if (age >= 10 && age <= 14) bracket = '10-14';
            else if (age >= 15 && age <= 19) bracket = '15-19';
            else if (age >= 20 && age <= 49) bracket = '20-49';
            
            $('input[name="age_bracket"]').prop('checked', false);
            
            if (bracket) {
                $('input[name="age_bracket"][value="' + bracket + '"]').prop('checked', true);
                $('#error_age').text('');
            } else {
                $('#error_age').text('Age is outside valid maternal care brackets (10-49 years old)');
            }
        }
    });
    
    console.log('✅ Age calculator ready! Try selecting a date...\n');
});


(function() {
    console.log('🔄 Installing backup observer...');
    
    function attachDirectly() {
        const birthInput = document.querySelector('input[name="birth_date"]');
        if (!birthInput) return;
        
        console.log('Observer: Found birth date input, attaching...');
        
 
        birthInput.onchange = function() {
            console.log('💥 DIRECT onchange fired!');
            $(this).trigger('change');
        };
        
        birthInput.oninput = function() {
            console.log('💥 DIRECT oninput fired!');
            $(this).trigger('input');
        };
    }
    
    
    setTimeout(attachDirectly, 1000);
    
 
    const observer = new MutationObserver(function(mutations) {
        attachDirectly();
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    console.log('✅ Backup observer installed\n');
})();

//for dynamic immunization form
$(document).on("click", "#add_fim_field", function (e) {
  e.preventDefault();
  $(".dynamic_immunization").append(`
            <div class="row g-3 mb-3 mt-2 align-items-center dynamic-row-immunization">
                    <div class="col-md-5 dropdown">
                        <label class="form-label">Immunization Type</label>
                            <select class="form-select" name="immunization_type[]">
                                <option value="" disabled selected>Select type</option>
                                <option value="Td1/TT1">Td1/TT1</option>
                                <option value="Td2/TT2">Td2/TT2</option>
                                <option value="Td3/TT3">Td3/TT3</option>
                                <option value="Td4/TT4">Td4/TT4</option>
                                <option value="Td5/TT5">Td5/TT5</option>
                            </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Date Given</label>
                        <input type="date" class="form-control" name="immunization_date[]"
                         max="<?php echo date('Y-m-d'); ?>" id="immunization_date">
                            <span id="error_immunization_date" class="text-danger"></span>

                    </div>
                    <div class="col d-flex align-items-center" style="padding-top: 10px;">
                        <button class="btn btn-danger remove-field-immunization" type="button">
                        <i class="bi bi-trash-fill text-white"></i>
                        </button>
                    </div>
            </div>
    `);
});

$(document).on("click", ".remove-field-immunization", function (e) {
  e.preventDefault();

  $(this).closest(".dynamic-row-immunization").remove();
});