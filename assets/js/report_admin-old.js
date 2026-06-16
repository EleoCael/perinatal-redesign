let currentReportType = "maternal";
let barangays = [];
let loadAttempts = 0;
const MAX_LOAD_ATTEMPTS = 10;

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
            // Try again after a short delay
            setTimeout(initReportPage, 200);
            return;
        } else {
            console.error("❌ Failed to find barangayList after maximum attempts");
            return;
        }
    }
    
    console.log("✓ Elements found, loading barangays...");
    loadAttempts = 0; // Reset counter
    loadBarangays();
    togglePeriodFields();
}

// Wait for DOM, then initialize
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM ready, waiting for page content...");
    initReportPage();
});

// Also provide a manual initialization function
function manualInitReportPage() {
    console.log("🔄 Manual initialization triggered");
    loadAttempts = 0;
    initReportPage();
}

function loadBarangays() {
    // Try different possible paths for the API
    const apiPaths = [
        "generateAPI.php?action=getBarangays",        // Same directory
        "../admin/generateAPI.php?action=getBarangays", // Up one level then admin
        "./generateAPI.php?action=getBarangays",      // Explicit current directory
        "admin/generateAPI.php?action=getBarangays"   // Down to admin directory
    ];
    
    let currentPathIndex = 0;
    
    function tryFetch(pathIndex) {
        if (pathIndex >= apiPaths.length) {
            console.error("All API paths failed");
            showError("Could not connect to server. Please check the API endpoint. Expected paths tried: " + apiPaths.join(", "));
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
                return response.text(); // Get text first to see raw response
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
                // Try next path
                tryFetch(pathIndex + 1);
            });
    }
    
    tryFetch(currentPathIndex);
}

function renderBarangayList() {
    const container = document.getElementById("barangayList");
    
    if (!container) {
        console.error("❌ CRITICAL: barangayList container not found in DOM!");
        console.log("Available elements with IDs:", 
            Array.from(document.querySelectorAll('[id]')).map(el => el.id)
        );
        
        // Try to reinitialize
        console.log("Attempting to reinitialize...");
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
        // Validate barangay data
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
    
    // Update the toggle all functionality after rendering
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
        } else {
            console.warn("⚠ selectAll checkbox not found");
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

function updateSelectedBarangays() {
    const selectedBarangays = Array.from(
        document.querySelectorAll(".barangay-check:checked")
    ).map((cb) => cb.value);

    console.log("Currently selected barangays:", selectedBarangays);
    return selectedBarangays;
}

function showError(message) {
    const container = document.getElementById("barangayList");
    if (container) {
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    ${message}
                    <hr>
                    <small>Check the browser console (F12) for detailed error information.</small>
                </div>
            </div>
        `;
    } else {
        console.error("Cannot show error - barangayList container not found!");
        alert(message);
    }
}

// Manual refresh function - you can call it from browser console
function refreshBarangays() {
    console.log("🔄 Manually refreshing barangays...");
    loadBarangays();
}

// Test function - you can call this from browser console to check API
function testBarangayAPI() {
    const testUrls = [
        "generateAPI.php?action=getBarangays",
        "../admin/generateAPI.php?action=getBarangays",
        "./generateAPI.php?action=getBarangays",
        "admin/generateAPI.php?action=getBarangays"
    ];
    
    console.log("🧪 Testing all possible API URLs...");
    
    testUrls.forEach((testUrl, index) => {
        console.log(`\n--- Testing URL ${index + 1}: ${testUrl} ---`);
        
        fetch(testUrl)
            .then(response => {
                console.log(`Response ${index + 1}:`, response.status, response.statusText);
                return response.text();
            })
            .then(text => {
                console.log(`Raw response ${index + 1}:`, text.substring(0, 200));
                try {
                    const data = JSON.parse(text);
                    console.log(`✓ Parsed JSON ${index + 1}:`, data);
                    if (data.success) {
                        console.log(`✓✓ SUCCESS with URL ${index + 1}! This is the correct path.`);
                    }
                } catch (e) {
                    console.error(`✗ Failed to parse JSON ${index + 1}:`, e.message);
                }
            })
            .catch(error => {
                console.error(`✗ Fetch error ${index + 1}:`, error.message);
            });
    });
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
    
    // Safety check
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

function generateReport() {
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

    // Show loading state
    const reportContent = document.getElementById("reportContent");
    reportContent.classList.remove("hidden");
    reportContent.innerHTML = `
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Generating report...</p>
        </div>
    `;

    fetch(`generateAPI.php?${params}`)
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            console.log("Report data received:", data);
           // console.log('Report data received:', response);
            //console.log('First row data:', response.data[0]);
            //console.log('Second row data:', response.data[1]);
            if (data.success) {
                displayReport(data.data, data.title);
            } else {
                throw new Error(data.error || "Unknown error");
            }
        })
        .catch((error) => {
            console.error("Error generating report:", error);
            reportContent.innerHTML = `
                <div class="alert alert-danger">
                    Failed to generate report: ${error.message}
                </div>
            `;
        });
}

function displayReport(data, title) {
    const reportContent = document.getElementById("reportContent");
    reportContent.classList.remove("hidden");

    let html = `<div class="card"><div class="card-body">`;
    html += `<h4 class="mb-4">${title}</h4>`;

    if (currentReportType === "maternal") {
        html += generatePrenatalTable(data);
    } else if (currentReportType === "child") {
        html += generateChildTable(data);
    } else if (currentReportType === "nutrition") {
        html += generateNutritionTable(data);
    }

    html += `</div></div>`;
    reportContent.innerHTML = html;
    
    // Scroll to report
    reportContent.scrollIntoView({ behavior: 'smooth' });
}

function generatePrenatalTable(data) {
    let html = '<div class="table-responsive"><table class="table table-bordered table-sm">';
    html += "<thead class='table-light'><tr>";
    html += '<th>Indicators</th>';
    html += '<th class="text-center">10–14 years old</th>';
    html += '<th class="text-center">15–19 years old</th>';
    html += '<th class="text-center">20–49 years old</th>';
    html += '<th class="text-center">Total</th>';
    html += "</tr></thead><tbody>";

    data.forEach((row) => {
        html += `<tr>`;
        const indentStyle = row.indent ? 'padding-left: 30px;' : '';
        html += `<td style="${indentStyle}">${row.indicator}</td>`;

        // FIXED: Use the correct property names from PHP
        html += `<td class="text-center">${row.age_10_14 || 0}</td>`;
        html += `<td class="text-center">${row.age_15_19 || 0}</td>`;
        html += `<td class="text-center">${row.age_20_49 || 0}</td>`;
        html += `<td class="text-center fw-bold">${row.grand_total || 0}</td>`;

        html += "</tr>";
    });

    html += "</tbody></table></div>";
    return html;
}

function generateChildTable(data) {
    let html = '<div class="table-responsive"><table class="table table-bordered table-sm table-hover">';
    html += "<thead class='table-light'><tr>";
    html += "<th>Indicators</th>";
    html += "<th class='text-center'>Male</th>";
    html += "<th class='text-center'>Female</th>";
    html += "<th class='text-center'>Total</th>";
    html += "</tr></thead><tbody>";

    data.forEach((row) => {
        html += "<tr>";
        html += `<td>${row.indicator}</td>`;
        html += `<td class="text-center">${row.male || 0}</td>`;
        html += `<td class="text-center">${row.female || 0}</td>`;
        html += `<td class="text-center fw-bold">${row.total || 0}</td>`;
        html += "</tr>";
    });

    html += "</tbody></table></div>";
    return html;
}

function generateNutritionTable(data) {
    return generateChildTable(data); // Same format as child report
}

// Log when script loads
console.log("✓ report_admin.js loaded successfully");

// Make init function globally available
window.initReportPage = initReportPage;
window.manualInitReportPage = manualInitReportPage;