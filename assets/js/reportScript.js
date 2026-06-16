let currentReportType = 'maternal';
let prenatalData = [];
let childData = [];
let nutritionData = [];
let retryCount = 0;
const MAX_RETRIES = 20;


document.addEventListener('DOMContentLoaded', function() {
    initializeReportControls();
});

function initializeReportControls() {

    const periodRadios = document.querySelectorAll('input[name="period"]');
    
    if (periodRadios.length > 0) {
        periodRadios.forEach(radio => {
            radio.addEventListener('change', togglePeriodFields);
        });
        
    
        togglePeriodFields();
        console.log('✓ Report controls initialized successfully');
        retryCount = 0; 
    } else {
        retryCount++;
        
        if (retryCount < MAX_RETRIES) {
        
            if (retryCount % 5 === 0) {
                console.log(`Waiting for report controls... (${retryCount}/${MAX_RETRIES})`);
            }
            setTimeout(initializeReportControls, 100);
        } else {
        
            console.log('Report controls not found - not a reports page');
        }
    }
}


function togglePeriodFields() {
    const periodInput = document.querySelector('input[name="period"]:checked');
    
    if (!periodInput) {
        console.error('No period radio button is checked');
        return;
    }
    
    const period = periodInput.value;
    const monthDiv = document.getElementById('month-div');
    const quarterDiv = document.getElementById('quarter-div');
    
    if (!monthDiv || !quarterDiv) {
        console.error('Month or Quarter div not found');
        return;
    }
    
    if (period === 'monthly') {
        monthDiv.classList.remove('hidden');
        quarterDiv.classList.add('hidden');
    } else if (period === 'quarterly') {
        monthDiv.classList.add('hidden');
        quarterDiv.classList.remove('hidden');
    } else {
      
        monthDiv.classList.add('hidden');
        quarterDiv.classList.add('hidden');
    }
}


function setReportType(type) {
    currentReportType = type;
    
 
    document.querySelectorAll('.btn-outline-primary').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

async function generateReport() {
    const period = document.querySelector('input[name="period"]:checked')?.value;
    const month = document.getElementById('month')?.value;
    const quarter = document.getElementById('quarter')?.value;
    const year = document.getElementById('year')?.value;

    if (!period || !year) {
        alert('Please select all required fields');
        return;
    }

 
    let action = '';
    if (currentReportType === 'maternal') action = 'prenatal';
    else if (currentReportType === 'child') action = 'child';
    else if (currentReportType === 'nutrition') action = 'nutrition';

    try {
        const url = `/rhusystem/midwife/reports/generateAPI.php?action=${action}&period=${period}&month=${month}&quarter=${quarter}&year=${year}`;
        const response = await fetch(url);
        
        if (!response.ok) {
            alert('Error: ' + response.statusText);
            return;
        }

        const result = await response.json();
        
        if (!result.success) {
            alert('Error: ' + (result.error || 'Unknown error'));
            return;
        }


        if (currentReportType === 'maternal') {
            prenatalData = result.data;
        } else if (currentReportType === 'child') {
            childData = result.data;
        } else if (currentReportType === 'nutrition') {
            nutritionData = result.data;
        }

     
        displayReport(period, month, quarter, year);

    } catch (error) {
        console.error('Error fetching data:', error);
        alert('Error loading report data: ' + error.message);
    }
}

function displayReport(period, month, quarter, year) {
    const months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const quarterNames = ['', 'Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'];
    
    let reportTitle = '';
    if (period === 'monthly') {
        reportTitle = `${months[parseInt(month)]} ${year}`;
    } else if (period === 'quarterly') {
        reportTitle = `${quarterNames[parseInt(quarter)]} ${year}`;
    } else if (period === 'annual') {
        reportTitle = `Annual Report ${year}`;
    } else {
        reportTitle = `Year ${year}`;
    }


    document.getElementById('reportContent').classList.remove('hidden');

    document.getElementById('prenatalReport').classList.add('hidden');
    document.getElementById('childReport').classList.add('hidden');
    document.getElementById('nutritionReport').classList.add('hidden');

    if (currentReportType === 'maternal') {
        document.getElementById('prenatalReport').classList.remove('hidden');
        document.getElementById('prenatalTitle').textContent = `Prenatal Care Report - ${reportTitle}`;
        buildPrenatalTable(prenatalData);
    } else if (currentReportType === 'child') {
        document.getElementById('childReport').classList.remove('hidden');
        document.getElementById('childTitle').textContent = `Child Care & Immunization Report - ${reportTitle}`;
        buildChildTable(childData);
    } else if (currentReportType === 'nutrition') {
        document.getElementById('nutritionReport').classList.remove('hidden');
        document.getElementById('nutritionTitle').textContent = `Nutrition Services Report - ${reportTitle}`;
        buildNutritionTable(nutritionData);
    }

    setTimeout(() => {
        document.getElementById('reportContent').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

function buildPrenatalTable(data) {
    const table = document.getElementById('prenatalTable');
    table.innerHTML = `
        <thead  class='table-light'>
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
            <td class="text-center fw-bold">${row.total || 0}</td>
        `;
        tbody.appendChild(tr);
    });
}

function buildChildTable(data) {
    const table = document.getElementById('childTable');
    table.innerHTML = `
        <thead>
            <tr>
                <th style="width: 60%;">Indicators</th>
                <th class="text-center" style="width: 13.33%;">Male</th>
                <th class="text-center" style="width: 13.33%;">Female</th>
                <th class="text-center" style="width: 13.33%;">Total</th>
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

function buildNutritionTable(data) {
    const table = document.getElementById('nutritionTable');
    table.innerHTML = `
        <thead>
            <tr>
                <th style="width: 60%;">Indicators</th>
                <th class="text-center" style="width: 13.33%;">Male</th>
                <th class="text-center" style="width: 13.33%;">Female</th>
                <th class="text-center" style="width: 13.33%;">Total</th>
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