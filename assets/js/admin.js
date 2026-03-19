  
// Admin panel JavaScript

// Toggle sidebar on mobile
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}

// Confirm delete actions
function confirmDelete(message = 'Are you sure?') {
    return confirm(message);
}

// Export data to CSV
function exportToCSV(data, filename) {
    const csv = data.map(row => 
        row.map(cell => 
            `"${cell.toString().replace(/"/g, '""')}"`
        ).join(',')
    ).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Chart initialization (if using charts)
function initDashboardCharts() {
    // Revenue chart
    const revenueCtx = document.getElementById('revenue-chart');
    if(revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Revenue (KSh)',
                    data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
                    borderColor: '#2c3e50',
                    backgroundColor: 'rgba(44, 62, 80, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    // Package distribution chart
    const packagesCtx = document.getElementById('packages-chart');
    if(packagesCtx) {
        new Chart(packagesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Basic', 'Daily', 'Weekly', 'Monthly'],
                datasets: [{
                    data: [30, 45, 15, 10],
                    backgroundColor: [
                        '#3498db',
                        '#e67e22',
                        '#27ae60',
                        '#9b59b6'
                    ]
                }]
            }
        });
    }
}

// Filter table data
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const tr = table.getElementsByTagName('tr');
    
    for(let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let found = false;
        
        for(let j = 0; j < td.length; j++) {
            if(td[j]) {
                const textValue = td[j].textContent || td[j].innerText;
                if(textValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        tr[i].style.display = found ? '' : 'none';
    }
}

// Date range picker
function initDateRangePicker() {
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    
    if(startDate && endDate) {
        startDate.addEventListener('change', function() {
            endDate.min = this.value;
        });
    }
}

// Bulk actions
function selectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.select-item');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function getSelectedIds() {
    const selected = [];
    document.querySelectorAll('.select-item:checked').forEach(cb => {
        selected.push(cb.value);
    });
    return selected;
}

// Initialize on document load
document.addEventListener('DOMContentLoaded', function() {
    initDashboardCharts();
    initDateRangePicker();
    
    // Add active class to current menu item
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        if(link.getAttribute('href') === currentPath.split('/').pop()) {
            link.classList.add('active');
        }
    });
});