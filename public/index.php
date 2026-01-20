<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Payroll System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/logo.png">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    backdropBlur: {
                        xs: '2px',
                    }
                }
            }
        }
    </script>

    <style>
        /* ===== Fade In ===== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 1.2s ease-out forwards;
        }

        /* ===== Card Glow ===== */
        .card-glow::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(
                120deg,
                rgba(59,130,246,.6),
                rgba(34,197,94,.6),
                rgba(168,85,247,.6)
            );
            filter: blur(35px);
            opacity: .55;
            z-index: -1;
        }

        /* ===== Button Glow ===== */
        .btn-glow:hover {
            box-shadow: 0 0 35px rgba(59,130,246,.8);
        }
        .btn-glow-green:hover {
            box-shadow: 0 0 35px rgba(34,197,94,.8);
        }

        /* ===== Icon Hover ===== */
        .btn-icon i {
            transition: .3s;
        }
        .btn-icon:hover i {
            transform: rotate(-6deg) scale(1.15);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center relative overflow-hidden bg-black">

    <!-- Background Image -->
    <div class="absolute inset-0 bg-cover bg-center scale-110"
         style="background-image:url('assets/payroll.png');"></div>

    <!-- Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br
                from-blue-900/50 via-black/60 to-purple-900/50"></div>

    <!-- Blur Overlay -->
    <div class="absolute inset-0 backdrop-blur-lg"></div>

    <!-- Main Card -->
    <div class="relative z-10 fade-in">
        <div class="
            relative card-glow
            w-[90vw] max-w-md
            rounded-3xl
            bg-white/15
            backdrop-blur-2xl
            border border-white/20
            shadow-2xl
            px-10 py-12
            text-center
            transition-all duration-500
            hover:scale-[1.03]
        ">

            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="
                    p-4 rounded-2xl
                    bg-white/20
                    backdrop-blur-xl
                    shadow-lg
                ">
                    <img src="assets/logo.png"
                         alt="Payroll Logo"
                         class="w-20 h-20 object-contain drop-shadow-xl">
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-extrabold tracking-widest text-white mb-2">
                PAYROLL SYSTEM
            </h1>
            <p class="text-sm tracking-wide text-white/70 mb-10">
                Modern & Secure Employee Payroll Management
            </p>

            <!-- Buttons -->
            <div class="flex flex-col gap-5">

                <!-- Login -->
                <a href="login.php"
                   class="
                        btn-glow btn-icon
                        flex items-center justify-center gap-3
                        w-full py-3.5 rounded-2xl
                        bg-gradient-to-r from-blue-600 to-blue-700
                        text-white font-semibold tracking-wide
                        transition-all duration-300
                        hover:scale-105
                        active:scale-95
                   ">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    LOGIN
                </a>

                <!-- Register -->
                <a href="register.php"
                   class="
                        btn-glow-green btn-icon
                        flex items-center justify-center gap-3
                        w-full py-3.5 rounded-2xl
                        bg-gradient-to-r from-green-600 to-green-700
                        text-white font-semibold tracking-wide
                        transition-all duration-300
                        hover:scale-105
                        active:scale-95
                   ">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    REGISTER
                </a>

            </div>

            <!-- Footer -->
            <p class="text-xs text-white/40 mt-8 tracking-wide">
                © <?= date('Y') ?> Payroll System • Secure & Reliable
            </p>

        </div>
    </div>

    <!-- Lucide Init -->
    <script>
        lucide.createIcons();
    </script>

</body>
</html>
