/**
 * Alpine.js component: quizPlayer
 *
 * Manages state for the quiz player page.
 * Registered via Alpine.data() — do NOT import Alpine directly here;
 * it is already initialised by Livewire/Flux (@fluxScripts).
 *
 * @param {Object} config
 * @param {Array}  config.questions - Quiz questions array (from @js($questions))
 */
export default (config = {}) => ({
    questions: config.questions ?? [],
    quizIndex: 0,
    selectedOptions: [],
    answeredStates: [],
    quizVideoError: false,
    quizIsPlaying: false,
    quizSpeed: 1.0,
    quizCurrentTime: 0,
    quizDuration: 4,

    init() {
        // Initialise per-question state arrays after questions are set
        this.selectedOptions = Array.from({ length: this.questions.length }, () => null);
        this.answeredStates  = Array.from({ length: this.questions.length }, () => false);

        // Reset video state whenever the active question changes
        this.$watch('quizIndex', () => {
            this.quizVideoError  = false;
            this.quizIsPlaying   = false;
            this.quizCurrentTime = 0;
            this.$nextTick(() => {
                if (this.$refs.quizPlayer) {
                    this.$refs.quizPlayer.load();
                    this.$refs.quizPlayer.playbackRate = this.quizSpeed;
                }
            });
        });
    },

    get currentQuestion() {
        return this.questions[this.quizIndex] || { options: [], correct_index: 0 };
    },

    get currentSelected() {
        return this.selectedOptions[this.quizIndex] ?? null;
    },

    get isCurrentAnswered() {
        return Boolean(this.answeredStates[this.quizIndex]);
    },

    get correctCount() {
        return this.questions.reduce((total, q, idx) => {
            return total + (this.answeredStates[idx] && this.selectedOptions[idx] === Number(q.correct_index) ? 1 : 0);
        }, 0);
    },

    get progressPercent() {
        const answered = this.answeredStates.filter(Boolean).length;
        return this.questions.length
            ? Math.round((answered / this.questions.length) * 100)
            : 100;
    },

    get scorePercent() {
        return this.questions.length
            ? Math.round((this.correctCount / this.questions.length) * 100)
            : 0;
    },

    selectOption(idx) {
        if (this.isCurrentAnswered) return;
        this.selectedOptions.splice(this.quizIndex, 1, idx);
    },

    checkAnswer() {
        if (this.currentSelected === null || this.isCurrentAnswered) return;
        this.answeredStates.splice(this.quizIndex, 1, true);
    },

    prevQuestion() {
        if (this.quizIndex > 0) {
            this.quizIndex--;
        }
    },

    nextQuestion() {
        if (this.quizIndex < this.questions.length - 1) {
            this.quizIndex++;
        } else {
            this.$wire.completeQuiz(this.scorePercent, this.correctCount);
        }
    },

    toggleQuizPlay() {
        if (this.quizVideoError || !this.$refs.quizPlayer) return;
        if (this.$refs.quizPlayer.paused) {
            this.$refs.quizPlayer.play();
            this.quizIsPlaying = true;
        } else {
            this.$refs.quizPlayer.pause();
            this.quizIsPlaying = false;
        }
    },

    setQuizSpeed(val) {
        this.quizSpeed = val;
        if (this.$refs.quizPlayer) {
            this.$refs.quizPlayer.playbackRate = val;
        }
    },
});
