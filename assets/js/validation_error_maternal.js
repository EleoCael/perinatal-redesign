document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (el) {
    el.removeAttribute("data-bs-toggle");
  });
});

function validateName(name) {
  const nameRegex = /^[A-Za-z\s\-']+$/;
  return nameRegex.test(name);
}

function validateContactNumber(contact) {
  const contactRegex = /^09\d{9}$/;
  return contactRegex.test(contact);
}

function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function validateAgeBracket(age, bracket) {
  if (!bracket) return false;
  const ageNum = parseInt(age);
  if (bracket === "10-14") return ageNum >= 10 && ageNum <= 14;
  else if (bracket === "15-19") return ageNum >= 15 && ageNum <= 19;
  else if (bracket === "20-49") return ageNum >= 20 && ageNum <= 49;
  return false;
}

function validateFutureDate(dateString) {
  if (!dateString) return true;
  const selectedDate = new Date(dateString);
  const today = new Date();
  return selectedDate <= today;
}

// ─── Validates all Patient Details required fields across the whole form ───
function validatePatientDetails() {
  let isValid = true;
  let errorMessages = [];

  const form = document.querySelector("form");

  // First Name
  const firstNameInput = form.querySelector('input[name="first_name"]');
  if (firstNameInput) {
    firstNameInput.style.border = "";
    document.getElementById("error_first_name").textContent = "";
    if (!firstNameInput.value.trim()) {
      firstNameInput.style.border = "2px solid #dc3545";
      document.getElementById("error_first_name").textContent = "First name is required";
      isValid = false;
      errorMessages.push("First name is required");
    } else if (!validateName(firstNameInput.value)) {
      firstNameInput.style.border = "2px solid #dc3545";
      document.getElementById("error_first_name").textContent = "First name must contain only letters";
      isValid = false;
      errorMessages.push("First name must contain only letters");
    }
  }

  // Last Name
  const lastNameInput = form.querySelector('input[name="last_name"]');
  if (lastNameInput) {
    lastNameInput.style.border = "";
    document.getElementById("error_last_name").textContent = "";
    if (!lastNameInput.value.trim()) {
      lastNameInput.style.border = "2px solid #dc3545";
      document.getElementById("error_last_name").textContent = "Last name is required";
      isValid = false;
      errorMessages.push("Last name is required");
    } else if (!validateName(lastNameInput.value)) {
      lastNameInput.style.border = "2px solid #dc3545";
      document.getElementById("error_last_name").textContent = "Last name must contain only letters";
      isValid = false;
      errorMessages.push("Last name must contain only letters");
    }
  }

  // Contact Number
  const contactInput = form.querySelector('input[name="contact_number"]');
  if (contactInput) {
    contactInput.style.border = "";
    document.getElementById("error_contact_number").textContent = "";
    if (!contactInput.value.trim()) {
      contactInput.style.border = "2px solid #dc3545";
      document.getElementById("error_contact_number").textContent = "Contact number is required";
      isValid = false;
      errorMessages.push("Contact number is required");
    } else if (!validateContactNumber(contactInput.value)) {
      contactInput.style.border = "2px solid #dc3545";
      document.getElementById("error_contact_number").textContent = "Must be 11 digits starting with 09";
      isValid = false;
      errorMessages.push("Contact number must be 11 digits starting with 09");
    }
  }

  // Age
  const ageInput = form.querySelector('input[name="age"]');
  const ageBracketInput = form.querySelector('input[name="age_bracket"]:checked');
  if (ageInput) {
    ageInput.style.border = "";
    document.getElementById("error_age").textContent = "";
    document.getElementById("error_age_bracket").textContent = "";
    const ageValue = parseInt(ageInput.value);
    if (ageInput.value === "" || isNaN(ageValue) || ageValue < 10 || ageValue > 49) {
      ageInput.style.border = "2px solid #dc3545";
      document.getElementById("error_age").textContent = "Age must be between 10-49";
      isValid = false;
      errorMessages.push("Age must be between 10-49");
    } else if (!ageBracketInput) {
      document.getElementById("error_age_bracket").textContent = "Please select an age bracket";
      isValid = false;
      errorMessages.push("Age bracket is required");
    } else if (!validateAgeBracket(ageInput.value, ageBracketInput.value)) {
      ageInput.style.border = "2px solid #dc3545";
      document.getElementById("error_age").textContent = "Age does not match selected bracket";
      document.getElementById("error_age_bracket").textContent = "Age bracket does not match age";
      isValid = false;
      errorMessages.push("Age must match the selected age bracket");
    }
  }

  // Date of Registration
  const dateRegInput = form.querySelector('input[name="date_of_registration"]');
  if (dateRegInput) {
    dateRegInput.style.border = "";
    document.getElementById("error_date_of_registration").textContent = "";
    if (!dateRegInput.value) {
      dateRegInput.style.border = "2px solid #dc3545";
      document.getElementById("error_date_of_registration").textContent = "Date of registration is required";
      isValid = false;
      errorMessages.push("Date of registration is required");
    } else if (!validateFutureDate(dateRegInput.value)) {
      dateRegInput.style.border = "2px solid #dc3545";
      document.getElementById("error_date_of_registration").textContent = "Date cannot be in the future";
      isValid = false;
      errorMessages.push("Date of registration cannot be in the future");
    }
  }

  // Family Serial Number
  const familySerialInput = form.querySelector('input[name="family_serial_number"]');
  if (familySerialInput) {
    familySerialInput.style.border = "";
    document.getElementById("error_family_serial_number").textContent = "";
    if (!familySerialInput.value.trim()) {
      familySerialInput.style.border = "2px solid #dc3545";
      document.getElementById("error_family_serial_number").textContent = "Family serial number is required";
      isValid = false;
      errorMessages.push("Family serial number is required");
    }
  }

  // Socio-Economic Status
  const socioEconSelect = form.querySelector('select[name="socio_economic_status"]');
  if (socioEconSelect) {
    socioEconSelect.style.border = "";
    document.getElementById("error_socio_economic_status").textContent = "";
    if (!socioEconSelect.value) {
      socioEconSelect.style.border = "2px solid #dc3545";
      document.getElementById("error_socio_economic_status").textContent = "Please select socio-economic status";
      isValid = false;
      errorMessages.push("Socio-economic status is required");
    }
  }

  // Address
  const addressInput = form.querySelector('input[name="address"]');
  if (addressInput) {
    addressInput.style.border = "";
    document.getElementById("error_address").textContent = "";
    if (!addressInput.value.trim()) {
      addressInput.style.border = "2px solid #dc3545";
      document.getElementById("error_address").textContent = "Address is required";
      isValid = false;
      errorMessages.push("Address is required");
    }
  }

  // Date of Birth
  const birthDateInput = form.querySelector('input[name="birth_date"]');
  if (birthDateInput) {
    birthDateInput.style.border = "";
    document.getElementById("error_birth_date").textContent = "";
    if (!birthDateInput.value) {
      birthDateInput.style.border = "2px solid #dc3545";
      document.getElementById("error_birth_date").textContent = "Date of birth is required";
      isValid = false;
      errorMessages.push("Date of birth is required");
    } else if (!validateFutureDate(birthDateInput.value)) {
      birthDateInput.style.border = "2px solid #dc3545";
      document.getElementById("error_birth_date").textContent = "Date cannot be in the future";
      isValid = false;
      errorMessages.push("Date of birth cannot be in the future");
    }
  }

  // Email (optional but validate format if filled)
  const emailInput = form.querySelector('input[name="email"]');
  if (emailInput && emailInput.value.trim()) {
    emailInput.style.border = "";
    if (!validateEmail(emailInput.value)) {
      emailInput.style.border = "2px solid #dc3545";
      isValid = false;
      errorMessages.push("Please enter a valid email address");
    }
  }

  return { isValid, errorMessages };
}


function setupFormNavigation() {
  // ── NEXT / BACK buttons ──
  document.querySelectorAll(".js-next_btn, .js-next_button").forEach((button) => {
    button.addEventListener("click", (e) => {
      let isValid = true;
      let errorMessage = "";

      document.querySelectorAll(".form-control, .form-select").forEach((input) => {
        input.style.border = "";
      });
      document.querySelectorAll('[id^="error_"]').forEach((span) => {
        span.textContent = "";
      });

      const activeTabPane = document.querySelector(".tab-pane.active");
      const isInfantForm = activeTabPane.querySelector('input[name="infant_first_name"]') !== null;
      const isMaternalForm = activeTabPane.querySelector('input[name="first_name"]') !== null;

      if (isInfantForm) {
        const infantFirstNameInput = activeTabPane.querySelector('input[name="infant_first_name"]');
        if (infantFirstNameInput) {
          if (!infantFirstNameInput.value.trim()) {
            infantFirstNameInput.style.border = "2px solid #dc3545";
            let errorSpan = document.getElementById("error_first_name");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_first_name"; infantFirstNameInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "First name is required";
            isValid = false; errorMessage += "First name is required\n";
          } else if (!validateName(infantFirstNameInput.value)) {
            infantFirstNameInput.style.border = "2px solid #dc3545";
            let errorSpan = document.getElementById("error_first_name");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_first_name"; infantFirstNameInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "First name must contain only letters";
            isValid = false; errorMessage += "First name must contain only letters\n";
          }
        }
        const infantLastNameInput = activeTabPane.querySelector('input[name="infant_last_name"]');
        if (infantLastNameInput) {
          if (!infantLastNameInput.value.trim()) {
            infantLastNameInput.style.border = "2px solid #dc3545";
            let errorSpan = document.getElementById("error_last_name");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_last_name"; infantLastNameInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "Last name is required";
            isValid = false; errorMessage += "Last name is required\n";
          } else if (!validateName(infantLastNameInput.value)) {
            infantLastNameInput.style.border = "2px solid #dc3545";
            let errorSpan = document.getElementById("error_last_name");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_last_name"; infantLastNameInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "Last name must contain only letters";
            isValid = false; errorMessage += "Last name must contain only letters\n";
          }
        }
        const addressInput = activeTabPane.querySelector('input[name="address"]');
        if (addressInput && !addressInput.value.trim()) {
          let errorSpan = document.getElementById("error_address");
          if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_address"; addressInput.parentNode.appendChild(errorSpan); }
          errorSpan.textContent = "Address is required";
          isValid = false; errorMessage += "Address is required\n";
        }
        const dateRegInput = activeTabPane.querySelector('input[name="date_of_registration"]');
        if (dateRegInput) {
          if (!dateRegInput.value) {
            dateRegInput.style.border = "2px solid #dc3545";
            let errorSpan = document.getElementById("error_date_of_registration");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_date_of_registration"; dateRegInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "Date of registration is required";
            isValid = false; errorMessage += "Date of registration is required\n";
          } else if (!validateFutureDate(dateRegInput.value)) {
            dateRegInput.style.border = "2px solid #dc3545";
            let errorSpan = document.getElementById("error_date_of_registration");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_date_of_registration"; dateRegInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "Date cannot be in the future";
            isValid = false; errorMessage += "Date of registration cannot be in the future\n";
          }
        }
        const familySerialInput = activeTabPane.querySelector('input[name="family_serial_number"]');
        if (familySerialInput && !familySerialInput.value.trim()) {
          let errorSpan = document.getElementById("error_family_serial_number");
          if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_family_serial_number"; familySerialInput.parentNode.appendChild(errorSpan); }
          errorSpan.textContent = "Family serial number is required";
          isValid = false; errorMessage += "Family serial number is required\n";
        }
        const socioEconHidden = activeTabPane.querySelector('input[name="socio_economic_status"][type="hidden"]');
        if (socioEconHidden && !socioEconHidden.value) {
          let errorSpan = document.getElementById("error_socio_economic_status");
          if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_socio_economic_status"; socioEconHidden.parentNode.appendChild(errorSpan); }
          errorSpan.textContent = "Socio-economic status is required";
          isValid = false; errorMessage += "Socio-economic status is required\n";
        }
        const nameOfMotherInput = activeTabPane.querySelector('input[name="name_of_mother"]');
        if (nameOfMotherInput && !nameOfMotherInput.value.trim()) {
          let errorSpan = document.getElementById("error_name_of_mother");
          if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_name_of_mother"; nameOfMotherInput.parentNode.appendChild(errorSpan); }
          errorSpan.textContent = "Complete name of Mother is required";
          isValid = false; errorMessage += "Complete name of Mother is required\n";
        }
        const infantBirthDateInput = activeTabPane.querySelector('input[name="infant_birth_date"]');
        if (infantBirthDateInput) {
          if (!infantBirthDateInput.value) {
            infantBirthDateInput.style.border = "2px solid #dc3545";
            let errorSpan = document.getElementById("error_birth_date");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_birth_date"; infantBirthDateInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "Date of birth is required";
            isValid = false; errorMessage += "Date of birth is required\n";
          } else if (!validateFutureDate(infantBirthDateInput.value)) {
            infantBirthDateInput.style.border = "2px solid #dc3545";
            let errorSpan = document.getElementById("error_birth_date");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_birth_date"; infantBirthDateInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "Date cannot be in the future";
            isValid = false; errorMessage += "Date of birth cannot be in the future\n";
          }
        }
        const contactInput = activeTabPane.querySelector('input[name="contact_number"]');
        if (contactInput) {
          if (!contactInput.value.trim()) {
            let errorSpan = document.getElementById("error_contact_number");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_contact_number"; contactInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "Contact number is required";
            isValid = false; errorMessage += "Contact number is required\n";
          } else if (!validateContactNumber(contactInput.value)) {
            let errorSpan = document.getElementById("error_contact_number");
            if (!errorSpan) { errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_contact_number"; contactInput.parentNode.appendChild(errorSpan); }
            errorSpan.textContent = "Contact number must be 11 digits starting with 09";
            isValid = false; errorMessage += "Contact number must be 11 digits starting with 09\n";
          }
        }

      } else if (isMaternalForm) {
        const firstNameInput = activeTabPane.querySelector('input[name="first_name"]');
        if (firstNameInput) {
          if (!firstNameInput.value.trim()) {
            firstNameInput.style.border = "2px solid #dc3545";
            document.getElementById("error_first_name").textContent = "First name is required";
            isValid = false; errorMessage += "First name is required\n";
          } else if (!validateName(firstNameInput.value)) {
            firstNameInput.style.border = "2px solid #dc3545";
            document.getElementById("error_first_name").textContent = "First name must contain only letters";
            isValid = false; errorMessage += "First name must contain only letters\n";
          }
        }
        const lastNameInput = activeTabPane.querySelector('input[name="last_name"]');
        if (lastNameInput) {
          if (!lastNameInput.value.trim()) {
            lastNameInput.style.border = "2px solid #dc3545";
            document.getElementById("error_last_name").textContent = "Last name is required";
            isValid = false; errorMessage += "Last name is required\n";
          } else if (!validateName(lastNameInput.value)) {
            lastNameInput.style.border = "2px solid #dc3545";
            document.getElementById("error_last_name").textContent = "Last name must contain only letters";
            isValid = false; errorMessage += "Last name must contain only letters\n";
          }
        }
        const contactInput = activeTabPane.querySelector('input[name="contact_number"]');
        if (contactInput) {
          if (!contactInput.value.trim()) {
            contactInput.style.border = "2px solid #dc3545";
            document.getElementById("error_contact_number").textContent = "Contact number is required";
            isValid = false; errorMessage += "Contact number is required\n";
          } else if (!validateContactNumber(contactInput.value)) {
            contactInput.style.border = "2px solid #dc3545";
            document.getElementById("error_contact_number").textContent = "Must be 11 digits starting with 09";
            isValid = false; errorMessage += "Contact number must be 11 digits starting with 09\n";
          }
        }
        const ageInput = activeTabPane.querySelector('input[name="age"]');
        const ageBracketInput = activeTabPane.querySelector('input[name="age_bracket"]:checked');
        if (ageInput) {
          const ageValue = parseInt(ageInput.value);
          if (ageInput.value === "" || isNaN(ageValue) || ageValue < 10 || ageValue > 49) {
            ageInput.style.border = "2px solid #dc3545";
            document.getElementById("error_age").textContent = "Age must be between 10-49";
            isValid = false; errorMessage += "Age must be between 10-49\n";
          } else if (!ageBracketInput) {
            document.getElementById("error_age_bracket").textContent = "Please select an age bracket";
            isValid = false; errorMessage += "Age bracket is required\n";
          } else if (!validateAgeBracket(ageInput.value, ageBracketInput.value)) {
            ageInput.style.border = "2px solid #dc3545";
            document.getElementById("error_age").textContent = "Age does not match selected bracket";
            document.getElementById("error_age_bracket").textContent = "Age bracket does not match age";
            isValid = false; errorMessage += "Age must match the selected age bracket\n";
          }
        }
        const dateRegInput = activeTabPane.querySelector('input[name="date_of_registration"]');
        if (dateRegInput) {
          if (!dateRegInput.value) {
            dateRegInput.style.border = "2px solid #dc3545";
            document.getElementById("error_date_of_registration").textContent = "Date of registration is required";
            isValid = false; errorMessage += "Date of registration is required\n";
          } else if (!validateFutureDate(dateRegInput.value)) {
            dateRegInput.style.border = "2px solid #dc3545";
            document.getElementById("error_date_of_registration").textContent = "Date cannot be in the future";
            isValid = false; errorMessage += "Date of registration cannot be in the future\n";
          }
        }
        const familySerialInput = activeTabPane.querySelector('input[name="family_serial_number"]');
        if (familySerialInput && !familySerialInput.value.trim()) {
          familySerialInput.style.border = "2px solid #dc3545";
          document.getElementById("error_family_serial_number").textContent = "Family serial number is required";
          isValid = false; errorMessage += "Family serial number is required\n";
        }
        const socioEconSelect = activeTabPane.querySelector('select[name="socio_economic_status"]');
        if (socioEconSelect && !socioEconSelect.value) {
          socioEconSelect.style.border = "2px solid #dc3545";
          document.getElementById("error_socio_economic_status").textContent = "Please select socio-economic status";
          isValid = false; errorMessage += "Socio-economic status is required\n";
        }
        const addressInput = activeTabPane.querySelector('input[name="address"]');
        if (addressInput && !addressInput.value.trim()) {
          addressInput.style.border = "2px solid #dc3545";
          document.getElementById("error_address").textContent = "Address is required";
          isValid = false; errorMessage += "Address is required\n";
        }
        const birthDateInput = activeTabPane.querySelector('input[name="birth_date"]');
        if (birthDateInput) {
          if (!birthDateInput.value) {
            birthDateInput.style.border = "2px solid #dc3545";
            document.getElementById("error_birth_date").textContent = "Date of birth is required";
            isValid = false; errorMessage += "Date of birth is required\n";
          } else if (!validateFutureDate(birthDateInput.value)) {
            birthDateInput.style.border = "2px solid #dc3545";
            document.getElementById("error_birth_date").textContent = "Date cannot be in the future";
            isValid = false; errorMessage += "Date of birth cannot be in the future\n";
          }
        }
        const gravidityInput = activeTabPane.querySelector('input[name="gravidity"]');
        const gravidityError = document.getElementById("error_gravidity");
        if (gravidityInput && gravidityInput.value !== "") {
          if (parseFloat(gravidityInput.value) < 0) {
            gravidityInput.style.border = "2px solid #dc3545";
            if (gravidityError) gravidityError.textContent = "Cannot be negative";
            isValid = false; errorMessage += "Gravidity cannot be negative\n";
          }
        }
        const parityInput = activeTabPane.querySelector('input[name="parity"]');
        const parityError = document.getElementById("error_parity");
        if (parityInput && parityInput.value !== "") {
          if (parseFloat(parityInput.value) < 0) {
            parityInput.style.border = "2px solid #dc3545";
            if (parityError) parityError.textContent = "Cannot be negative";
            isValid = false; errorMessage += "Parity cannot be negative\n";
          }
        }
        const emailInput = activeTabPane.querySelector('input[name="email"]');
        if (emailInput && emailInput.value.trim() && !validateEmail(emailInput.value)) {
          emailInput.style.border = "2px solid #dc3545";
          let errorSpan = emailInput.nextElementSibling;
          if (!errorSpan || !errorSpan.classList.contains("text-danger")) {
            errorSpan = document.createElement("span"); errorSpan.className = "text-danger"; errorSpan.id = "error_email"; emailInput.parentNode.appendChild(errorSpan);
          }
          errorSpan.textContent = "Please enter a valid email address";
          isValid = false; errorMessage += "Please enter a valid email address\n";
        }
      }

      if (!isValid) {
        alert("Please fix the following errors:\n\n" + errorMessage);
        e.preventDefault();
        return;
      }

      var activeTab = document.querySelector(".nav-tabs .nav-link.active");
      var nextTab = activeTab.parentElement.nextElementSibling;
      if (nextTab) {
        var nextTabLink = nextTab.querySelector(".nav-link");
        nextTabLink.setAttribute("data-bs-toggle", "tab");
        nextTabLink.click();
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
        prevTabLink.setAttribute("data-bs-toggle", "tab");
        prevTabLink.click();
        scrollToTop();
      }
    });
  });
}

function scrollToTop() {
  const mainContainer =
    document.querySelector(".main-container") ||
    document.querySelector(".main-content");
  if (mainContainer) {
    mainContainer.scrollIntoView({ behavior: "smooth", block: "start" });
  }
}

// ── EVENT DELEGATION: intercept maternal form submit at the document level ──
// This works even when the form is dynamically injected via loadPage()
document.addEventListener("submit", function (e) {
   const form = e.target;
  const isMaternalSubmit = form.action && form.action.includes("maternal_process.php");
  const isInfantSubmit = form.action && form.action.includes("infant_process.php");
  const isPostpartumSubmit = form.action && form.action.includes("postpartum_process.php");
  if (!isMaternalSubmit && !isInfantSubmit && !isPostpartumSubmit) return;

  e.preventDefault();

  const result = validatePatientDetails();
  if (!result.isValid) {
    const firstTabLink = document.querySelector('.nav-tabs .nav-item:first-child .nav-link');
    if (firstTabLink) {
      firstTabLink.setAttribute("data-bs-toggle", "tab");
      firstTabLink.click();
      scrollToTop();
    }
    Swal.fire({
      icon: "error",
      title: "Please fix the following errors:",
      html: result.errorMessages.map(msg => `• ${msg}`).join("<br>"),
      confirmButtonText: "OK",
      confirmButtonColor: "#c0392b",
    });
    return;
  }

  // Validation passed — submit via fetch to stay in the SPA
  const formData = new FormData(form);
  formData.append("submit_btn", "1");

  fetch(form.action, { method: "POST", body: formData })
    .then(response => response.text())
    .then(() => {
      Swal.fire({
        icon: "success",
        title: "Record Added Successfully!",
        confirmButtonText: "OK",
        confirmButtonColor: "#3085d6",
      }).then(() => {
        loadPage('addPatient_LandingPg.php');
      });
    })
    .catch(err => {
      console.error("Submit error:", err);
      Swal.fire({
        icon: "error",
        title: "Something went wrong",
        text: "Please try again.",
        confirmButtonText: "OK",
        confirmButtonColor: "#c0392b",
      });
    });
});

document.addEventListener("DOMContentLoaded", function () {
  setupFormNavigation();
});