/**
 * Alpine.js component: videoPlayer
 *
 * Manages state for the inline video player used inside the lesson (materi) view.
 * Watches `activeIndex` from the parent lessonPlayer scope to reset the player
 * whenever the active sign changes.
 *
 * Registered via Alpine.data() — do NOT import Alpine directly here;
 * it is already initialised by Livewire/Flux (@fluxScripts).
 */
export default () => ({
    isPlaying: false,
    isLooping: true,
    speed: 1.0,
    currentTime: 0,
    duration: 4,
    videoError: false,

    init() {
        // Reset player state whenever the parent's activeIndex changes
        this.$watch('activeIndex', () => this.reset());
    },

    get videoProgress() {
        return this.duration
            ? Math.min(100, Math.round((this.currentTime / this.duration) * 100))
            : 0;
    },

    get formattedTime() {
        const fmt = (s) => '00:' + String(Math.floor(s || 0)).padStart(2, '0');
        return fmt(this.currentTime) + ' / ' + fmt(this.duration);
    },

    reset() {
        this.videoError  = false;
        this.isPlaying   = false;
        this.currentTime = 0;
        this.$nextTick(() => {
            if (this.$refs.player) {
                this.$refs.player.load();
                this.$refs.player.playbackRate = this.speed;
            }
        });
    },

    togglePlay() {
        if (this.videoError || !this.$refs.player) return;
        if (this.$refs.player.paused) {
            this.$refs.player.play();
            this.isPlaying = true;
        } else {
            this.$refs.player.pause();
            this.isPlaying = false;
        }
    },

    setSpeed(val) {
        this.speed = val;
        if (this.$refs.player) this.$refs.player.playbackRate = val;
    },

    seek(e) {
        if (this.videoError || !this.$refs.player || !this.duration) return;
        const rect  = e.currentTarget.getBoundingClientRect();
        const ratio = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        this.$refs.player.currentTime = ratio * this.duration;
        this.currentTime = this.$refs.player.currentTime;
    },
});
