<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();

        // 1. Paket Kuis 1: Tantangan Abjad Visual A–Z
        $quizAbjad = Quiz::firstOrCreate(
            ['slug' => 'tantangan-abjad-visual-a-z'],
            [
                'title' => 'Tantangan Abjad Visual A–Z',
                'description' => 'Uji kecekatan membaca dan mengenali 26 alfabet BISINDO.',
                'level' => 'Pemula',
                'xp_reward' => 50,
                'icon_emoji' => '🔤',
                'status' => 'published',
                'sort_order' => 1,
                'created_by' => $admin?->id,
            ],
        );

        if ($quizAbjad->questions()->count() === 0) {
            // Soal 1: Huruf A (sign_to_word)
            $q1 = $quizAbjad->questions()->create([
                'question_text' => 'Isyarat dengan menyatukan ujung ibu jari dan telunjuk membentuk segitiga melambangkan huruf?',
                'question_type' => 'sign_to_word',
                'media_url' => '/storage/signs/abjad/a.mp4',
                'points' => 1,
                'sort_order' => 1,
            ]);

            $q1->options()->createMany([
                ['option_text' => 'Huruf A', 'is_correct' => true, 'sort_order' => 0],
                ['option_text' => 'Huruf B', 'is_correct' => false, 'sort_order' => 1],
                ['option_text' => 'Huruf C', 'is_correct' => false, 'sort_order' => 2],
                ['option_text' => 'Huruf D', 'is_correct' => false, 'sort_order' => 3],
            ]);

            // Soal 2: Huruf I (sign_to_word)
            $q2 = $quizAbjad->questions()->create([
                'question_text' => 'Mengacungkan jari kelingking tegak ke atas melambangkan huruf?',
                'question_type' => 'sign_to_word',
                'media_url' => '/storage/signs/abjad/i.mp4',
                'points' => 1,
                'sort_order' => 2,
            ]);

            $q2->options()->createMany([
                ['option_text' => 'Huruf U', 'is_correct' => false, 'sort_order' => 0],
                ['option_text' => 'Huruf I', 'is_correct' => true, 'sort_order' => 1],
                ['option_text' => 'Huruf E', 'is_correct' => false, 'sort_order' => 2],
                ['option_text' => 'Huruf O', 'is_correct' => false, 'sort_order' => 3],
            ]);

            // Soal 3: Huruf U (word_to_sign)
            $q3 = $quizAbjad->questions()->create([
                'question_text' => 'Manakah gerakan isyarat yang tepat untuk Huruf U?',
                'question_type' => 'word_to_sign',
                'points' => 1,
                'sort_order' => 3,
            ]);

            $q3->options()->createMany([
                ['option_text' => 'Dua jari telunjuk & tengah tegak rapat', 'is_correct' => true, 'sort_order' => 0],
                ['option_text' => 'Dua jari telunjuk & tengah membentuk huruf V terbuka', 'is_correct' => false, 'sort_order' => 1],
                ['option_text' => 'Tiga jari tengah, telunjuk, manis tegak', 'is_correct' => false, 'sort_order' => 2],
                ['option_text' => 'Jari telunjuk dan jempol membentuk lingkaran', 'is_correct' => false, 'sort_order' => 3],
            ]);
        }

        // 2. Paket Kuis 2: Sapaan & Perkenalan Kilat
        $quizSapaan = Quiz::firstOrCreate(
            ['slug' => 'sapaan-perkenalan-kilat'],
            [
                'title' => 'Sapaan & Perkenalan Kilat',
                'description' => 'Salam, terima kasih, perkenalan diri, dan panggilan sopan.',
                'level' => 'Pemula',
                'xp_reward' => 40,
                'icon_emoji' => '👋',
                'status' => 'published',
                'sort_order' => 2,
                'created_by' => $admin?->id,
            ],
        );

        if ($quizSapaan->questions()->count() === 0) {
            $q4 = $quizSapaan->questions()->create([
                'question_text' => 'Menyentuhkan ujung jari ke dagu lalu mengarahkannya ke depan berarti?',
                'question_type' => 'sign_to_word',
                'media_url' => '/storage/signs/sapaan/terima-kasih.mp4',
                'points' => 1,
                'sort_order' => 1,
            ]);

            $q4->options()->createMany([
                ['option_text' => 'Selamat Pagi', 'is_correct' => false, 'sort_order' => 0],
                ['option_text' => 'Terima Kasih', 'is_correct' => true, 'sort_order' => 1],
                ['option_text' => 'Sama-sama', 'is_correct' => false, 'sort_order' => 2],
                ['option_text' => 'Minta Maaf', 'is_correct' => false, 'sort_order' => 3],
            ]);

            $q5 = $quizSapaan->questions()->create([
                'question_text' => 'Melambaikan tangan kanan terbuka ke arah depan berarti?',
                'question_type' => 'sign_to_word',
                'media_url' => '/storage/signs/sapaan/halo.mp4',
                'points' => 1,
                'sort_order' => 2,
            ]);

            $q5->options()->createMany([
                ['option_text' => 'Halo / Hai', 'is_correct' => true, 'sort_order' => 0],
                ['option_text' => 'Sampai Jumpa', 'is_correct' => false, 'sort_order' => 1],
                ['option_text' => 'Kabar Baik', 'is_correct' => false, 'sort_order' => 2],
                ['option_text' => 'Siapa Namamu', 'is_correct' => false, 'sort_order' => 3],
            ]);
        }
    }
}
