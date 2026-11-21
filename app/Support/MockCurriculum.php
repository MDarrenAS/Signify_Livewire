<?php

namespace App\Support;

class MockCurriculum
{
    public static function getUnits(): array
    {
        return [
            [
                'id'          => 1,
                'badge'       => 'UNIT 1 • TINGKAT DASAR',
                'title'       => 'Pondasi Komunikasi BISINDO',
                'description' => 'Kuasai abjad visual, angka dasar, dan sapaan penting untuk memulai percakapan.',
                'is_locked'   => false,
                'modules'     => [
                    self::abjadModule(),
                    self::angkaModule(),
                    [
                        'slug'        => 'kata-ganti-sapaan',
                        'title'       => 'Kata Ganti & Sapaan Dasar',
                        'description' => 'Terkunci • Selesaikan Modul Angka terlebih dahulu',
                        'status'      => 'locked',
                        'total_parts' => 3,
                        'parts'       => [],
                    ],
                ],
            ],
        ];
    }

    public static function findModuleAndPart(string $modulSlug, string $partSlug): ?array
    {
        foreach (self::getUnits() as $unit) {
            foreach ($unit['modules'] as $modul) {
                if (($modul['slug'] ?? '') !== $modulSlug) {
                    continue;
                }

                $parts = $modul['parts'] ?? [];
                foreach ($parts as $index => $part) {
                    if (($part['slug'] ?? '') === $partSlug) {
                        return [
                            'unit'      => $unit,
                            'modul'     => $modul,
                            'part'      => $part,
                            'partIndex' => $index,
                            'totalParts'=> count($parts),
                            'nextPart'  => $parts[$index + 1] ?? null,
                            'prevPart'  => $parts[$index - 1] ?? null,
                        ];
                    }
                }
            }
        }

        return null;
    }

    private static function abjadModule(): array
    {
        return [
            'slug'           => 'abjad-dan-ejaan',
            'title'          => 'Abjad & Ejaan',
            'description'    => 'Kuasai 26 isyarat huruf dasar BISINDO (6 Bagian).',
            'xp_reward'      => 70,
            'visual_content' => 'A-Z',
            'is_expanded'    => false,
            'parts'          => [
                [
                    'order'      => 1,
                    'type'       => 'lesson',
                    'slug'       => 'huruf-vokal',
                    'title'      => 'Huruf Vokal',
                    'subtitle'   => 'A, I, U, E, O',
                    'category'   => 'Huruf Vokal • BISINDO',
                    'badge_text' => '5 Isyarat',
                    'status'     => 'completed',
                    'xp_reward'  => 10,
                    'items'      => [
                        [
                            'order'       => 1,
                            'symbol'      => 'A',
                            'title'       => 'Huruf A',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/a.mp4',
                            'instruction' => 'Pertemukan ujung ibu jari dan jari telunjuk kedua tangan di depan dada hingga membentuk sudut segitiga runcing menyerupai puncak huruf A.',
                            'una_tip'     => 'Pastikan ujung telunjuk dan ibu jari saling menyentuh rapat agar bentuk segitiganya terlihat jelas dari depan.',
                        ],
                        [
                            'order'       => 2,
                            'symbol'      => 'I',
                            'title'       => 'Huruf I',
                            'hand_type'   => '1 Tangan',
                            'video_url'   => '/storage/signs/abjad/i.mp4',
                            'instruction' => 'Angkat tangan kanan menghadap ke depan, tegakkan jari kelingking lurus ke atas, sementara keempat jari lainnya mengepal rapat.',
                            'una_tip'     => 'Cukup satu tangan saja! Jaga posisi kelingking tetap diam dan tegak agar tidak tertukar dengan huruf J.',
                        ],
                        [
                            'order'       => 3,
                            'symbol'      => 'U',
                            'title'       => 'Huruf U',
                            'hand_type'   => '1 Tangan',
                            'video_url'   => '/storage/signs/abjad/u.mp4',
                            'instruction' => 'Angkat tangan kanan menghadap ke depan, tegakkan jari telunjuk dan jari tengah sejajar ke atas dengan sedikit celah membentuk huruf U.',
                            'una_tip'     => 'Beri sedikit ruang di antara telunjuk dan jari tengah supaya terlihat seperti lengkungan huruf U.',
                        ],
                        [
                            'order'       => 4,
                            'symbol'      => 'E',
                            'title'       => 'Huruf E',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/e.mp4',
                            'instruction' => 'Tegakkan jari telunjuk tangan kiri secara vertikal, lalu tempelkan tiga jari tangan kanan (telunjuk, tengah, manis) secara mendatar di sisi kanan telunjuk kiri.',
                            'una_tip'     => 'Ingat jumlahnya: huruf E memakai tiga jari mendatar, sedangkan huruf F hanya memakai dua jari.',
                        ],
                        [
                            'order'       => 5,
                            'symbol'      => 'O',
                            'title'       => 'Huruf O',
                            'hand_type'   => '1 Tangan',
                            'video_url'   => '/storage/signs/abjad/o.mp4',
                            'instruction' => 'Temukan ujung ibu jari dan ujung jari telunjuk tangan kanan membentuk lingkaran bulat menyerupai huruf O, sementara jari lainnya rileks.',
                            'una_tip'     => 'Bentuk lingkaran yang bulat dan terbuka di tengah agar mudah dibaca lawan bicara.',
                        ],
                    ],
                ],
                [
                    'order'      => 2,
                    'type'       => 'lesson',
                    'slug'       => 'konsonan-awal',
                    'title'      => 'Konsonan Awal',
                    'subtitle'   => 'B, C, D, F, G',
                    'category'   => 'Konsonan • BISINDO',
                    'badge_text' => '5 Isyarat',
                    'status'     => 'completed',
                    'xp_reward'  => 10,
                    'items'      => [
                        [
                            'order'       => 1,
                            'symbol'      => 'B',
                            'title'       => 'Huruf B',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/b.mp4',
                            'instruction' => 'Tegakkan telapak tangan kiri lurus ke atas, lalu bentuk dua lengkungan dengan ibu jari dan jari tangan kanan menempel di sisi kanan tangan kiri.',
                            'una_tip'     => 'Tangan kiri berperan sebagai tiang tegak, sedangkan tangan kanan membentuk dua perut huruf B.',
                        ],
                        [
                            'order'       => 2,
                            'symbol'      => 'C',
                            'title'       => 'Huruf C',
                            'hand_type'   => '1 Tangan',
                            'video_url'   => '/storage/signs/abjad/c.mp4',
                            'instruction' => 'Lengkungkan ibu jari dan keempat jari tangan kanan secara bersamaan menghadap ke samping membentuk setengah lingkaran huruf C.',
                            'una_tip'     => 'Rapatkan keempat jari bagian atas agar lengkungan huruf C terlihat tegas.',
                        ],
                        [
                            'order'       => 3,
                            'symbol'      => 'D',
                            'title'       => 'Huruf D',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/d.mp4',
                            'instruction' => 'Tegakkan jari telunjuk tangan kiri sebagai tiang vertikal, lalu lengkungkan ibu jari dan jari telunjuk tangan kanan menempel dari ujung atas hingga pangkal bawahnya.',
                            'una_tip'     => 'Berbeda dengan B yang punya dua lengkungan, huruf D hanya membentuk satu lengkungan penuh.',
                        ],
                        [
                            'order'       => 4,
                            'symbol'      => 'F',
                            'title'       => 'Huruf F',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/f.mp4',
                            'instruction' => 'Tegakkan jari telunjuk tangan kiri secara vertikal, lalu tempelkan jari telunjuk dan jari tengah tangan kanan secara mendatar di bagian atas dan tengah telunjuk kiri.',
                            'una_tip'     => 'Gunakan dua jari mendatar di bagian atas tiang telunjuk kiri untuk membentuk huruf F.',
                        ],
                        [
                            'order'       => 5,
                            'symbol'      => 'G',
                            'title'       => 'Huruf G',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/g.mp4',
                            'instruction' => 'Bentuk lengkungan huruf C dengan tangan kanan, lalu arahkan jari telunjuk tangan kiri secara mendatar masuk ke bagian tengah lengkungan tersebut.',
                            'una_tip'     => 'Telunjuk kiri berfungsi sebagai garis palang horizontal pada huruf G.',
                        ],
                    ],
                ],
                [
                    'order'      => 3,
                    'type'       => 'lesson',
                    'slug'       => 'konsonan-tengah-1',
                    'title'      => 'Konsonan Tengah I',
                    'subtitle'   => 'H, J, K, L, M',
                    'category'   => 'Konsonan • BISINDO',
                    'badge_text' => '5 Isyarat',
                    'status'     => 'completed',
                    'xp_reward'  => 10,
                    'items'      => [
                        [
                            'order'       => 1,
                            'symbol'      => 'H',
                            'title'       => 'Huruf H',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/h.mp4',
                            'instruction' => 'Tegakkan jari telunjuk kiri dan jari telunjuk kanan secara sejajar vertikal, lalu hubungkan bagian tengah keduanya menggunakan ibu jari kanan.',
                            'una_tip'     => 'Bayangkan dua telunjukmu adalah tiang kiri dan kanan, dan ibu jarimu adalah jembatan penghubungnya.',
                        ],
                        [
                            'order'       => 2,
                            'symbol'      => 'J',
                            'title'       => 'Huruf J',
                            'hand_type'   => '1 Tangan (Bergerak)',
                            'video_url'   => '/storage/signs/abjad/j.mp4',
                            'instruction' => 'Tegakkan jari kelingking tangan kanan seperti huruf I, kemudian gerakkan ke bawah sambil melengkung ke arah dalam membentuk kait huruf J.',
                            'una_tip'     => 'Gunakan tombol 0.5x Lambat untuk melihat arah ayunan kelingking dari atas ke bawah.',
                        ],
                        [
                            'order'       => 3,
                            'symbol'      => 'K',
                            'title'       => 'Huruf K',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/k.mp4',
                            'instruction' => 'Tegakkan jari telunjuk tangan kiri secara vertikal, lalu buka jari telunjuk dan jari tengah tangan kanan membentuk sudut miring yang menempel di tengah telunjuk kiri.',
                            'una_tip'     => 'Jangan tertukar dengan huruf V ya! Pastikan ibu jari atau pangkal jari menyentuh tengah telunjuk kiri.',
                        ],
                        [
                            'order'       => 4,
                            'symbol'      => 'L',
                            'title'       => 'Huruf L',
                            'hand_type'   => '1 Tangan',
                            'video_url'   => '/storage/signs/abjad/l.mp4',
                            'instruction' => 'Angkat tangan kanan menghadap ke depan, rentangkan jari telunjuk lurus ke atas dan ibu jari lurus ke samping membentuk sudut siku-siku 90 derajat.',
                            'una_tip'     => 'Lipat rapat jari tengah, manis, dan kelingking ke telapak tangan agar bentuk L terlihat tajam.',
                        ],
                        [
                            'order'       => 5,
                            'symbol'      => 'M',
                            'title'       => 'Huruf M',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/m.mp4',
                            'instruction' => 'Buka telapak tangan kiri menghadap ke atas, lalu tempelkan tiga ujung jari tangan kanan (telunjuk, tengah, manis) mengarah ke bawah di atas telapak tangan kiri.',
                            'una_tip'     => 'Hitung kakinya! Huruf M menggunakan tiga jari, sedangkan huruf N hanya dua jari.',
                        ],
                    ],
                ],
                [
                    'order'      => 4,
                    'type'       => 'lesson',
                    'slug'       => 'konsonan-tengah-2',
                    'title'      => 'Konsonan Tengah II',
                    'subtitle'   => 'N, P, Q, R, S',
                    'category'   => 'Konsonan • BISINDO',
                    'badge_text' => '5 Isyarat',
                    'status'     => 'completed',
                    'xp_reward'  => 10,
                    'items'      => [
                        [
                            'order'       => 1,
                            'symbol'      => 'N',
                            'title'       => 'Huruf N',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/n.mp4',
                            'instruction' => 'Buka telapak tangan kiri menghadap ke atas, lalu tempelkan dua ujung jari tangan kanan (telunjuk dan tengah) mengarah ke bawah di atas telapak tangan kiri.',
                            'una_tip'     => 'Jangan tertukar dengan M ya! Cukup tempelkan telunjuk dan jari tengah saja.',
                        ],
                        [
                            'order'       => 2,
                            'symbol'      => 'P',
                            'title'       => 'Huruf P',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/p.mp4',
                            'instruction' => 'Tegakkan jari telunjuk tangan kiri secara vertikal, lalu lengkungkan ibu jari dan jari telunjuk tangan kanan menempel di bagian atas telunjuk kiri.',
                            'una_tip'     => 'Lengkungan kepala huruf P berada di setengah bagian atas telunjuk kiri, berbeda dengan D yang penuh sampai bawah.',
                        ],
                        [
                            'order'       => 3,
                            'symbol'      => 'Q',
                            'title'       => 'Huruf Q',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/q.mp4',
                            'instruction' => 'Bentuk lingkaran bulat menggunakan ibu jari dan jari telunjuk tangan kiri, lalu selipkan jari telunjuk tangan kanan menyilang di bagian bawah lingkaran tersebut.',
                            'una_tip'     => 'Telunjuk kanan berperan sebagai ekor miring di bagian bawah huruf Q.',
                        ],
                        [
                            'order'       => 4,
                            'symbol'      => 'R',
                            'title'       => 'Huruf R',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/r.mp4',
                            'instruction' => 'Tegakkan jari telunjuk kiri vertikal, tempelkan lengkungan telunjuk kanan di bagian atas, lalu julurkan jari tengah kanan miring ke bawah sebagai kaki huruf R.',
                            'una_tip'     => 'Posisinya mirip huruf P, namun ditambahkan jari tengah kanan yang menjulur miring ke bawah.',
                        ],
                        [
                            'order'       => 5,
                            'symbol'      => 'S',
                            'title'       => 'Huruf S',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/s.mp4',
                            'instruction' => 'Lengkungkan jari kelingking tangan kiri dan jari kelingking tangan kanan, lalu kaitkan keduanya secara berlawanan arah hingga menyerupai lekukan huruf S.',
                            'una_tip'     => 'Kaitkan kedua kelingking dengan lembut di depan dada agar lekukan S mudah dikenali.',
                        ],
                    ],
                ],
                [
                    'order'      => 5,
                    'type'       => 'lesson',
                    'slug'       => 'konsonan-akhir',
                    'title'      => 'Konsonan Akhir',
                    'subtitle'   => 'T, V, W, X, Y, Z',
                    'category'   => 'Konsonan • BISINDO',
                    'badge_text' => '6 Isyarat',
                    'status'     => 'completed',
                    'xp_reward'  => 10,
                    'items'      => [
                        [
                            'order'       => 1,
                            'symbol'      => 'T',
                            'title'       => 'Huruf T',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/t.mp4',
                            'instruction' => 'Tegakkan jari telunjuk tangan kiri lurus ke atas, lalu letakkan jari telunjuk tangan kanan secara mendatar tepat di atas ujung telunjuk kiri membentuk huruf T.',
                            'una_tip'     => 'Pastikan telunjuk kanan berada tepat di puncak ujung telunjuk kiri, bukan di tengah.',
                        ],
                        [
                            'order'       => 2,
                            'symbol'      => 'V',
                            'title'       => 'Huruf V',
                            'hand_type'   => '1 Tangan',
                            'video_url'   => '/storage/signs/abjad/v.mp4',
                            'instruction' => 'Angkat tangan kanan menghadap ke depan, buka lebar jari telunjuk dan jari tengah membentuk sudut runcing menyerupai huruf V.',
                            'una_tip'     => 'Buka kedua jari lebih lebar daripada huruf U agar sudut lancip V terlihat berbeda.',
                        ],
                        [
                            'order'       => 3,
                            'symbol'      => 'W',
                            'title'       => 'Huruf W',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/w.mp4',
                            'instruction' => 'Bentuk huruf V pada tangan kiri dan tangan kanan (telunjuk dan jari tengah terbuka), lalu pertemukan kedua ujung ibu jari di tengah hingga membentuk gelombang W.',
                            'una_tip'     => 'Gabungkan dua bentuk huruf V berdampingan untuk menghasilkan huruf W.',
                        ],
                        [
                            'order'       => 4,
                            'symbol'      => 'X',
                            'title'       => 'Huruf X',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/x.mp4',
                            'instruction' => 'Silangkan jari telunjuk tangan kanan di atas jari telunjuk tangan kiri pada bagian tengahnya hingga membentuk tanda silang huruf X.',
                            'una_tip'     => 'Posisikan kedua telunjuk miring secara simetris supaya membentuk tanda silang yang jelas.',
                        ],
                        [
                            'order'       => 5,
                            'symbol'      => 'Y',
                            'title'       => 'Huruf Y',
                            'hand_type'   => '2 Tangan',
                            'video_url'   => '/storage/signs/abjad/y.mp4',
                            'instruction' => 'Buka jari telunjuk dan jari tengah tangan kiri membentuk cabang huruf V, lalu tempelkan ujung jari telunjuk tangan kanan secara vertikal tepat di pangkal bawah cabang tersebut.',
                            'una_tip'     => 'Tangan kiri membentuk cabang atas, dan telunjuk kanan menjadi batang tegak di bawahnya.',
                        ],
                        [
                            'order'       => 6,
                            'symbol'      => 'Z',
                            'title'       => 'Huruf Z',
                            'hand_type'   => '1 Tangan (Bergerak)',
                            'video_url'   => '/storage/signs/abjad/z.mp4',
                            'instruction' => 'Arahkan jari telunjuk tangan kanan ke depan, lalu gerakkan secara berurutan ke samping kanan, serong kiri bawah, dan mendatar ke kanan membentuk pola Z.',
                            'una_tip'     => 'Gerakkan telunjukmu seolah sedang menggambar huruf Z di udara dari sudut pandang lawan bicara.',
                        ],
                    ],
                ],
                [
                    'order'         => 6,
                    'type'          => 'quiz',
                    'slug'          => 'kuis-evaluasi-abjad',
                    'title'         => 'Kuis Evaluasi Abjad',
                    'subtitle'      => '6 Soal Latihan & Ejaan Kata',
                    'badge_text'    => 'Skor 100 • Sempurna',
                    'status'        => 'completed',
                    'xp_reward'     => 20,
                    'passing_score' => 80,
                    'questions'     => [
                        [
                            'order'         => 1,
                            'question'      => 'Huruf vokal manakah yang diperagakan pada gerakan BISINDO berikut?',
                            'media_url'     => '/storage/signs/abjad/a.mp4',
                            'symbol_hint'   => 'A',
                            'options'       => ['Huruf A', 'Huruf E', 'Huruf O', 'Huruf U'],
                            'correct_index' => 0,
                            'explanation'   => 'Pertemuan ujung ibu jari dan telunjuk kedua tangan membentuk sudut puncak huruf A.',
                        ],
                        [
                            'order'         => 2,
                            'question'      => 'Gerakan tangan berikut menunjukkan huruf konsonan...',
                            'media_url'     => '/storage/signs/abjad/k.mp4',
                            'symbol_hint'   => 'K',
                            'options'       => ['Huruf H', 'Huruf K', 'Huruf R', 'Huruf V'],
                            'correct_index' => 1,
                            'explanation'   => 'Telunjuk kiri tegak dengan dua jari kanan menempel menyamping di tengah merupakan isyarat huruf K.',
                        ],
                        [
                            'order'         => 3,
                            'question'      => 'Perhatikan jumlah jari yang menempel di telapak tangan kiri. Huruf apakah ini?',
                            'media_url'     => '/storage/signs/abjad/n.mp4',
                            'symbol_hint'   => 'N',
                            'options'       => ['Huruf M', 'Huruf N', 'Huruf W', 'Huruf E'],
                            'correct_index' => 1,
                            'explanation'   => 'Dua jari (telunjuk dan tengah) di atas telapak tangan kiri menandakan huruf N, sedangkan M memakai tiga jari.',
                        ],
                        [
                            'order'         => 4,
                            'question'      => 'Isyarat 1 tangan yang digerakkan membentuk pola zig-zag di udara melambangkan huruf...',
                            'media_url'     => '/storage/signs/abjad/z.mp4',
                            'symbol_hint'   => 'Z',
                            'options'       => ['Huruf J', 'Huruf S', 'Huruf X', 'Huruf Z'],
                            'correct_index' => 3,
                            'explanation'   => 'Huruf Z diperagakan menggunakan satu telunjuk yang menggambar jalur zig-zag di udara.',
                        ],
                        [
                            'order'         => 5,
                            'question'      => 'Simak rangkaian ejaan 3 huruf BISINDO berikut. Kata apakah yang dieja?',
                            'media_url'     => '/storage/signs/ejaan/ibu.mp4',
                            'symbol_hint'   => 'I - B - U',
                            'options'       => ['A - P - I', 'I - B - U', 'U - B - I', 'A - B - U'],
                            'correct_index' => 1,
                            'explanation'   => 'Urutan isyarat tersebut adalah kelingking tegak (I), dua lengkungan (B), dan dua jari sejajar (U) membentuk kata IBU.',
                        ],
                        [
                            'order'         => 6,
                            'question'      => 'Simak rangkaian ejaan 4 huruf BISINDO berikut. Kata apakah yang dieja?',
                            'media_url'     => '/storage/signs/ejaan/buku.mp4',
                            'symbol_hint'   => 'B - U - K - U',
                            'options'       => ['B - A - T - U', 'K - U - K - U', 'B - U - K - U', 'M - A - T - A'],
                            'correct_index' => 2,
                            'explanation'   => 'Rangkaian gerakan tersebut mengeja huruf B - U - K - U secara berurutan.',
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function angkaModule(): array
    {
        return [
            'slug'           => 'angka-dan-hitungan',
            'title'          => 'Angka & Hitungan',
            'description'    => 'Belajar angka satuan hingga ribuan dalam isyarat BISINDO.',
            'tag'            => 'SEDANG AKTIF',
            'visual_content' => '1 2 3',
            'is_expanded'    => true,
            'parts'          => [
                [
                    'order'      => 1,
                    'type'       => 'lesson',
                    'slug'       => 'angka-satuan',
                    'title'      => 'Angka Satuan (0–9)',
                    'subtitle'   => '0, 1, 2, 3, 4, 5',
                    'category'   => 'Angka Dasar • BISINDO',
                    'badge_text' => '10 Isyarat',
                    'status'     => 'completed',
                    'xp_reward'  => 10,
                    'items'      => [
                        ['order' => 1, 'symbol' => '1', 'title' => 'Angka 1', 'hand_type' => '1 Tangan', 'video_url' => '/storage/signs/angka/1.mp4', 'instruction' => 'Tegakkan jari telunjuk tangan kanan lurus ke atas menghadap ke depan.', 'una_tip' => 'Pastikan jari lainnya mengepal rapat di telapak tangan.'],
                        ['order' => 2, 'symbol' => '2', 'title' => 'Angka 2', 'hand_type' => '1 Tangan', 'video_url' => '/storage/signs/angka/2.mp4', 'instruction' => 'Tegakkan jari telunjuk dan jari tengah tangan kanan membentuk tanda dua.', 'una_tip' => 'Mirip huruf V, hadapkan telapak tangan ke lawan bicara.'],
                    ],
                ],
                [
                    'order'      => 2,
                    'type'       => 'lesson',
                    'slug'       => 'angka-belasan-puluhan',
                    'title'      => 'Angka Belasan & Puluhan',
                    'subtitle'   => '10, 11, 20, 50',
                    'category'   => 'Angka Lanjutan • BISINDO',
                    'badge_text' => '8 Isyarat',
                    'status'     => 'completed',
                    'xp_reward'  => 10,
                    'items'      => [
                        ['order' => 1, 'symbol' => '10', 'title' => 'Angka 10', 'hand_type' => '1 Tangan (Bergerak)', 'video_url' => '/storage/signs/angka/10.mp4', 'instruction' => 'Peragakan angka 1 diikuti gerakan angka 0 secara berurutan.', 'una_tip' => 'Gunakan ritme yang stabil saat berpindah dari angka 1 ke 0.'],
                    ],
                ],
                [
                    'order'      => 3,
                    'type'       => 'lesson',
                    'slug'       => 'angka-ratusan-ribuan',
                    'title'      => 'Angka Ratusan & Ribuan',
                    'subtitle'   => '100, 500, 1.000',
                    'category'   => 'Angka Besar • BISINDO',
                    'badge_text' => '6 Isyarat',
                    'status'     => 'not_started',
                    'xp_reward'  => 15,
                    'items'      => [
                        ['order' => 1, 'symbol' => '100', 'title' => 'Seratus (100)', 'hand_type' => '1 Tangan (Bergerak)', 'video_url' => '/storage/signs/angka/100.mp4', 'instruction' => 'Peragakan angka 1 lalu tarik tangan membentuk lengkungan huruf C ke samping.', 'una_tip' => 'Lengkungan C setelah angka menandakan satuan ratusan dalam BISINDO.'],
                        ['order' => 2, 'symbol' => '1K', 'title' => 'Seribu (1.000)', 'hand_type' => '2 Tangan', 'video_url' => '/storage/signs/angka/1000.mp4', 'instruction' => 'Peragakan angka 1 dengan tangan kanan, lalu sentuhkan ujung jari ke telapak tangan kiri.', 'una_tip' => 'Sentuhan ke telapak tangan kiri menandakan kelipatan ribuan.'],
                    ],
                ],
                [
                    'order'         => 4,
                    'type'          => 'quiz',
                    'slug'          => 'kuis-angka',
                    'title'         => 'Kuis Hitungan Harian',
                    'subtitle'      => 'Evaluasi Angka BISINDO',
                    'badge_text'    => 'Kuis',
                    'status'        => 'not_started',
                    'xp_reward'     => 20,
                    'passing_score' => 80,
                    'questions'     => [
                        [
                            'order'         => 1,
                            'question'      => 'Gerakan tambahan apa yang menandakan satuan ratusan setelah angka satuan?',
                            'media_url'     => '/storage/signs/angka/100.mp4',
                            'symbol_hint'   => '100',
                            'options'       => ['Lengkungan C ke samping', 'Kelingking digerakkan ke bawah', 'Dua telunjuk disilangkan', 'Ibu jari ditekuk'],
                            'correct_index' => 0,
                            'explanation'   => 'Satuan ratusan ditandai dengan tarikan lengkungan menyerupai huruf C setelah angka.',
                        ],
                    ],
                ],
            ],
        ];
    }
}