// admin_script.js - Admin Dashboard JavaScript Functions

console.log("Admin scripts loaded successfully");

// ============================================================
// CRITICAL FIX: Bootstrap Safety Functions (ADD AT TOP)
// ============================================================
function waitForBootstrap(callback) {
  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    callback();
  } else {
    console.warn("⚠️ Bootstrap not yet loaded, retrying...");
    setTimeout(() => waitForBootstrap(callback), 100);
  }
}

function showModalSafely(modalId) {
  const modalElement = document.getElementById(modalId);
  
  if (!modalElement) {
    console.error(`❌ Modal element not found: ${modalId}`);
    return;
  }

  waitForBootstrap(() => {
    try {
      const modal = new bootstrap.Modal(modalElement);
      modal.show();
    } catch (error) {
      console.error(`Error showing modal ${modalId}:`, error);
      AdminUtils.showError('Error opening dialog. Please refresh the page.');
    }
  });
}
// ============================================================

// Admin-specific utility functions
const AdminUtils = {
  // Show success notification
  showSuccess: function (message) {
    if (typeof swal !== "undefined") {
      swal("Success!", message, "success");
    } else {
      alert("Success: " + message);
    }
  },

  // Show error notification
  showError: function (message) {
    if (typeof swal !== "undefined") {
      swal("Error!", message, "error");
    } else {
      alert("Error: " + message);
    }
  },

  // Confirm action with sweetalert
  confirmAction: function (message, callback) {
    if (typeof swal !== "undefined") {
      swal({
        title: "Are you sure?",
        text: message,
        icon: "warning",
        buttons: true,
        dangerMode: true,
      }).then((willProceed) => {
        if (willProceed) {
          callback();
        }
      });
    } else {
      if (confirm(message)) {
        callback();
      }
    }
  },

  // Format date for display
  formatDate: function (dateString) {
    const options = {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    };
    return new Date(dateString).toLocaleDateString("en-US", options);
  },

  // Validate email format
  validateEmail: function (email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  },
};

// Make functions globally available
window.AdminUtils = AdminUtils;

// Main function to load content via AJAX and manage active links
function loadPage(page, clickedElement) {
  console.log("loadPage called with:", page);
  
  const mainContent = document.getElementById("main-content");
  if (!mainContent) {
    console.error("main-content element not found!");
    return;
  }
  
  mainContent.style.opacity = "0.5";

  document.querySelectorAll(".sidebar-nav-link").forEach((link) => {
    link.classList.remove("active");
  });

  if (clickedElement) {
    clickedElement.classList.add("active");
  }

  fetch(page)
    .then((response) => {
      console.log("Response status:", response.status);
      if (!response.ok) {
        throw new Error("Network response was not ok: " + response.status);
      }
      return response.text();
    })
    .then((data) => {
      console.log("Page loaded successfully");
      mainContent.innerHTML = data;
      mainContent.style.opacity = "1";
      mainContent.classList.add("fade-in");

      setupFormNavigation();
      initCreateMidwifePage();
      initManageMidwivesPage();
      initEditMidwifePage();
      
      if (page === 'report_dashboard.php' || page.includes('report_dashboard.php')) {
        console.log("🎯 Report dashboard detected, initializing...");
        setTimeout(function() {
          if (typeof window.initReportPage === 'function') {
            console.log("✅ Calling initReportPage()");
            window.initReportPage();
          } else {
            console.warn("⚠️ initReportPage function not found");
          }
        }, 100);
      }
    })
    .catch((error) => {
      console.error("Error loading page:", error);
      mainContent.innerHTML = `
        <div class="content-card text-center">
          <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Error loading page: ${error.message}
          </div>
          <button class="btn btn-primary mt-3" onclick="loadPage('manage_midwives.php')">
            Return to Midwives List
          </button>
        </div>`;
      mainContent.style.opacity = "1";
    });
}

// Special function for loading home page
function loadHomePage() {
  const homeLink = document.querySelector(
    '.sidebar-nav-link[onclick*="loadHomePage"]'
  );
  loadPage("home.php", homeLink);
}

// Handles the navigation between tabs in forms
function setupFormNavigation() {
  document.querySelectorAll(".js-next_btn").forEach((button) => {
    button.addEventListener("click", () => {
      var activeTab = document.querySelector(".nav-tabs .nav-link.active");
      var nextTab = activeTab.parentElement.nextElementSibling;
      if (nextTab) {
        var nextTabLink = nextTab.querySelector(".nav-link");
        new bootstrap.Tab(nextTabLink).show();
        scrollToTop();
      }
    });
  });

  document.querySelectorAll(".js-back_btn").forEach((button) => {
    button.addEventListener("click", () => {
      var activeTab = document.querySelector(".nav-tabs .nav-link.active");
      var prevTab = activeTab.parentElement.previousElementSibling;
      if (prevTab) {
        var prevTabLink = prevTab.querySelector(".nav-link");
        new bootstrap.Tab(prevTabLink).show();
        scrollToTop();
      }
    });
  });
}

// Smooth scroll helper
function scrollToTop() {
  const mainContainer =
    document.querySelector(".main-container") ||
    document.querySelector(".main-content");
  if (mainContainer) {
    mainContainer.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
}

/* ===========================================================
   Create Midwife Page Initializer - FIXED
=========================================================== */
function initCreateMidwifePage() {
  const form = document.getElementById("midwifeForm");
  if (!form) return;

  console.log("Init: Midwife form found. Attaching AJAX handler."); 

  const submitBtn = document.getElementById("submitBtn");
  const messageArea = document.getElementById("messageArea");
  const confirmPassword = document.getElementById("confirmPassword");
  const password = document.getElementById("password");

  if (confirmPassword && password) {
    confirmPassword.addEventListener("input", function () {
      this.setCustomValidity(
        this.value !== password.value ? "Passwords do not match" : ""
      );
    });
  }

  if (form.getAttribute('data-handler-attached') === 'true') {
      return; 
  }
  
  const handler = function (e) {
    console.log("Form Submit Handler running. Preventing default action...");
    e.preventDefault();
    e.stopPropagation();

    const formData = new FormData(form);
    formData.append("ajax_submit", "1");

    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
      '<i class="bi bi-hourglass-split me-2"></i>Creating...';
    submitBtn.disabled = true;

    fetch(form.getAttribute("action"), {
      method: "POST",
      body: formData,
      headers: { "X-Requested-With": "XMLHttpRequest" },
    })
    .then((response) => {
      console.log("Response status:", response.status);
      console.log("Response headers Content-Type:", response.headers.get('content-type'));
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      console.log("Parsed JSON response:", data);
      
      if (!data || typeof data !== 'object') {
        throw new Error("Invalid response object received");
      }

      if (data.success) {
        console.log("SUCCESS: Midwife created successfully");
        
        // Use Swal (capital S) for SweetAlert2, fallback to showMsg if not available
        if (typeof Swal !== "undefined") {
          Swal.fire({
            title: "Success!", 
            text: data.message.replace(/<br>/g, '\n').replace(/<[^>]*>/g, ''), 
            icon: "success",
            confirmButtonText: "OK"
          }).then(() => {
            const sel = document.getElementById("health_center_id");
            const used = sel ? sel.value : null;

            form.reset();

            if (sel && used) {
              const opt = sel.querySelector(`option[value="${used}"]`);
              if (opt && !/Occupied\)$/i.test(opt.textContent)) {
                opt.disabled = true;
                opt.textContent = opt.textContent.trim() + " (Occupied)";
              }
            }

            if (typeof loadPage === "function") {
              loadPage("manage_midwives.php");
            }
          });
        } else {
          showMsg("success", data.message || "Midwife account created successfully!");
          form.reset();
          
          const sel = document.getElementById("health_center_id");
          const used = sel ? sel.value : null;
          if (sel && used) {
            const opt = sel.querySelector(`option[value="${used}"]`);
            if (opt && !/Occupied\)$/i.test(opt.textContent)) {
              opt.disabled = true;
              opt.textContent = opt.textContent.trim() + " (Occupied)";
            }
          }
        }
      } else {
        console.log("ERROR: Creation failed - " + data.message);
        showMsg("danger", data.message || "Failed to create account.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMsg("danger", "Error: " + error.message);
    })
    .finally(() => {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    });

    function showMsg(type, message) {
      messageArea.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show">
          <i class="bi ${
            type === "success"
              ? "bi-check-circle"
              : "bi-exclamation-triangle"
          } me-2"></i>
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      `;
      messageArea.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
  };

  form.addEventListener("submit", handler);
  form.setAttribute('data-handler-attached', 'true');
}

/* ===========================================================
   Manage Midwives Page Initializer (FIXED)
=========================================================== */
function initManageMidwivesPage() {
  const manageTable = document.querySelector('table');
  if (!manageTable) return;

  console.log("Init: Manage Midwives page loaded");

  // Initialize Bootstrap tooltips SAFELY
  waitForBootstrap(() => {
    try {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
      );
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        try {
          new bootstrap.Tooltip(tooltipTriggerEl);
        } catch (error) {
          console.warn("Could not initialize tooltip:", error);
        }
      });
    } catch (error) {
      console.warn("Tooltip initialization failed:", error);
    }
  });

  // Set up reassign modal functionality
  const confirmReassignBtn = document.getElementById('confirmReassign');
  if (confirmReassignBtn) {
    confirmReassignBtn.addEventListener('click', function() {
      const newBarangayId = document.getElementById('newBarangay').value;
      
      if (!newBarangayId) {
        AdminUtils.showError('Please select a barangay');
        return;
      }

      performReassignMidwife(window.currentMidwifeId, newBarangayId);
    });
  }
}

// Global variables for midwife management
window.currentMidwifeId = null;

// ============================================================
// MIDWIFE MANAGEMENT FUNCTIONS (FIXED VERSIONS)
// ============================================================

function editMidwife(userId) {
  console.log("Edit button clicked for user ID:", userId);
  
  if (typeof loadPage === "function") {
    loadPage('edit_midwife.php?id=' + userId);
  } else {
    console.error("loadPage function not found");
    AdminUtils.showError("Cannot load edit page");
  }
}

function reassignMidwife(userId, midwifeName) {
  window.currentMidwifeId = userId;
  document.getElementById("reassignMidwifeName").textContent = midwifeName;
  
  showModalSafely("reassignModal");
}

function performReassignMidwife(userId, newBarangayId) {
  const formData = new FormData();
  formData.append("user_id", userId);
  formData.append("new_barangay_id", newBarangayId);
  formData.append("action", "reassign_barangay");

  fetch("process_manage_midwife.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        AdminUtils.showSuccess(data.message);
   
        const modalElement = document.getElementById("reassignModal");
        if (modalElement) {
          try {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
              modal.hide();
            }
          } catch (error) {
            console.warn("Could not close modal:", error);
          }
        }
        
        if (typeof loadPage === "function") {
          setTimeout(() => {
            loadPage("manage_midwives.php");
          }, 500);
        }
      } else {
        AdminUtils.showError(data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      AdminUtils.showError("Network error occurred");
    });
}


/* ===========================================================
   Edit Midwife Page Initializer
=========================================================== */
function initEditMidwifePage() {
  const user_id = document.getElementById('user_id');
  const firstName = document.getElementById('firstName');
  
  if (!user_id || !firstName) return;

  console.log("🚀 INITIALIZING EDIT MIDWIFE PAGE");


  function updateMidwife() {
    console.log("=== UPDATE MIDWIFE FUNCTION CALLED ===");
    
    const user_id = document.getElementById('user_id').value;
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('email').value;
    const health_center_id = document.getElementById('health_center_id').value;
  
    console.log("Form values:", {
      user_id, firstName, lastName, email, health_center_id
    });

    if (!firstName || !lastName || !email || !health_center_id) {
      showMessage('danger', 'Please fill in all required fields');
      return;
    }

    const submitBtn = document.getElementById('updateBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('user_id', user_id);
    formData.append('firstName', firstName);
    formData.append('lastName', lastName);
    formData.append('email', email);
    formData.append('health_center_id', health_center_id);

    console.log("Sending AJAX request to process_edit_midwife.php");

    fetch('process_edit_midwife.php', {
      method: "POST",
      body: formData
    })
    .then(response => {
      console.log("Response status:", response.status);
      return response.text();
    })
    .then(text => {
      console.log("Raw response:", text);
      
      let data;
      try {
        data = JSON.parse(text.trim());
      } catch (e) {
        console.error("JSON parse error:", e);
        throw new Error("Invalid server response");
      }
      
      if (data.success) {
        console.log("SUCCESS: Update completed");
        showMessage('success', data.message);
        
        setTimeout(() => {
          console.log("Redirecting to manage midwives");
          if (typeof loadPage === "function") {
            loadPage('manage_midwives.php');
          } else {
            console.error("loadPage function not available");
          }
        }, 1500);
        
      } else {
        console.log("ERROR: Update failed");
        showMessage('danger', data.message);
      }
    })
    .catch(error => {
      console.error("Fetch error:", error);
      showMessage('danger', 'Network error: ' + error.message);
    })
    .finally(() => {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    });
  }

  function goBack() {
    console.log("Going back to manage midwives");
    if (typeof loadPage === "function") {
      loadPage('manage_midwives.php');
    } else {
      console.error("loadPage function not available");
    }
  }

  function showMessage(type, message) {
    const messageArea = document.getElementById('messageArea');
    if (messageArea) {
      messageArea.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show">
          <i class="bi ${type === "success" ? "bi-check-circle" : "bi-exclamation-triangle"} me-2"></i>
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      `;
      messageArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  const updateBtn = document.getElementById('updateBtn');
  const backBtn = document.getElementById('backBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  
  if (updateBtn) {
    console.log("✅ Attaching click listener to update button");
    updateBtn.addEventListener('click', function(e) {
      console.log("🎯 UPDATE BUTTON CLICKED!");
      e.preventDefault();
      updateMidwife();
    });
    
    updateBtn.onclick = function(e) {
      console.log("🎯 UPDATE BUTTON CLICKED (onclick)!");
      e.preventDefault();
      updateMidwife();
    };
  } else {
    console.error("❌ Update button not found!");
  }
  
  if (backBtn) {
    backBtn.addEventListener('click', function(e) {
      e.preventDefault();
      goBack();
    });
    backBtn.onclick = function(e) {
      e.preventDefault();
      goBack();
    };
  }
  
  if (cancelBtn) {
    cancelBtn.addEventListener('click', function(e) {
      e.preventDefault();
      goBack();
    });
    cancelBtn.onclick = function(e) {
      e.preventDefault();
      goBack();
    };
  }
  
  console.log("✅ Edit Midwife page fully initialized");
  console.log("Buttons found:", {
    updateBtn: !!updateBtn,
    backBtn: !!backBtn,
    cancelBtn: !!cancelBtn
  });
}

// Initialize admin-specific features when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  console.log("Admin dashboard initialized");
  loadHomePage();
});