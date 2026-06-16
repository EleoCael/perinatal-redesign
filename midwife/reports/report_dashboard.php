

<div class="container-fluid">
    <div class="filter-card">
        <h5 class="mb-4">Generate Report</h5>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <label class="form-label fw-bold">Report Type</label>
                <div>
                    <button class="btn btn-outline-primary me-2 mb-2 active" onclick="setReportType('maternal')">
                        Prenatal Care
                    </button>
                    <button class="btn btn-outline-primary me-2 mb-2" onclick="setReportType('child')">
                        Infant Care & Immunization
                    </button>
                    <button class="btn btn-outline-primary mb-2" onclick="setReportType('nutrition')">
                        Nutrition Services
                    </button>
                </div>
            </div>
        </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Report Period</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="period" value="monthly" id="monthly" checked onchange="togglePeriodFields()">
                            <label class="form-check-label" for="monthly">
                                Monthly
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="period" value="quarterly" id="quarterly" onchange="togglePeriodFields()">
                            <label class="form-check-label" for="quarterly">
                                Quarterly
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="period" value="annual" id="annual" onchange="togglePeriodFields()">
                            <label class="form-check-label" for="annual">
                                Annual
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        <div class="row align-items-end">
            <div class="col-md-2" id="month-div">
                <label class="form-label fw-bold">Month</label>
                <select class="form-select" id="month">
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10" selected>October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </div>

            <div class="col-md-2 hidden" id="quarter-div">
                <label class="form-label fw-bold">Quarter</label>
                <select class="form-select" id="quarter">
                    <option value="1">Q1 (Jan-Mar)</option>
                    <option value="2">Q2 (Apr-Jun)</option>
                    <option value="3">Q3 (Jul-Sep)</option>
                    <option value="4" selected>Q4 (Oct-Dec)</option>
                </select>
            </div>
    
            <div class="col-md-2">
                <label class="form-label fw-bold">Year</label>
                <input type="number" class="form-control" id="year" value="2025" min="2020" max="2030">
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="generateReport()">
                    <i class="bi bi-file-earmark-bar-graph-fill text-white"></i> Generate
                </button>
            </div>

            <div class="col-md-2">
                <button class="btn btn-success w-100" onclick="window.print()">
                    <i class="bi bi-printer-fill text-white"></i> Print
                </button>
            </div>
        </div>
    </div>

    <div id="reportContent" class="hidden report-print">
        <div id="prenatalReport" class="hidden">
            
                <h4 class=" mb-3"><span id="prenatalTitle"></span></h4>
            
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="prenatalTable">
                </table>
            </div>
        </div>

        <div id="childReport" class="hidden">
            
                <h4 class=" mb-3"><span id="childTitle"></span></h4>  
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="childTable">
                </table>
            </div>
        </div>

        <div id="nutritionReport" class="hidden">
           
                <h4 class=" mb-3"><span id="nutritionTitle"></span></h4>
              
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="nutritionTable">
                </table>
            </div>
        </div>
    </div>
</div>

