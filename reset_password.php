<?php
// Ensure the token is available
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('<h3 style="color:red; text-align:center;">Invalid or expired reset token. Please try again.</h3>');
}

// Sanitize the token input
$token = htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f4f9fc;
            color: #1b4965;
            height: 100vh;
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

        .btn-primary {
            background-color: #1b4965;
            border-color: #1b4965;
            padding: 10px 20px;
            font-size: 1rem;
            width: 100%;
            border-radius: 50px;
        }

        .btn-primary:hover {
            background-color: #143a50;
            border-color: #143a50;
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
            <div class="card-header">Reset Password</div>
            <form id="resetPasswordForm">
                <input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Enter your new password" required />
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm your new password" required />
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="showPassword" />
                    <label class="form-check-label" for="showPassword">Show Password</label>
                </div>
                <div id="feedback"></div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById("showPassword").addEventListener("change", function() {
            const newPasswordField = document.getElementById("newPassword");
            const confirmPasswordField = document.getElementById("confirmPassword");
            const type = this.checked ? "text" : "password";
            newPasswordField.type = type;
            confirmPasswordField.type = type;
        });

        document.getElementById("resetPasswordForm").addEventListener("submit", async function(e) {
            e.preventDefault();
            const feedback = document.getElementById("feedback");
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;

            if (newPassword !== confirmPassword) {
                feedback.className = "error";
                feedback.textContent = "Passwords do not match.";
                feedback.style.display = "block";
                return;
            }

            const formData = new FormData(this);
            formData.append("action", "reset_password");

            try {
                const response = await fetch("backend/auth.php", {
                    method: "POST",
                    body: formData,
                });

                const data = await response.json();
                if (response.ok) {
                    feedback.className = "success";
                    feedback.textContent = "Password reset successful! Redirecting to login...";
                    feedback.style.display = "block";
                    setTimeout(() => window.location.href = "login.php", 1500);
                } else {
                    feedback.className = "error";
                    feedback.textContent = data.message || "Failed to reset password.";
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