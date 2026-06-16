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
                                <input class="form-control-search" type="text" name="search_maternal" id="search_maternal" placeholder="Search Maternal Records">
                            </div>
                            <div class="filter">
                                <select class="form-select" class= "form-control" onchange="selectMaternalData(this.options[this.selectedIndex].value)">
                                    <option value="all">All Maternal Records</option>
                                    <option value="no_immunization">No Immunization</option>
                                    <option value="incomplete_immunization">Incomplete Immunization</option>
                                    <option value="complete_immunization">Complete Immunization</option>
                                    <option value="complete_supplementation">Complete Supplemention</option>
                                    <option value="missing_td1">Missing Td1/TT1</option>
                                    <option value="missing_td2">Missing Td2/TT2</option>
                                    <option value="missing_td3">Missing Td3/TT3</option>
                                    <option value="missing_td4">Missing Td4/TT4</option>
                                    <option value="missing_td5">Missing Td5/TT5</option>
                                    <option value="missing_iron">Incomplete Iron w/Folic Acid</option>
                                    <option value="missing_calcium">Incomplete Calcium Carbonate</option>
                                    <option value="missing_iodine">Iodine Capsule not given</option>

                                    <option value="missing_iron_post">Incomplete Iron w/Folic Acid(postpartum)</option>
                                    <option value="missing_vitA">Missing Vitamin A</option>
                                </select>
                            </div>                   
                            <a href="#" onclick="loadPage('patient/maternal/add_maternal.php')" class="btn btn-primary ms-3 me-3" name="add_maternal_btn"><i class="bi bi-person-plus me-2" style="color:white;"></i>Add Maternal Record</a>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table table-hover mb-0" style="background-color: #fff;">
                <caption><strong>List of Maternal Patients</strong></caption>
                <thead class="table-dark">
                    <tr>
                        <th style="color: #fff;">No.</th>
                        <th style="color: #fff;">Date of Registration</th>
                        <th style="color: #fff;">Family Serial No.</th>
                        <th style="color: #fff;">Name</th>
                        <th style="color: #fff;">Address</th>
                        <th style="color: #fff;">Socio-Economic Status</th>
                        <th style="color: #fff;">Actions</th>
                    </tr>
                </thead>
                <tbody id="maternal_record_list">

                </tbody>
            </table>

            <div id="pagination-container" class="mt-4"></div>

        </div>
    </div>
</div>