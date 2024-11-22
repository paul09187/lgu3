<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registration</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f4f9fc;
            color: #1b4965;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
        }

        .container {
            display: flex;
            flex-direction: row;
            width: 100%;
            max-width: 1000px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            background-color: #ffffff;
        }

        .left {
            flex: 1;
            background-color: #5fa8d3;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .left img {
            max-width: 80%;
            height: auto;
            border-radius: 10px;
        }

        .right {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-header {
            text-align: center;
            color: #1b4965;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 500;
        }

        .form-control {
            border-radius: 50px;
            padding: 0.8rem 1.5rem;
        }

        .btn-primary {
            background-color: #1b4965;
            border-color: #1b4965;
            padding: 0.8rem;
            border-radius: 50px;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: #143a50;
            border-color: #143a50;
        }

        #feedback {
            display: none;
            margin-top: 1rem;
            text-align: center;
        }

        #feedback.success {
            color: green;
        }

        #feedback.error {
            color: red;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                max-width: 100%;
            }

            .left {
                display: none;
            }

            .right {
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="left">
            <img src="assets/img/Unified-LGU-3-LOGO-preview.png" alt="LGU Logo" />
        </div>
        <div class="right">
            <div class="card-header">Create an Account</div>
            <form id="registerForm">
                <div class="row">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="username" name="name" placeholder="Enter your full name" required />
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="contactNumber" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contactNumber" name="contact_number" placeholder="Enter your contact number" required />
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" placeholder="Enter your address" required></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="birthDate" class="form-label">Birth Date</label>
                        <input type="date" class="form-control" id="birthDate" name="birth_date" required />
                    </div>
                    <div class="col-md-6">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-control" id="gender" name="gender" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="civilStatus" class="form-label">Civil Status</label>
                        <select class="form-control" id="civilStatus" name="civil_status" required>
                            <option value="" disabled selected>Select Civil Status</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="widowed">Widowed</option>
                            <option value="separated">Separated</option>
                            <option value="divorced">Divorced</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="occupation" class="form-label">Occupation</label>
                        <input type="text" class="form-control" id="occupation" name="occupation" placeholder="Enter your occupation" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="householdNumber" class="form-label">Household Number</label>
                        <input type="text" class="form-control" id="householdNumber" name="household_number" placeholder="Enter your household number" required />
                    </div>
                    <div class="col-md-6">
                        <label for="barangayId" class="form-label">Barangay ID</label>
                        <input type="text" class="form-control" id="barangayId" name="barangay_id" placeholder="Enter your barangay ID" required />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required />
                    </div>
                    <div class="col-md-6">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm your password" required />
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="showPassword" />
                    <label class="form-check-label" for="showPassword">Show Password</label>
                </div>
                <div id="feedback"></div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <small>Already have an account? <a href="login.php" class="text-decoration-none">Login here</a>.</small>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("showPassword").addEventListener("change", function() {
            const passwordField = document.getElementById("password");
            const confirmPasswordField = document.getElementById("confirmPassword");
            const type = this.checked ? "text" : "password";
            passwordField.type = type;
            confirmPasswordField.type = type;
        });

        document.getElementById("registerForm").addEventListener("submit", async function(e) {
            e.preventDefault();
            const feedback = document.getElementById("feedback");
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirmPassword").value;

            if (password !== confirmPassword) {
                feedback.className = "error";
                feedback.textContent = "Passwords do not match.";
                feedback.style.display = "block";
                return;
            }

            const formData = new FormData(this);
            formData.append("action", "register");

            try {
                const response = await fetch("backend/auth.php", {
                    method: "POST",
                    body: formData,
                });

                const data = await response.json();
                if (response.ok) {
                    feedback.className = "success";
                    feedback.textContent = "Registration successful! Redirecting...";
                    feedback.style.display = "block";
                    setTimeout(() => (window.location.href = "login.php"), 1500);
                } else {
                    feedback.className = "error";
                    feedback.textContent = data.message || "Registration failed.";
                    feedback.style.display = "block";
                }
            } catch {
                feedback.className = "error";
                feedback.textContent = "An error occurred.";
                feedback.style.display = "block";
            }
        });
    </script>
</body>

</html>