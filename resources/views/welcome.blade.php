<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading | solopredict</title>
    <!-- Bootstrap CSS -->
     <link rel="icon" href="./favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for additional icons (optional) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8B05EC;
            /* Brand primary color */
            --dark-bg: #f2f2f2;
            /* Rich dark background */
            --text-light: rgba(255, 255, 255, 0.9);
        }

        body {
            background-color: var(--dark-bg);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            overflow: hidden;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }

        .loading-container {
            text-align: center;
            color: black;
            transform: translateY(-10%);
            opacity: 0;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .brand-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            filter: drop-shadow(0 0 8px rgba(78, 115, 223, 0.4));
            transition: transform 0.3s ease;
        }

        .brand-logo:hover {
            transform: scale(1.05);
        }

        .spinner {
            width: 3.5rem;
            height: 3.5rem;
            border-width: 0.3em;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        .loading-text {
            margin-top: 1.5rem;
            font-size: 1.1rem;
            font-weight: 300;
            letter-spacing: 0.5px;
            color: black;
            opacity: 0;
            animation: fadeIn 0.5s ease-out 0.3s forwards;
        }

        .progress-container {
            width: 200px;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            margin: 1.5rem auto 0;
            overflow: hidden;
            border-radius: 2px;
        }

        .progress-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, var(--primary-color), #7a9eff);
            animation: progress 2.5s ease-in-out infinite;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes progress {
            0% {
                width: 0;
                transform: translateX(-50%);
            }

            50% {
                width: 100%;
                transform: translateX(0);
            }

            100% {
                width: 0;
                transform: translateX(100%);
            }
        }
    </style>
</head>

<body>
    <div class="loading-container">
        <!-- Brand Logo - Replace with your actual logo -->
        <!--<div class="brand-logo"-->
        <!--    style="background-image: url('https://www.solopredict.com/dp/public/assets/images/logo.png')"></div>-->
        <!-- Alternative: Use img tag -->
        <!-- <img src="your-logo.svg" alt="Brand Logo" class="brand-logo img-fluid"> -->

        <!-- Animated Spinner -->


        <!-- Loading Text -->
        <div class="log"><img src="./logo.png" width="200"></div>
        <div class="loading-text">Initializing Application..</div>
        {{-- <div class="spinner-border text-primary spinner" role="status">
            <span class="visually-hidden">Loading...</span>
        </div> --}}
        <!-- Progress Indicator -->
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Optional: Simulate loading progress
        document.addEventListener('DOMContentLoaded', function() {
            // Uncomment to redirect after loading
            // setTimeout(function() {
            //     window.location.href = "main.html";
            // }, 5000);

            // Optional: Add percentage counter
            // let percent = 0;
            // const interval = setInterval(() => {
            //     percent += Math.random() * 10;
            //     if(percent >= 100) {
            //         percent = 100;
            //         clearInterval(interval);
            //     }
            //     document.querySelector('.loading-text').textContent = `Loading ${Math.floor(percent)}%`;
            // }, 300);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const loadingText = document.querySelector('.loading-text');
            let timeLeft = 3;

            // Update counter every second
            const countdown = setInterval(function() {
                loadingText.textContent = `Loading... Redirecting in ${timeLeft}s`;
                timeLeft--;

             
            }, 1000);
        });
        // Simple redirect after 3 seconds
        setTimeout(function() {
            window.location.href = "{{ url('/admin') }}";
        }, 3000); // 3000 milliseconds = 3 seconds
    </script>
</body>

</html>