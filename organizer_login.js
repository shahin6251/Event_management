// --- JavaScript for Organizer Login Page ---

document.addEventListener('DOMContentLoaded', () => {
    // Get interactive elements from the page
    const loginForm = document.getElementById('login-form');
    const forgotPasswordLink = document.getElementById('forgot-password-link');
    const signupLink = document.getElementById('signup-link');

    // Add event listener for the form submission
    if (loginForm) {
        loginForm.addEventListener('submit', (event) => {
            // Prevent the form from actually submitting to a server
            event.preventDefault(); 
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            // Simple validation check
            if (!username || !password) {
                console.error("Username and password fields are required.");
                // In a real app, you would display a user-friendly error message here
                return;
            }
            
            console.log('Login form submitted.');
            console.log('Username:', username);
            console.log('Password:', password);
            
            // Placeholder for actual login logic (e.g., API call)
            // For now, we'll just log a success message.
            alert(`Simulating login for user: ${username}`);
        });
    }

    // Add event listener for the "Forgot Password" link
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', (event) => {
            event.preventDefault();
            console.log('"Forgot Password" link clicked.');
            // In a real application, this would redirect to a password reset page
            alert("Redirecting to the password recovery page.");
        });
    }

    // --- THIS IS THE CORRECTED PART ---
    // Add event listener for the "Sign Up" link
    if (signupLink) {
        signupLink.addEventListener('click', (event) => {
            event.preventDefault();
            console.log('"Sign Up" link clicked, redirecting...');
            // This line now correctly redirects to your sign-up page.
            window.location.href = 'organizer_signup.html';
        });
    }
});

