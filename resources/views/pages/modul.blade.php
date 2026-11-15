
<x-layouts::app :title="'Modul Belajar'">
    <style>
        .module-page {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #333;
        }

        .module-heading {
            margin-bottom: 28px;
        }

        .module-heading h1 {
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .module-heading p {
            color: #828282;
            font-size: 14px;
        }

        .module-banner {
            background: #FDE9BF;
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 32px;
        }

        .module-banner h2 {
            font-size: 23px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .module-banner p {
            color: #62563F;
            font-size: 14px;
            line-height: 1.7;
            max-width: 600px;
        }

        .module-section-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .module-card {
            background: white;
            border: 1px solid #E8E8E8;
            border-radius: 18px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            min-height: 245px;
            transition: .2s ease;
        }

        .module-card:hover {
            border-color: #F9B92F;
            transform: translateY(-3px);
        }

        .module-card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .module-number {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: #FEF5E0;
            color: #D99A16;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 800;
            font-size: 18px;
        }

        .module-level {
            background: #F3F3F3;
            color: #777;
            border-radius: 20px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .module-card h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .module-card p {
            color: #828282;
            font-size: 13px;
            line-height: 1.7;
            margin-bottom: 22px;
        }

        .module-card-bottom {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .module-duration {
            color: #828282;
            font-size: 12px;
        }

        .module-button {
            display: inline-block;
            background: #F9B92F;
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .module-button:hover {
            background: #E9A91E;
        }

        .module-button.disabled {
            background: #EAEAEA;
            color: #999;
            cursor: not-allowed;
        }

        @media (max-width: 700px) {
            .module-grid {
                grid-template-columns: 1fr;
            }

            .module-banner {
                padding: 24px;
            }

            .module-heading h1 {
                font-size: 25px;
            }
        }
    </style>

    <div class="module-page">
        <div class="module-heading">
            <h1>Modul Belajar</h1>
            <p>Yuk, mulai perjalananmu mengenal Bahasa Isyarat Indonesia!</p>
        </div>

        <div class="module-banner">
            <h2>Belajar Bahasa Isyarat, Selangkah demi Selangkah!</h2>
            <p>
                Pelajari BISINDO mulai dari gerakan paling dasar hingga
                percakapan sehari-hari. Pilih modul dan belajar sesuai
                kecepatanmu sendiri.
            </p>
        </div>

        <h2 class="module-section-title">Daftar Modul Pembelajaran</h2>

        <div class="module-grid">
            <div class="module-card">
                <div class="module-card-top">
                    <div class="module-number">01</div>
                    <span class="module-level">TINGKAT DASAR</span>
                </div>
                <h3>Abjad dan Ejaan</h3>
                <p>
                    Kenali 26 isyarat abjad dasar dan pelajari cara
                    mengeja nama maupun kata menggunakan gerakan tangan.
                </p>
                <div class="module-card-bottom">
                    <span class="module-duration">Materi pemula</span>
                    <a href="#" class="module-button">Mulai Belajar</a>
                </div>
            </div>

            <div class="module-card">
                <div class="module-card-top">
                    <div class="module-number">02</div>
                    <span class="module-level">TINGKAT DASAR</span>
                </div>
                <h3>Kata Sapaan</h3>
                <p>
                    Pelajari berbagai ungkapan sederhana seperti halo,
                    terima kasih, maaf, dan salam perkenalan.
                </p>
                <div class="module-card-bottom">
                    <span class="module-duration">Materi pemula</span>
                    <a href="#" class="module-button">Mulai Belajar</a>
                </div>
            </div>

            <div class="module-card">
                <div class="module-card-top">
                    <div class="module-number">03</div>
                    <span class="module-level">TINGKAT MENENGAH</span>
                </div>
                <h3>Angka dan Waktu</h3>
                <p>
                    Kenali isyarat angka, bilangan, hari, tanggal,
                    dan ungkapan waktu dalam komunikasi sehari-hari.
                </p>
                <div class="module-card-bottom">
                    <span class="module-duration">Materi lanjutan</span>
                    <a href="#" class="module-button">Mulai Belajar</a>
                </div>
            </div>

            <div class="module-card">
                <div class="module-card-top">
                    <div class="module-number">04</div>
                    <span class="module-level">TINGKAT MENENGAH</span>
                </div>
                <h3>Percakapan Sehari-hari</h3>
                <p>
                    Gabungkan kosakata yang sudah dipelajari untuk
                    memahami percakapan sederhana dalam BISINDO.
                </p>
                <div class="module-card-bottom">
                    <span class="module-duration">Materi lanjutan</span>
                    <a href="#" class="module-button disabled">Segera Hadir</a>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>