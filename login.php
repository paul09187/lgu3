<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f4f9fc;
            color: #1b4965;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
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
            <div class="card-header">Login</div>
            <form id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required />
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required />
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="showPassword" />
                    <label class="form-check-label" for="showPassword">Show Password</label>
                </div>
                <div id="feedback"></div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <small>Forgot your password? <a href="forgot_password.php" class="text-decoration-none">Reset it here</a>.</small><br />
                <small>Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a>.</small>
            </div>
        </div>
    </div>
    <script>
        document.getElementById("showPassword").addEventListener("change", function() {
            const passwordField = document.getElementById("password");
            passwordField.type = this.checked ? "text" : "password";
        });

        document.getElementById("loginForm").addEventListener("submit", async function(e) {
            e.preventDefault();
            const feedback = document.getElementById("feedback");
            const formData = new FormData(this);
            formData.append("action", "login");

            try {
                const response = await fetch("backend/auth.php", {
                    method: "POST",
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    feedback.className = "success";
                    feedback.textContent = "Login successful! Redirecting...";
                    feedback.style.display = "block";
                    setTimeout(() => window.location.href = "index.php", 1500);
                } else {
                    feedback.className = "error";
                    feedback.textContent = data.message || "Login failed.";
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