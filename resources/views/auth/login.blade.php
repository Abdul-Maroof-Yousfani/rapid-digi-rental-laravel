<!DOCTYPE html>
<html lang="en">
<!-- auth-login.html  21 Nov 2019 03:49:32 GMT -->
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Rapid Digi Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            user-select: none;
            }
            .bg-img {
            background: url('{{ asset('assets/img/login_image.png') }}');
            height: 100vh;
            background-size: cover;
            background-position: center;
            }
            .bg-img:after {
            position: absolute;
            content: "";
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background: rgba(0, 0, 0, 0.7);
            }
            .content {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 999;
            text-align: center;
            padding: 60px 32px;
            width: 370px;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.04);
            box-shadow: -1px 4px 28px 0px rgba(0, 0, 0, 0.75);
            }
            .content header {
            color: white;
            font-size: 33px;
            font-weight: 600;
            margin: 0 0 35px 0;
            font-family: "'Playfair Display', 'Poppins', sans-serif";
            }
            .field {
            position: relative;
            height: 45px;
            width: 100%;
            display: flex;
            background: rgba(255, 255, 255, 0.94);
            }
            .field span {
            color: #222;
            width: 40px;
            line-height: 45px;
            }
            .field input {
            height: 100%;
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            color: #222;
            font-size: 16px;
            font-family: "Poppins", sans-serif;
            }
            .space {
            margin-top: 16px;
            }
            .invalid-feedback {
                color: red;
                font-size: 14px;
                text-align: left;
                margin-top: 5px;
                font-family: "Poppins", sans-serif;
            }
            input.is-invalid {
                border: 1px solid red !important;
            }
            .show {
            position: absolute;
            right: 13px;
            font-size: 13px;
            font-weight: 700;
            color: #222;
            display: none;
            cursor: pointer;
            font-family: "Montserrat", sans-serif;
            }
            .pass-key:valid ~ .show {
            display: block;
            }
            .pass {
            text-align: left;
            margin: 10px 0;
            }
            .pass a {
            color: white;
            text-decoration: none;
            font-family: "Poppins", sans-serif;
            }
            .pass:hover a {
            text-decoration: underline;
            }
            .field input[type="submit"] {
            background: #3498db;
            border: 1px solid #2691d9;
            color: white;
            font-size: 18px;
            letter-spacing: 1px;
            font-weight: 600;
            cursor: pointer;
            font-family: "Montserrat", sans-serif;
            }
            .field input[type="submit"]:hover {
            background: #2691d9;
            }
            .login {
            color: white;
            margin: 20px 0;
            font-family: "Poppins", sans-serif;
            }
            .links {
            display: flex;
            cursor: pointer;
            color: white;
            margin: 0 0 20px 0;
            }
            .facebook,
            .instagram {
            width: 100%;
            height: 45px;
            line-height: 45px;
            margin-left: 10px;
            }
            .facebook {
            margin-left: 0;
            background: #4267b2;
            border: 1px solid #3e61a8;
            }
            .instagram {
            background: #e1306c;
            border: 1px solid #df2060;
            }
            .facebook:hover {
            background: #3e61a8;
            }
            .instagram:hover {
            background: #df2060;
            }
            .links i {
            font-size: 17px;
            }
            i span {
            margin-left: 8px;
            font-weight: 500;
            letter-spacing: 1px;
            font-size: 16px;
            font-family: "Poppins", sans-serif;
            }
            .signup {
            font-size: 15px;
            color: white;
            font-family: "Poppins", sans-serif;
            }
            .signup a {
            color: #3498db;
            text-decoration: none;
            }
            .signup a:hover {
            text-decoration: underline;
            }
            @keyframes fadeSlideUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .invalid-feedback {
                color: red;
                font-size: 14px;
                text-align: left;
                margin-top: 5px;
                font-family: "Poppins", sans-serif;
                animation: fadeSlideUp 0.4s ease forwards;
            }

    </style>
</head>
<body>

    <div class="bg-img">
        <div class="content">
          <header>Login | Form</header>
          <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate="">
            @csrf
            <div class="field">
                <span class="fa fa-user"></span>
                <input id="email" type="email" placeholder="Enter Your Email"
                       class="form-control @error('email') is-invalid @enderror"
                       name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            </div>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror


            <div class="field space">
                <span class="fa fa-lock"></span>
                <input id="password" type="password" placeholder="Enter Your Password"
                       class="pass-key @error('password') is-invalid @enderror"
                       name="password" required>
                <span class="show">SHOW</span>
            </div>
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror


            <div class="pass">
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-small">
                  Forgot Password?
                </a>
                @endif
            </div>
            <div class="field">
              <input type="submit" value="LOGIN">
            </div>
          </form>








          
        </div>
      </div>


</body>


<!-- auth-login.html  21 Nov 2019 03:49:32 GMT -->
</html>

<script>

const pass_field = document.querySelector(".pass-key");
const showBtn = document.querySelector(".show");
showBtn.addEventListener("click", function() {
  if (pass_field.type === "password") {
    pass_field.type = "text";
    showBtn.textContent = "HIDE";
    showBtn.style.color = "#3498db";
  } else {
    pass_field.type = "password";
    showBtn.textContent = "SHOW";
    showBtn.style.color = "#222";
  }
});
</script>
