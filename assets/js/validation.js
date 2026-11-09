document.addEventListener("DOMContentLoaded", function () {

    // Set the minimum appointment date to today
    const dateInputs = document.querySelectorAll('input[type="date"]');

    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day = String(today.getDate()).padStart(2, "0");

    const todayDate = `${year}-${month}-${day}`;

    dateInputs.forEach(function (input) {
        input.setAttribute("min", todayDate);
    });

    // Validate email addresses
    const emailInputs = document.querySelectorAll('input[type="email"]');

    emailInputs.forEach(function (input) {
        input.addEventListener("input", function () {
            input.setCustomValidity("");

            if (input.value !== "" && !input.validity.valid) {
                input.setCustomValidity("Please enter a valid email address.");
            }
        });
    });

    // Validate passwords
    const passwordInputs = document.querySelectorAll('input[type="password"]');

    passwordInputs.forEach(function (input) {
        input.addEventListener("input", function () {
            if (input.value.length > 0 && input.value.length < 8) {
                input.setCustomValidity(
                    "Password must contain at least 8 characters."
                );
            } else {
                input.setCustomValidity("");
            }
        });
    });

});