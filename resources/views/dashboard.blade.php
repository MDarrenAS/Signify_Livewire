
<x-layouts::app :title="__('Dashboard')">

    <style>
        .dashboard-heading {
            margin-bottom: 28px;
        }

        .dashboard-heading h1 {
            font-size: 30px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .dashboard-heading p {
            color: #828282;
            font-size: 15px;
            margin: 0;
        }

        .welcome-banner {
            background: #FDE9BF;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .welcome-banner h2 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 10px;
        }

        .welcome-banner p {
            color: #686868;
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
            max-width: 600px;
        }

        .dashboard-section-title {
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 260px;
            gap: 22px;
            align-items: start;
        }

        .module-card {
            background: #FFF;
            border: 1px solid #EAEAEA;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 15px;
        }

        .module-card h3 {
            font-size: 17px;
            font-weight: 700;
            margin: 0 0 9px;
        }

        .module-card p {
            color: #828282;
            font-size: 13px;
            line-height: 1.7;
            margin: 0 0 18px;
        }

        .module-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 12px;
            color: #828282;
        }

        .module-status {
            background: #FEF5E0;
            color: #D99B19;
            padding: 7px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .module-progress {
            height: 8px;
            background: #EEEEEE;
            border-radius: 20px;
            margin: 15px 0;
            overflow: hidden;
        }

        .module-progress span {
            display: block;
            height: 100%;
            width: 50%;
            background: #F9B92F;
            border-radius: 20px;
        }

        .module-button {
            display: inline-block;
            background: #F9B92F;
            color: #FFF;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
        }

        .module-button.disabled {
            background: #EAEAEA;
            color: #999;
            cursor: default;
        }

        .mission-card {
            background: #FFF;
            border: 1px solid #EAEAEA;
            border-radius: 18px;
            padding: 22px;
        }

        .mission-card h3 {
            font-size: 18px;
            margin: 0 0 20px;
        }

        .mission-xp {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .mission-coin {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #FEF5E0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 23px;
        }

        .mission-xp strong {
            font-size: 14px;
        }

        .mission-xp span {
            display: block;
            color: #828282;
            font-size: 12px;
            margin-top: 4px;
        }

        .mission-progress {
            height: 9px;
            background: #EEEEEE;
            border-radius: 20px;
            overflow: hidden;
            margin: 12px 0;
        }

        .mission-progress span {
            display: block;
            height: 100%;
            width: 48.6%;
            background: #F9B92F;
            border-radius: 20px;
        }

        .mission-percentage {
            text-align: right;
            font-size: 12px;
            color: #828282;
        }

        @media (max-width: 1050px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .dashboard-heading h1 {
                font-size: 23px;
            }

            .welcome-banner {
                padding: 22px;
            }

            .welcome-banner h2 {
                font-size: 20px;
            }
        }
    </style>

    <div class="dashboard-heading">
        <h1>Selamat Datang, {{ auth()->user()->name }}</h1>
        <p>Lanjutkan belajar mu hari ini</p>
    </div>

    <section class="welcome-banner">
        <h2>Belajar Bahasa Isyarat Jadi Lebih Mudah!</h2>
        <p>
            Yuk, lanjutkan perjalanan belajarmu bersama Signify.
            Pelajari bahasa isyarat secara bertahap dan menyenangkan.
        </p>
    </section>

    <div class="dashboard-content">

        <section id="modul-belajar">
            <h2 class="dashboard-section-title">Modul Belajar</h2>

            <article class="module-card">
                <h3>Abjad dan Ejaan</h3>
                <p>
                    Kuasai 26 isyarat abjad dasar sebagai fondasi awal
                    untuk mengeja namamu.
                </p>

                <div class="module-info">
                    <span class="module-status">Sedang Dipelajari</span>
                    <span>Tingkat Dasar</span>
                    <strong>50%</strong>
                </div>

                <div class="module-progress">
                    <span></span>
                </div>

                <a href="#" class="module-button">Lanjutkan</a>
            </article>

            <article class="module-card">
                <h3>Kata Sapaan</h3>
                <p>
                    Kenali berbagai isyarat sapaan sederhana
                    untuk memulai percakapan sehari-hari.
                </p>

                <div class="module-info">
                    <span class="module-status">Belum Dimulai</span>
                    <span>Tingkat Dasar</span>
                </div>

                <br>

                <a href="#" class="module-button disabled">Belum Tersedia</a>
            </article>

            <article class="module-card">
                <h3>Percakapan Sehari-hari</h3>
                <p>
                    Pelajari isyarat untuk berkomunikasi dalam
                    situasi sehari-hari.
                </p>

                <div class="module-info">
                    <span class="module-status">Terkunci</span>
                    <span>Tingkat Lanjutan</span>
                </div>

                <br>

                <a href="#" class="module-button disabled">Terkunci</a>
            </article>
        </section>

        <aside class="mission-card">
            <h3>Misi Harian</h3>

            <div class="mission-xp">
                <div class="mission-coin">⭐</div>
                <div>
                    <strong>Dapatkan 10 XP</strong>
                    <span>Selesaikan misi belajarmu</span>
                </div>
            </div>

            <div class="mission-progress">
                <span></span>
            </div>

            <div class="mission-percentage">48,6% selesai</div>
        </aside>

    </div>

</x-layouts::app>
