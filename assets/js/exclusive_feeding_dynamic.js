// Exclusive feeding dynamic field
$(document).on("click", "#add_feed_field", function (e) {
  e.preventDefault();
  const today = new Date().toISOString().split('T')[0];
  
  $(".dynamic_exclusive_feeding").append(`
            <div class="row g-3 mb-3 mt-2 align-items-center dynamic-row-exclusive-feeding">
                    <div class="col-md-5 dropdown">
                         <label class="form-label">Month Child was exclusively breastfed</label>
                            <select class="form-select" name="month_check[]">
                                <option value="" disabled selected>Select Month</option>
                                <option value="1st Month">1st Month</option>
                                <option value="2nd Month">2nd Month</option>
                                <option value="3rd Month">3rd Month</option>
                                <option value="4th Month">4th Month</option>
                                <option value="5th Month">5th Month</option>
                                <option value="6th  Month">6th Month</option>
                            </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Date:</label>
                        <input type="date" class="form-control dynamic-date" name="month_date[]" max="${today}">
                        <span class="error-date text-danger" style="font-size: 0.875em;"></span>
                    </div>
                    <div class="col d-flex align-items-center" style="padding-top: 10px;">
                        <button class="btn btn-danger remove-field-exlusive" type="button" >
                        <i class="bi bi-trash-fill text-white"></i>
                        </button>
                    </div>
            </div>
    `);
});

$(document).on("click", ".remove-field-exlusive", function (e) {
  e.preventDefault();
  $(this).closest(".dynamic-row-exclusive-feeding").remove();
});

// Complementary feeding dynamic field
$(document).on("click", "#add_comple_field", function (e) {
  e.preventDefault();
  const today = new Date().toISOString().split('T')[0];
  
  $(".dynamic_complementary_feeding").append(`
            <div class="row g-3 mb-3 mt-2 align-items-center  dynamic-row-complementary-feeding">
                    <div class="col-md-5 dropdown">
                         <label class="form-label">Complementary Feeding</label>
                            <select class="form-select" name="complementary_month_check[]">
                                    <option value="" disabled selected>Select Month</option>
                                    <option value="6th Month">6th Month</option>
                                    <option value="7th Month">7th Month</option>
                                    <option value="8th Month">8th Month</option>
                            </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Date:</label>
                        <input type="date" class="form-control dynamic-date" name="complementary_month_date[]" max="${today}">
                        <span class="error-date text-danger" style="font-size: 0.875em;"></span>
                    </div>
                    <div class="col d-flex align-items-center" style="padding-top: 10px;">
                        <button class="btn btn-danger remove-field-comple" type="button" >
                        <i class="bi bi-trash-fill text-white"></i>
                        </button>
                    </div>
            </div>
    `);
});

$(document).on("click", ".remove-field-comple", function (e) {
  e.preventDefault();
  $(this).closest(".dynamic-row-complementary-feeding").remove();
});