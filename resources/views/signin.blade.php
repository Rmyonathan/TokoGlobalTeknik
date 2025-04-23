<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumka - Sign In</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous">
    <style>
        body {
            min-height: 100vh;
            position: relative;
        }

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: fixed;
            top: 0;
            left: 0;
            z-index: -1; /* Send the image to the back */
            filter: brightness(50%) opacity(80%);
        }

        .signin-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full height for centering */
            z-index: 1; /* Ensure the container is above the background image */
        }

        .title {
            color: orange; /* Orange color for the title */
            font-weight: bold; /* Bold styling */
            font-size: 1.5rem; /* Adjust font size as needed */
            font-style: italic; /* Italic styling */
            margin-bottom: 50px; /* Space between title and form */
        }

        .signin-form {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background for the form */
            padding : 2%; /* Reduced padding for better spacing */
            border-radius: 5%; /* Slightly rounded corners */
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.3); /* Increased shadow for depth */
            width: 60%; /* Reduced width */
            height: 41%; /* Reduced height */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .signin-form h2 {
            font-size: 2rem; /* Increased font size for the heading */
        }

        .signin-form .form-label {
            font-size: 1.2rem; /* Increased font size for labels */
        }

        .signin-form .form-control {
            font-size: 1.2rem; /* Increased font size for input fields */
            padding: 3%; /* Reduced padding for input fields */
        }

        .signin-form .btn {
            font-size: 1.2rem; /* Increased font size for button */
            padding: 3%; /* Reduced padding for button */
            border-radius : 20%;
        }
    </style>
</head>
<body>
    @if(session('loginError'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('loginError') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <img src="/storage/image/hotel-welcome.jpg" alt="Alumka Store">
    <div class="signin-container">
        <p class="title">SDI Management System</p>
        <div class="signin-form">
            <h2 class="text-center">Sign In</h2>
            <form action="/signin" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">System Email</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Enter email">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
