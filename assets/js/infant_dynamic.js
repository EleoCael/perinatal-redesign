//age calculatoin for postpartum

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