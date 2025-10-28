// --- JavaScript for Organizer Sign-up Page ---

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
            const companyName = document.getElementById('company-name').value;
            const companyWebsite = document.getElementById('company-website').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const termsAgreed = document.getElementById('terms-conditions').checked;
            const marketingConsent = document.getElementById('marketing-consent').checked;

            // --- Validation ---
            if (!fullName || !email || !username || !password || !confirmPassword) {
                console.error("Please fill out all required fields.");
                 // In a real app, you would show a user-friendly error message here.
                alert("Please fill out all required fields.");
                return;
            }

            if (password !== confirmPassword) {
                console.error("Passwords do not match.");
                alert("Passwords do not match.");
                return;
            }

            if (!termsAgreed) {
                console.error("You must agree to the terms and conditions.");
                alert("You must agree to the terms and conditions to sign up.");
                return;
            }

            // --- Form Submission Simulation ---
            console.log('Sign-up form submitted successfully!');
            const formData = {
                fullName,
                email,
                phone,
                companyName,
                companyWebsite,
                username,
                password, // In a real app, NEVER log or store plain text passwords
                termsAgreed,
                marketingConsent
            };
            console.log('Form Data:', formData);

            // In a real app, you would send this data to a server.
            alert(`Welcome, ${fullName}! Your account has been created (simulation).`);
            
            // Optionally, redirect to the login page after successful sign-up
            window.location.href = 'organizer_login.html';
        });
    }
});
