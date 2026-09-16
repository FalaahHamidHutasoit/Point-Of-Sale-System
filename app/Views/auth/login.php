<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - POS System</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>

        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            background: #f5f7fb;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .login-container {
            width: 100%;
            max-width: 1050px;
            min-height: 600px;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.10);
            display: flex;
        }

        /* =========================
           LEFT SIDE
        ========================= */

        .login-left {
            width: 50%;
            background: linear-gradient(145deg, #0d6efd, #084298);
            color: white;
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            top: -100px;
            right: -100px;
        }

        .login-left::after {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            bottom: -100px;
            left: -80px;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }

        .login-left h1 {
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .login-left p {
            font-size: 15px;
            line-height: 1.8;
            opacity: 0.9;
            max-width: 400px;
            position: relative;
            z-index: 2;
        }

        .feature-list {
            margin-top: 30px;
            position: relative;
            z-index: 2;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .feature-item i {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }


        /* =========================
           RIGHT SIDE
        ========================= */

        .login-right {
            width: 50%;
            padding: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-form {
            width: 100%;
            max-width: 380px;
        }

        .login-form h2 {
            font-size: 28px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 8px;
        }

        .login-form .subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 8px;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #8c98a4;
            z-index: 5;
        }

        .input-group-custom input {
            height: 50px;
            border: 1px solid #dee2e6;
            border-radius: 12px !important;
            padding-left: 45px;
            font-size: 14px;
            transition: 0.2s;
        }

        .input-group-custom input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.10);
        }

        .login-button {
            height: 50px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 10px;
            transition: 0.2s;
        }

        .login-button:hover {
            transform: translateY(-1px);
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: #adb5bd;
            font-size: 12px;
        }

        .alert {
            border-radius: 12px;
            font-size: 13px;
            border: none;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 768px) {

            .login-container {
                max-width: 500px;
                min-height: auto;
            }

            .login-left {
                display: none;
            }

            .login-right {
                width: 100%;
                padding: 40px 30px;
            }

        }

    </style>

</head>


<body>

    <div class="login-wrapper">

        <div class="login-container">


            <!-- =========================
                 LEFT
            ========================= -->

            <div class="login-left">

                <div class="brand-logo">
                    <i class="bi bi-shop"></i>
                </div>

                <h1>
                    POS SYSTEM
                </h1>

                <p>
                    Sistem informasi penjualan untuk membantu
                    pengelolaan barang, transaksi, stok, dan laporan
                    secara lebih terorganisir.
                </p>


                <div class="feature-list">

                    <div class="feature-item">
                        <i class="bi bi-box-seam"></i>
                        <span>Pengelolaan data barang</span>
                    </div>

                    <div class="feature-item">
                        <i class="bi bi-cart-check"></i>
                        <span>Transaksi penjualan</span>
                    </div>

                    <div class="feature-item">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>Laporan dan monitoring</span>
                    </div>

                </div>

            </div>


            <!-- =========================
                 RIGHT
            ========================= -->

            <div class="login-right">

                <div class="login-form">

                    <h2>
                        Selamat Datang
                    </h2>

                    <p class="subtitle">
                        Silakan masuk untuk melanjutkan ke sistem.
                    </p>


                    <!-- ERROR -->

                    <?php if (session()->getFlashdata('error')) : ?>

                        <div class="alert alert-danger mb-4">

                            <i class="bi bi-exclamation-circle me-2"></i>

                            <?= session()->getFlashdata('error') ?>

                        </div>

                    <?php endif; ?>


                    <!-- FORM -->

                    <form
                        action="<?= base_url('login/process') ?>"
                        method="post">


                        <!-- USERNAME -->

                        <div class="mb-3">

                            <label class="form-label">
                                Username
                            </label>

                            <div class="input-group-custom">

                                <i class="bi bi-person"></i>

                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    placeholder="Masukkan username"
                                    autocomplete="username"
                                    required>

                            </div>

                        </div>


                        <!-- PASSWORD -->

                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <div class="input-group-custom">

                                <i class="bi bi-lock"></i>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Masukkan password"
                                    autocomplete="current-password"
                                    required>

                            </div>

                        </div>


                        <!-- BUTTON -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100 login-button">

                            <i class="bi bi-box-arrow-in-right me-2"></i>

                            Masuk ke Sistem

                        </button>

                    </form>


                    <div class="login-footer">

                        © <?= date('Y') ?> POS System

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>