/**
 * resources/js/app.js
 *
 * Entry-point for Vite. Alpine.js is already bootstrapped by Livewire/Flux
 * (@fluxScripts / @livewireScripts), so we MUST NOT import or start Alpine here.
 *
 * We register custom Alpine.data() components by listening to the `alpine:init`
 * event that Livewire fires before Alpine.start(). This is the official pattern
 * for Livewire + Alpine integration to avoid double-init.
 */

import lessonPlayer from './alpine/lesson-player.js';
import quizPlayer   from './alpine/quiz-player.js';
import videoPlayer  from './alpine/video-player.js';

document.addEventListener('alpine:init', () => {
    Alpine.data('lessonPlayer', lessonPlayer);
    Alpine.data('quizPlayer',   quizPlayer);
    Alpine.data('videoPlayer',  videoPlayer);
});
