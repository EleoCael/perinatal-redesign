<!--Table-->
<div class="container-fluid">
  <div class="row">
    <div class="panel panel-default shadow-lg rounded">
      <div class="panel-heading">
        <div class="panel-body">
          <form method="POST">
            <div class="d-flex align-items-center">
              <div class="form-group position-relative flex-grow-1 me-2">
                <i class="fa fa-search search-icon"></i>
                <input class="form-control-search" type="text" name="search_infant" id="search_infant" placeholder="Search Infant Record">
              </div>
              <div>
                  <select class="form-select" class="form-control"  onchange="selectInfantData(this.value); this.blur();">
                      <option value="all">All Infant Records</option>
                      <option value="no_immunization">No Immunization</option>
                      <option value="incomplete_immunization">Incomplete Immunization</option>
                      <option value="complete_immunization">Complete Immunization</option>
                      <option value="complete_supplementation">Complete Supplemention</option>
                      <option value="missing_bcg">Missing BCG</option>
                      <option value="missing_hepaB">Missing HEPA B1</option>
                      <option value="incomplete_pentavalent">Incomplete Pentavalent</option>
                      <option value="incomplete_opv">Incomplete OPV</option>
                      <option value="missing_ipv">Missing IPV</option>
                      <option value="incomplete_opv">Incomplete OPV</option>
                      <option value="missing_ipv">Missing IPV</option>
                      <option value="incomplete_mcv">Incomplete MCV</option>
                      <option value="incomplete_rvv">Incomplete RVV</option>
                      <option value="incomplete_pcv">Incomplete PCV</option>

                      <option value="incomplete_vitA">Incomplete Vitamin A</option>
                      <option value="incomplete_iron">Incomplete Iron</option>
                      <option value="missing_deworming">Missing Deworming</option>
                  </select>
              </div>
              <a href="#" onclick="loadPage('patient/maternal/add_maternal.php')" class="btn btn-primary ms-3 me-3" name="add_maternal_btn">
                <i class="bi bi-person-plus me-2" style="color:white;"></i>Add Infant Record
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- Shadow applied to panel -->
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="background-color: #fff;">
          <caption>List of Infant Patients</caption>
          <thead class="table-dark">
            <tr>
              <th style="color: #fff;">No.</th>
              <th style="color: #fff;">Date of Registration</th>
              <th style="color: #fff;">Family Serial No.</th>
              <th style="color: #fff;">Name</th>
              <th style="color: #fff;">Name of Mother</th>
              <th style="color: #fff;">Socio-Economic Status</th>
              <th style="color: #fff;">Actions</th>
            </tr>
          </thead>
          <tbody id="infant_record_list"></tbody>
        </table>
      </div>

      <div id="pagination-container" class="mt-4"></div>
    </div>
  </div>

</div>