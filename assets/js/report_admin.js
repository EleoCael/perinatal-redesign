let currentReportType = "maternal";
let barangays = [];
let loadAttempts = 0;
const MAX_LOAD_ATTEMPTS = 10;

// Data storage for reports
let prenatalData = [];
let childData = [];
let nutritionData = [];

// Initialize when page content is actually loaded
function initReportPage() {
    console.log("🚀 Initializing report page...");
    
    // Check if elements exist
    const barangayList = document.getElementById("barangayList");
    const selectAll = document.getElementById("selectAll");
    
    if (!barangayList) {
        console.warn(`⚠ Barangay list not found yet (attempt ${loadAttempts + 1}/${MAX_LOAD_ATTEMPTS})`);
        
        if (loadAttempts < MAX_LOAD_ATTEMPTS) {
            loadAttempts++;
            setTimeout(initReportPage, 200);
            return;
        } else {
            console.error("❌ Failed to find barangayList after maximum attempts");
            return;
        }
    }
    
    console.log("✓ Elements found, loading barangays...");
    loadAttempts = 0;
    loadBarangays();
    togglePeriodFields();
}

// Wait for DOM, then initialize
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM ready, waiting for page content...");
    initReportPage();
});

function loadBarangays() {
    const apiPaths = [
        "generateAPI.php?action=getBarangays",
        "../admin/generateAPI.php?action=getBarangays",
        "./generateAPI.php?action=getBarangays",
        "admin/generateAPI.php?action=getBarangays"
    ];
    
    let currentPathIndex = 0;
    
    function tryFetch(pathIndex) {
        if (pathIndex >= apiPaths.length) {
            console.error("All API paths failed");
            showError("Could not connect to server. Please check the API endpoint.");
            return;
        }
        
        const apiUrl = apiPaths[pathIndex];
        console.log(`Trying API path ${pathIndex + 1}/${apiPaths.length}: ${apiUrl}`);
        
        fetch(apiUrl)
            .then((response) => {
                console.log(`Response from ${apiUrl}:`, response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then((text) => {
                console.log(`Raw response from ${apiUrl}:`, text.substring(0, 200));
                const data = JSON.parse(text);
                console.log("Parsed JSON data:", data);
                
                if (data.success && data.data) {
                    barangays = data.data;
                    console.log(`✓ SUCCESS! Loaded ${barangays.length} barangays from ${apiUrl}:`, barangays);
                    renderBarangayList();
                } else {
                    throw new Error(data.error || "No data received from server");
                }
            })
            .catch((error) => {
                console.error(`✗ Failed with path ${apiUrl}:`, error.message);
                tryFetch(pathIndex + 1);
            });
    }
    
    tryFetch(currentPathIndex);
}

function renderBarangayList() {
    const container = document.getElementById("barangayList");
    
    if (!container) {
        console.error("❌ CRITICAL: barangayList container not found in DOM!");
        setTimeout(initReportPage, 500);
        return;
    }

    console.log("✓ Container found, rendering barangays...");
    container.innerHTML = "";

    if (!barangays || barangays.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    No barangays found. Please check if the database has health center records.
                </div>
            </div>
        `;
        console.warn("⚠ No barangays data available");
        return;
    }

    console.log(`Rendering ${barangays.length} barangays to the page`);
    
    let renderedCount = 0;
    barangays.forEach((barangay) => {
        if (!barangay.health_center_id || !barangay.barangay_name) {
            console.warn("⚠ Invalid barangay data:", barangay);
            return;
        }
        
        const col = document.createElement("div");
        col.className = "col-md-3 col-sm-6 mb-2";
        col.innerHTML = `
            <div class="form-check barangay-checkbox">
                <input class="form-check-input barangay-check" type="checkbox" 
                       value="${barangay.health_center_id}" 
                       id="barangay${barangay.health_center_id}">
                <label class="form-check-label" for="barangay${barangay.health_center_id}">
                    ${barangay.barangay_name}
                    ${barangay.municipality ? `, ${barangay.municipality}` : ''}
                </label>
            </div>
        `;
        container.appendChild(col);
        renderedCount++;
    });
    
    console.log(`✓ Successfully rendered ${renderedCount} barangay checkboxes`);
    updateToggleAll();
}

function updateToggleAll() {
    const checkboxes = document.querySelectorAll(".barangay-check");
    console.log(`✓ Found ${checkboxes.length} barangay checkboxes for toggle functionality`);
    
    if (checkboxes.length > 0) {
        const selectAll = document.getElementById("selectAll");
        if (selectAll) {
            selectAll.onchange = toggleAllBarangays;
            console.log("✓ Toggle all functionality attached");
        }
    }
}

function toggleAllBarangays() {
    const selectAll = document.getElementById("selectAll");
    const checkboxes = document.querySelectorAll(".barangay-check");
    
    console.log(`Toggle all: ${selectAll.checked}, Found ${checkboxes.length} checkboxes`);
    
    checkboxes.forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
    });
    
    console.log(`✓ Set ${checkboxes.length} checkboxes to: ${selectAll.checked}`);
}

function showError(message) {
    const container = document.getElementById("barangayList");
    if (container) {
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    ${message}
                </div>
            </div>
        `;
    }
}

function setReportType(type) {
    currentReportType = type;
    document.querySelectorAll(".btn-outline-primary").forEach((btn) => {
        btn.classList.remove("active");
    });
    event.target.classList.add("active");
    
    console.log("Report type set to:", type);
}

function togglePeriodFields() {
    const periodInput = document.querySelector('input[name="period"]:checked');
    
    if (!periodInput) {
        console.warn("⚠ Period input not found yet");
        return;
    }
    
    const period = periodInput.value;
    const monthDiv = document.getElementById("month-div");
    const quarterDiv = document.getElementById("quarter-div");

    if (!monthDiv || !quarterDiv) {
        console.warn("⚠ Period divs not found yet");
        return;
    }

    if (period === "monthly") {
        monthDiv.classList.remove("hidden");
        quarterDiv.classList.add("hidden");
    } else if (period === "quarterly") {
        monthDiv.classList.add("hidden");
        quarterDiv.classList.remove("hidden");
    } else {
        monthDiv.classList.add("hidden");
        quarterDiv.classList.add("hidden");
    }
}

async function generateReport() {
    const selectedBarangays = Array.from(
        document.querySelectorAll(".barangay-check:checked")
    ).map((cb) => cb.value);

    console.log("Selected barangays:", selectedBarangays);

    if (selectedBarangays.length === 0) {
        alert("Please select at least one barangay");
        return;
    }

    const period = document.querySelector('input[name="period"]:checked').value;
    const month = document.getElementById("month").value;
    const quarter = document.getElementById("quarter").value;
    const year = document.getElementById("year").value;

    const params = new URLSearchParams({
        action: currentReportType,
        period: period,
        month: month,
        quarter: quarter,
        year: year,
        barangays: selectedBarangays.join(","),
    });

    console.log("Generating report with params:", params.toString());

    try {
        const response = await fetch(`generateAPI.php?${params}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        console.log("Report data received:", result);

        if (!result.success) {
            throw new Error(result.error || 'Unknown error');
        }

        // Store data based on report type
        if (currentReportType === 'maternal') {
            prenatalData = result.data;
        } else if (currentReportType === 'child') {
            childData = result.data;
        } else if (currentReportType === 'nutrition') {
            nutritionData = result.data;
        }

        // Display the report using the title from API
        displayReport(result.title, period, month, quarter, year);

    } catch (error) {
        console.error("Error generating report:", error);
        alert('Error loading report data: ' + error.message);
    }
}

function displayReport(apiTitle, period, month, quarter, year) {
    const months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const quarterNames = ['', 'Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'];
    
    // Build period string
    let periodString = '';
    if (period === 'monthly') {
        periodString = `${months[parseInt(month)]} ${year}`;
    } else if (period === 'quarterly') {
        periodString = `${quarterNames[parseInt(quarter)]} ${year}`;
    } else if (period === 'annual') {
        periodString = `${year}`;
    }

    // Show report content
    document.getElementById('reportContent').classList.remove('hidden');

    // Hide all reports first
    document.getElementById('prenatalReport').classList.add('hidden');
    document.getElementById('childReport').classList.add('hidden');
    document.getElementById('nutritionReport').classList.add('hidden');

    // Show the selected report and populate it
    if (currentReportType === 'maternal') {
        document.getElementById('prenatalReport').classList.remove('hidden');
        document.getElementById('prenatalTitle').textContent = apiTitle || `Prenatal Care Report - ${periodString}`;
        buildPrenatalTable(prenatalData);
    } else if (currentReportType === 'child') {
        document.getElementById('childReport').classList.remove('hidden');
        document.getElementById('childTitle').textContent = apiTitle || `Child Care & Immunization Report - ${periodString}`;
        buildChildTable(childData);
    } else if (currentReportType === 'nutrition') {
        document.getElementById('nutritionReport').classList.remove('hidden');
        document.getElementById('nutritionTitle').textContent = apiTitle || `Nutrition Services Report - ${periodString}`;
        buildNutritionTable(nutritionData);
    }

    // Scroll to report
    setTimeout(() => {
        document.getElementById('reportContent').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

// Build Prenatal Table with Age Range Groups (MATCHES MIDWIFE STYLE)
function buildPrenatalTable(data) {
    const table = document.getElementById('prenatalTable');
    table.innerHTML = `
        <thead class='table-light'>
            <tr>
                <th style="width: 60%;">Indicators</th>
                <th class="text-center">10–14 years old</th>
                <th class="text-center">15–19 years old</th>
                <th class="text-center">20–49 years old</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    `;

    const tbody = table.querySelector('tbody');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No data available</td></tr>';
        return;
    }

    data.forEach(row => {
        const tr = document.createElement('tr');
        const indentStyle = row.indent ? 'padding-left: 30px;' : '';
        tr.innerHTML = `
            <td style="${indentStyle}">${row.indicator}</td>
            <td class="text-center">${row.age_10_14 || 0}</td>
            <td class="text-center">${row.age_15_19 || 0}</td>
            <td class="text-center">${row.age_20_49 || 0}</td>
            <td class="text-center fw-bold">${row.grand_total || row.total || 0}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Build Child Care Table with Gender Groups (MATCHES MIDWIFE STYLE)
function buildChildTable(data) {
    const table = document.getElementById('childTable');
    table.innerHTML = `
        <thead class='table-light'>
            <tr>
                <th style="width: 60%;">Indicators</th>
                <th class="text-center">Male</th>
                <th class="text-center">Female</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    `;

    const tbody = table.querySelector('tbody');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>';
        return;
    }

    data.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.indicator}</td>
            <td class="text-center">${row.male || 0}</td>
            <td class="text-center">${row.female || 0}</td>
            <td class="text-center fw-bold">${row.total || 0}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Build Nutrition Table with Gender Groups (MATCHES MIDWIFE STYLE)
function buildNutritionTable(data) {
    const table = document.getElementById('nutritionTable');
    table.innerHTML = `
        <thead class='table-light'>
            <tr>
                <th style="width: 60%;">Indicators</th>
                <th class="text-center">Male</th>
                <th class="text-center">Female</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    `;

    const tbody = table.querySelector('tbody');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>';
        return;
    }

    data.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.indicator}</td>
            <td class="text-center">${row.male || 0}</td>
            <td class="text-center">${row.female || 0}</td>
            <td class="text-center fw-bold">${row.total || 0}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Log when script loads
console.log("✓ report_admin.js loaded successfully");

// Make functions globally available
window.initReportPage = initReportPage;
window.manualInitReportPage = function() {
    console.log("🔄 Manual initialization triggered");
    loadAttempts = 0;
    initReportPage();
};