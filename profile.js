// profile.js
// Handles edit profile functionality

document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editBtn');
    const nameInput = document.getElementById('nameInput');
    const emailInput = document.getElementById('emailInput');
    const contactInput = document.getElementById('contactInput');
    const fileInput = document.getElementById('fileInput');
    const profileImage = document.getElementById('profileImage');
    const form = document.querySelector('form');
    const profileInfo = document.querySelector('.profile-info');

    let isEditMode = false;

    // Edit button click handler
    editBtn.addEventListener('click', function() {
        if (!isEditMode) {
            // Enter edit mode
            enableEditMode();
        } else {
            // Submit the form
            submitForm();
        }
    });

    // Enable edit mode
    function enableEditMode() {
        isEditMode = true;
        nameInput.disabled = false;
        emailInput.disabled = false;
        contactInput.disabled = false;
        
        editBtn.textContent = 'Save Changes';
        editBtn.classList.add('save-mode');

        // Add cancel button
        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'cancel-btn';
        cancelBtn.textContent = 'Cancel';
        cancelBtn.addEventListener('click', disableEditMode);

        // Wrap buttons in a container
        const buttonGroup = document.createElement('div');
        buttonGroup.className = 'button-group';
        buttonGroup.appendChild(editBtn.cloneNode(true));
        buttonGroup.appendChild(cancelBtn);

        // Replace the edit button with button group
        editBtn.parentNode.replaceChild(buttonGroup, editBtn);
        
        // Update references
        const newEditBtn = buttonGroup.querySelector('.edit-btn');
        newEditBtn.addEventListener('click', submitForm);
        buttonGroup.querySelector('.cancel-btn').addEventListener('click', disableEditMode);

        // Focus on first input
        nameInput.focus();
    }

    // Disable edit mode
    function disableEditMode() {
        isEditMode = false;
        nameInput.disabled = true;
        emailInput.disabled = true;
        contactInput.disabled = true;

        // Restore original button
        const buttonGroup = document.querySelector('.button-group');
        if (buttonGroup) {
            editBtn.textContent = 'Edit Profile';
            editBtn.classList.remove('save-mode');
            buttonGroup.parentNode.replaceChild(editBtn, buttonGroup);
            editBtn.addEventListener('click', function() {
                if (!isEditMode) {
                    enableEditMode();
                } else {
                    submitForm();
                }
            });
        }
    }

    // Submit form
    function submitForm() {
        // Validate inputs
        if (!nameInput.value.trim()) {
            showMessage('Name is required', true);
            nameInput.focus();
            return;
        }

        if (!emailInput.value.trim()) {
            showMessage('Email is required', true);
            emailInput.focus();
            return;
        }

        if (!isValidEmail(emailInput.value)) {
            showMessage('Please enter a valid email address', true);
            emailInput.focus();
            return;
        }

        if (!contactInput.value.trim()) {
            showMessage('Contact number is required', true);
            contactInput.focus();
            return;
        }

        // Submit the form
        form.submit();
    }

    // Validate email format
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Show message
    function showMessage(text, isError = false) {
        let messageEl = document.querySelector('.message');
        
        if (!messageEl) {
            messageEl = document.createElement('p');
            messageEl.className = 'message';
            form.parentNode.insertBefore(messageEl, form);
        }

        messageEl.textContent = text;
        messageEl.classList.toggle('error', isError);
        messageEl.style.display = 'block';

        // Auto-hide success messages after 3 seconds
        if (!isError) {
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 3000);
        }
    }

    // Handle file input change
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            showMessage('Please select a valid image file', true);
            fileInput.value = '';
            return;
        }

        // Validate file size (max 5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            showMessage('File size must be less than 5MB', true);
            fileInput.value = '';
            return;
        }

        // Preview the image
        const reader = new FileReader();
        reader.onload = function(event) {
            profileImage.src = event.target.result;
        };
        reader.readAsDataURL(file);

        // Enable edit mode if not already in it
        if (!isEditMode) {
            enableEditMode();
        }
    });

    // Make profile picture clickable to upload
    document.querySelector('.profile-pic').addEventListener('click', function() {
        fileInput.click();
    });

    // Prevent form submission on Enter key in inputs
    [nameInput, emailInput, contactInput].forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (isEditMode) {
                    submitForm();
                }
            }
        });
    });
});
