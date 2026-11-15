
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signify - Belajar Bahasa Isyarat</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #FAFAFA;
            color: #252525;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            width: min(1280px, 90%);
            margin: auto;
        }

        .navbar {
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eeeeee;
            padding: 0 5%;
            background: #FAFAFA;
        }

        .logo {
            font-family: 'Caveat Brush', cursive;
            font-size: 48px;
            color: #F9B92F;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 38px;
            font-size: 15px;
            font-weight: 600;
        }

        .nav-links a:hover {
            color: #E6A51A;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            transition: .2s;
            cursor: pointer;
        }

        .btn-yellow {
            background: #F9B92F;
            color: white;
            box-shadow: 0 5px 12px #f9b92f35;
        }

        .btn-yellow:hover {
            background: #EBAA1F;
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 1.5px solid #F9B92F;
            color: #252525;
            background: transparent;
        }

        .btn-outline:hover {
            background: #FFF5DF;
        }

        /* HERO */

        .hero {
            position: relative;
            overflow: hidden;
            padding: 100px 0 120px;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 480px;
            height: 430px;
            left: -130px;
            top: 100px;
            background: #FDE9BF;
            border-radius: 48% 52% 60% 40%;
            z-index: -1;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 70px;
        }

        .hero-visual {
            min-height: 440px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .visual-main {
            width: 390px;
            height: 390px;
            background: #FDE9BF;
            border-radius: 48% 52% 40% 60%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .visual-card {
            background: white;
            border-radius: 22px;
            padding: 25px;
            box-shadow: 0 12px 35px #00000012;
            text-align: center;
        }

        .visual-card h3 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .visual-card p {
            color: #828282;
            font-size: 14px;
        }

        .visual-letters {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .visual-letters span {
            width: 55px;
            height: 65px;
            display: grid;
            place-items: center;
            background: #FFF5DF;
            border-radius: 12px;
            color: #D99A17;
            font-size: 28px;
            font-weight: 800;
        }

        .visual-bubble {
            position: absolute;
            background: white;
            padding: 13px 20px;
            border-radius: 15px;
            box-shadow: 0 8px 25px #00000012;
            font-weight: 700;
            font-size: 14px;
        }

        .bubble-one {
            top: 35px;
            right: 0;
        }

        .bubble-two {
            bottom: 35px;
            left: 0;
        }

        .hero-text h1 {
            font-size: clamp(36px, 4vw, 52px);
            line-height: 1.25;
            font-weight: 800;
            margin-bottom: 25px;
        }

        .hero-text h1 span {
            color: #E8A719;
        }

        .hero-text p {
            font-size: 18px;
            line-height: 1.9;
            color: #828282;
            margin-bottom: 35px;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        /* INTRODUCTION */

        .introduction {
            background: #FEF5E0;
            padding: 100px 0;
        }

        .intro-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 80px;
        }

        .section-label {
            color: #D99A17;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .intro-text h2,
        .section-heading h2 {
            font-size: clamp(30px, 3vw, 42px);
            line-height: 1.35;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .intro-text h2 span {
            font-family: 'Caveat Brush', cursive;
            color: #E8A719;
            font-size: 1.3em;
        }

        .intro-text > p {
            color: #777777;
            font-size: 16px;
            line-height: 1.9;
            margin-bottom: 30px;
        }

        .intro-features {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .intro-feature {
            background: white;
            border-radius: 15px;
            padding: 17px 20px;
            font-size: 14px;
            font-weight: 700;
        }

        .intro-feature span {
            display: block;
            color: #828282;
            font-size: 12px;
            font-weight: 500;
            margin-top: 5px;
        }

        .intro-visual {
            min-height: 330px;
            background: #FDE9BF;
            border-radius: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .intro-visual-card {
            background: white;
            padding: 45px;
            border-radius: 25px;
            text-align: center;
            box-shadow: 0 12px 30px #00000010;
        }

        .intro-visual-card strong {
            display: block;
            font-size: 26px;
            margin-bottom: 10px;
        }

        .intro-visual-card p {
            color: #828282;
            font-size: 14px;
        }

        .intro-tag {
            position: absolute;
            padding: 12px 18px;
            border-radius: 12px;
            background: white;
            font-size: 13px;
            font-weight: 800;
            box-shadow: 0 5px 15px #00000010;
        }

        .tag-free {
            top: 25px;
            left: 20px;
            color: #6B8A47;
        }

        .tag-inclusive {
            right: 20px;
            top: 90px;
            color: #D99A17;
        }

        .tag-fun {
            bottom: 25px;
            left: 45px;
            color: #6B8A47;
        }

        /* BENEFITS */

        .benefits {
            padding: 110px 0;
        }

        .section-heading {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 55px;
        }

        .section-heading p {
            color: #828282;
            font-size: 16px;
            line-height: 1.8;
        }

        .benefit-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .benefit-card {
            background: white;
            padding: 35px 30px;
            border-radius: 22px;
            border: 1px solid #F0F0F0;
            box-shadow: 0 8px 25px #00000006;
            transition: .2s;
        }

        .benefit-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 15px 30px #00000010;
        }

        .benefit-icon {
            width: 65px;
            height: 65px;
            display: grid;
            place-items: center;
            background: #FFF3D8;
            color: #D99A17;
            font-size: 28px;
            border-radius: 18px;
            margin-bottom: 25px;
        }

        .benefit-card h3 {
            font-size: 19px;
            margin-bottom: 15px;
        }

        .benefit-card p {
            color: #828282;
            font-size: 14px;
            line-height: 1.9;
        }

        /* CTA */

        .cta {
            background: #F9B92F;
            padding: 100px 20px 120px;
            text-align: center;
            overflow: hidden;
            position: relative;
        }

        .cta h2 {
            font-size: clamp(32px, 4vw, 48px);
            font-weight: 800;
            margin-bottom: 20px;
        }

        .cta p {
            max-width: 720px;
            margin: 0 auto 35px;
            line-height: 1.9;
            color: #57400F;
            font-size: 16px;
        }

        .btn-dark {
            background: #252525;
            color: white;
            padding: 17px 35px;
        }

        .btn-dark:hover {
            background: #414141;
            transform: translateY(-2px);
        }

        .cta-decoration {
            position: absolute;
            width: 180px;
            height: 180px;
            border: 25px solid #FFFFFF30;
            border-radius: 50%;
        }

        .decoration-left {
            left: -70px;
            bottom: -80px;
        }

        .decoration-right {
            right: -60px;
            top: -70px;
        }

        /* FOOTER */

        footer {
            padding: 35px 5%;
            background: #252525;
            color: white;
            text-align: center;
            font-size: 13px;
        }

        footer .logo {
            font-size: 35px;
            margin-bottom: 10px;
        }

        footer p {
            color: #BBBBBB;
        }

        /* RESPONSIVE */

        @media (max-width: 900px) {
            .nav-links {
                gap: 18px;
            }

            .hero-content,
            .intro-content {
                gap: 35px;
            }

            .visual-main {
                width: 310px;
                height: 310px;
            }

            .benefit-grid {
                gap: 15px;
            }
        }

        @media (max-width: 700px) {
            .navbar {
                flex-wrap: wrap;
                padding: 15px 5%;
                gap: 12px;
            }

            .logo {
                font-size: 38px;
            }

            .nav-links {
                order: 3;
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                font-size: 12px;
                gap: 18px;
            }

            .nav-actions .btn {
                padding: 10px 15px;
                font-size: 12px;
            }

            .hero {
                padding: 60px 0 80px;
            }

            .hero-content,
            .intro-content {
                grid-template-columns: 1fr;
            }

            .hero-visual {
                min-height: 330px;
                order: 2;
            }

            .hero-text {
                order: 1;
            }

            .hero-text p {
                font-size: 15px;
            }

            .visual-main {
                width: 280px;
                height: 280px;
            }

            .introduction,
            .benefits {
                padding: 70px 0;
            }

            .intro-visual {
                min-height: 280px;
            }

            .benefit-grid {
                grid-template-columns: 1fr;
            }

            .cta {
                padding: 75px 20px;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="logo">Signify</a>

        <nav class="nav-links">
            <a href="#beranda">Beranda</a>
            <a href="#tentang">Tentang Kami</a>
            <a href="#keunggulan">Keunggulan</a>
        </nav>

        <div class="nav-actions">
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="btn btn-outline">Masuk</a>
            @endif

            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-yellow">Daftar</a>
            @endif
        </div>
    </header>

    <main>

        <!-- HERO -->
        <section class="hero" id="beranda">
            <div class="container hero-content">

                <div class="hero-visual">
                    <div class="visual-main">
                        <div class="visual-card">
                            <h3>Belajar BISINDO</h3>
                            <p>Mulai dari hal sederhana!</p>

                            <div class="visual-letters">
                                <span>A</span>
                                <span>B</span>
                                <span>C</span>
                            </div>
                        </div>
                    </div>

                    <div class="visual-bubble bubble-one">
                        ✨ Belajar Jadi Seru!
                    </div>

                    <div class="visual-bubble bubble-two">
                        👋 Yuk, Mulai Belajar!
                    </div>
                </div>

                <div class="hero-text">
                    <h1>
                        Cara seru dan efektif belajar
                        <span>bahasa isyarat!</span>
                    </h1>

                    <p>
                        Belajar BISINDO kini jadi seru dan menyenangkan lewat
                        modul interaktif, latihan visual yang mudah diikuti,
                        dan kamus isyarat terlengkap.
                    </p>

                    <div class="hero-buttons">
                        <a href="{{ Route::has('register') ? route('register') : '#tentang' }}"
                           class="btn btn-yellow">
                            Mulai Belajar
                        </a>

                        <a href="#tentang" class="btn btn-outline">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- INTRODUCTION -->
        <section class="introduction" id="tentang">
            <div class="container intro-content">

                <div class="intro-text">
                    <div class="section-label">Tentang Signify</div>

                    <h2>
                        Kenalan Dengan
                        <span>Signify</span>
                    </h2>

                    <p>
                        Signify hadir sebagai teman belajar bahasa isyarat
                        yang membantu kamu mengenal dan memahami BISINDO
                        dengan cara yang lebih mudah, interaktif, dan
                        menyenangkan. Cukup luangkan waktu 5–10 menit
                        setiap hari untuk mulai belajar.
                    </p>

                    <div class="intro-features">
                        <div class="intro-feature">
                            5–10 menit sehari
                            <span>Belajar tanpa terburu-buru</span>
                        </div>

                        <div class="intro-feature">
                            Latihan kapan saja
                            <span>Belajar sesuai waktumu</span>
                        </div>
                    </div>
                </div>

                <div class="intro-visual">
                    <div class="intro-visual-card">
                        <strong>Belajar Tanpa Batas</strong>
                        <p>Mulai perjalanan bahasa isyaratmu bersama Signify.</p>
                    </div>

                    <div class="intro-tag tag-free">GRATIS</div>
                    <div class="intro-tag tag-inclusive">INKLUSIF</div>
                    <div class="intro-tag tag-fun">MENYENANGKAN</div>
                </div>

            </div>
        </section>

        <!-- BENEFITS -->
        <section class="benefits" id="keunggulan">
            <div class="container">

                <div class="section-heading">
                    <div class="section-label">Keunggulan Signify</div>

                    <h2>Belajar Bahasa Isyarat Lebih Menyenangkan</h2>

                    <p>
                        Temukan pengalaman belajar bahasa isyarat yang
                        lebih mudah melalui berbagai fitur interaktif
                        yang tersedia di Signify.
                    </p>
                </div>

                <div class="benefit-grid">

                    <article class="benefit-card">
                        <div class="benefit-icon">📖</div>

                        <h3>Kamus Isyarat Visual</h3>

                        <p>
                            Lihat ribuan video atau gambar contoh gerakan
                            tangan dari berbagai sudut pandang yang jelas
                            dan mudah ditiru.
                        </p>
                    </article>

                    <article class="benefit-card">
                        <div class="benefit-icon">✋</div>

                        <h3>Latihan Praktik Interaktif</h3>

                        <p>
                            Latih gerakan tanganmu secara langsung di depan
                            layar dengan panduan visual yang seru seperti
                            bermain game!
                        </p>
                    </article>

                    <article class="benefit-card">
                        <div class="benefit-icon">📚</div>

                        <h3>Langkah Belajar Rapi & Santai</h3>

                        <p>
                            Mulai dari abjad, kata sapaan manis, sampai
                            obrolan sehari-hari langkah demi langkah
                            tanpa rasa terbebani.
                        </p>
                    </article>

                </div>
            </div>
        </section>

        <!-- CALL TO ACTION -->
        <section class="cta">
            <div class="cta-decoration decoration-left"></div>
            <div class="cta-decoration decoration-right"></div>

            <div class="container">
                <h2>Satu Gerakan, Ribuan Makna</h2>

                <p>
                    Tidak ada kata terlambat untuk mulai belajar.
                    Kuasai bahasa isyarat dengan modul interaktif kami
                    dan jadilah bagian dari masyarakat tanpa batas komunikasi.
                </p>

                <a href="{{ Route::has('register') ? route('register') : route('home') }}"
                   class="btn btn-dark">
                    Mulai Perjalananmu
                </a>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer>
        <div class="logo">Signify</div>
        <p>Belajar bahasa isyarat, bangun komunikasi tanpa batas.</p>
        <p style="margin-top: 15px;">
            &copy; {{ date('Y') }} Signify. All rights reserved.
        </p>
    </footer>

</body>
</html>
