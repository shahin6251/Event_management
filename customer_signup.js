// --- JavaScript for Customer Sign-up Page ---

document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signup-form');

    if (signupForm) {
        signupForm.addEventListener('submit', (event) => {
            // Prevent the default form submission
            event.preventDefault();

            // --- Form Data Retrieval ---
            const fullName = document.getElementById('full-name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const dob = document.getElementById('dob').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const termsAgreed = document.getElementById('terms-conditions').checked;
            const marketingConsent = document.getElementById('marketing-consent').checked;

            // --- Validation ---
            if (!fullName || !email || !username || !password || !confirmPassword) {
                alert("Please fill out all required fields.");
                return;
            }

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return;
            }

            if (!termsAgreed) {
                alert("You must agree to the terms and conditions to sign up.");
                return;
            }

            // --- Form Submission Simulation ---
            console.log('Customer sign-up form submitted successfully!');
            const formData = {
                fullName,
                email,
                phone,
                dob,
                username,
                termsAgreed,
                marketingConsent
            };
            console.log('Form Data:', formData);

            alert(`Welcome, ${fullName}! Your customer account has been created (simulation).`);
            
            // Optionally, redirect to the login page after successful sign-up
            window.location.href = 'customer_login.html';
        });
    }
});
