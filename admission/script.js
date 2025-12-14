document.addEventListener('DOMContentLoaded', () => {

    // Ethnicity "Other" Checkbox 
    const ethnicityOther = document.getElementById('ethnicityOther');
    const ethnicityOtherText = document.getElementById('ethnicityOtherText');
    if (ethnicityOther) {
        ethnicityOther.addEventListener('change', () => {
            ethnicityOtherText.style.display = ethnicityOther.checked ? 'block' : 'none';
            ethnicityOtherText.required = ethnicityOther.checked;
            if (!ethnicityOther.checked) ethnicityOtherText.value = ''; 
        });
    }

    // Disability "Other" Checkbox
    const disabilityOther = document.getElementById('disabilityOther');
    const disabilityOtherText = document.getElementById('disabilityOtherText');
    if (disabilityOther) {
        disabilityOther.addEventListener('change', () => {
            disabilityOtherText.style.display = disabilityOther.checked ? 'block' : 'none';
            disabilityOtherText.required = disabilityOther.checked;
            if (!disabilityOther.checked) disabilityOtherText.value = ''; 
        });
    }

    // signature Preview
    function setupSignaturePreview(inputFileId, previewContainerId) {
        const inputFile = document.getElementById(inputFileId);
        const previewContainer = document.getElementById(previewContainerId);

        if (inputFile && previewContainer) {
            inputFile.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.innerHTML = `<img src="${e.target.result}" alt="Signature Preview">`;
                    };
                    reader.readAsDataURL(this.files[0]);
                } else {
                    previewContainer.innerHTML = ''; 
                }
            });
        }
    }

    setupSignaturePreview('studentSignature', 'studentSignaturePreview');
    setupSignaturePreview('parentSignature', 'parentSignaturePreview');

    //Terms Modal
    const termsModal = document.getElementById('termsModal');
    const termsLink = document.getElementById('termsLink');
    const closeButtons = document.querySelectorAll('.close-button, .modal-close-btn');

    if (termsLink) {
        termsLink.addEventListener('click', (e) => {
            e.preventDefault();
            termsModal.classList.add('show');
        });
    }

    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            termsModal.classList.remove('show');
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === termsModal) {
            termsModal.classList.remove('show');
        }
    });

    //  same as current address checkbox
    const sameAsCurrentCheckbox = document.getElementById('sameAsCurrent');
    if (sameAsCurrentCheckbox) {
        const currentFields = ['currentHouseStreet', 'currentBarangay', 'currentCity', 'currentProvince', 'currentZipCode', 'mobileNumber', 'telephoneNumber']; 
        const permanentFields = ['permanentHouseStreet', 'permanentBarangay', 'permanentCity', 'permanentProvince', 'permanentZipCode', 'permanentMobileNumber', 'permanentTelephoneNumber'];

        sameAsCurrentCheckbox.addEventListener('change', function() {
            if (this.checked) {
                for (let i = 0; i < currentFields.length; i++) {
                    const currentInput = document.getElementById(currentFields[i]);
                    const permanentInput = document.getElementById(permanentFields[i]);
                    if (currentInput && permanentInput) {
                        permanentInput.value = currentInput.value;
                    }
                }
            }
        });
    }

    // Unsaved Changes Warning
    const studentForm = document.getElementById('admissionForm'); 
    let studentFormIsDirty = false; 
    let studentFormIsSubmitting = false; 

    function setStudentFormDirty() {
        studentFormIsDirty = true;
        studentForm.removeEventListener('input', setStudentFormDirty);
        studentForm.removeEventListener('change', setStudentFormDirty);
    }

    if (studentForm) {
        studentForm.addEventListener('input', setStudentFormDirty);
        studentForm.addEventListener('change', setStudentFormDirty);

        studentForm.addEventListener('submit', function() {
            studentFormIsSubmitting = true;
            
            const progSelect = document.getElementById('academicProgram');
            if (progSelect) {
                progSelect.disabled = false; 
            }
        });
    }

    window.addEventListener('beforeunload', function (e) {
        if (studentFormIsDirty && !studentFormIsSubmitting) {
            const confirmationMessage = 'You have unsaved changes. Are you sure you want to leave? Your entered data will be lost.';
            e.returnValue = confirmationMessage; 
            return confirmationMessage; 
        }
    });

});