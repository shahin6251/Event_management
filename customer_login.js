// --- JavaScript for Customer Login Page ---

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

            if (!username || !password) {
                console.error("Username and password are required.");
                return;
            }
            
            console.log('Customer login form submitted.');
            alert(`Simulating login for customer: ${username}`);
        });
    }

    // Add event listener for the "Forgot Password" link
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', (event) => {
            event.preventDefault();
            console.log('"Forgot Password" link clicked.');
            alert("Redirecting to the password recovery page.");
        });
    }

    // Add event listener for the "Sign Up" link
    if (signupLink) {
        signupLink.addEventListener('click', (event) => {
            event.preventDefault();
            console.log('"Sign Up" link clicked, redirecting...');
            window.location.href = 'customer_signup.html';
        });
    }
});
