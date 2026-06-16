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
                                <input class="form-control-search" type="text" name="search_postpartum" id="search_postpartum" placeholder="Search Postpartum Record">
                            </div>
                            <div class="filter">
                                <select class="form-select" class= "form-control" onchange="selectData(this.options[this.selectedIndex].value)">
                                    <option value="all">All Maternal Records</option>
                                    <option value="complete_supplementation">Complete Supplemention</option>
                                    <option value="missing_iron_post">Incomplete Iron w/Folic Acid(postpartum)</option>
                                    <option value="missing_vitA">Missing Vitamin A</option>
                                </select>
                            </div>  
                            <a href="#" onclick="loadPage('patient/postpartum/add_postpartum.php')" class="btn btn-primary ms-3 me-3" name="add_maternal_btn"><i class="bi bi-person-plus me-2" style="color:white;"></i>Add Postpartum Record</a>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table table-hover mb-0" style="background-color: #fff;">
                <caption>List of Postpartum Maternal Patients</caption>
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
                <tbody id="postpartum_record_list">

                </tbody>
            </table>

            <div id="pagination-container" class="mt-4"></div>
        </div>
    </div>
</div>