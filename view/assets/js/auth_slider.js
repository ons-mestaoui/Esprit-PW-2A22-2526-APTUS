document.addEventListener('DOMContentLoaded', () => {
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('auth-container');

    if (signUpButton && signInButton && container) {
        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
            // Update URL if needed or just handle state
            history.pushState(null, null, '?panel=signup');
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
            history.pushState(null, null, '?panel=login');
        });
    }

    // Handle initial state from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('panel') === 'signup') {
        container.classList.add("right-panel-active");
    }
});
