/**
 * Alpine.js component: lessonPlayer
 *
 * Manages state for the lesson (materi) player page.
 * Registered via Alpine.data() — do NOT import Alpine directly here;
 * it is already initialised by Livewire/Flux (@fluxScripts).
 *
 * @param {Object} config
 * @param {Array}  config.items            - Lesson items array (from @js($items))
 * @param {number} config.xpReward         - XP reward for completing this part
 * @param {Array}  config.completedIndices - Pre-completed indices (when $alreadyDone)
 */
export default (config = {}) => ({
    items: config.items ?? [],
    xpReward: config.xpReward ?? 0,
    activeIndex: 0,
    completedIndices: config.completedIndices ?? [],

    get currentItem() {
        return this.items[this.activeIndex] || {};
    },

    get isLastItem() {
        return this.activeIndex >= this.items.length - 1;
    },

    get progressPercent() {
        return this.items.length
            ? Math.round(((this.activeIndex + 1) / this.items.length) * 100)
            : 100;
    },

    get nextButtonLabel() {
        if (this.isLastItem) {
            return 'Selesai & Klaim +' + this.xpReward + ' XP ✓';
        }
        const nextTitle = this.items[this.activeIndex + 1]?.title ?? 'Berikutnya';
        return 'Lanjut ' + nextTitle + ' →';
    },

    selectItem(idx) {
        this.activeIndex = idx;
    },

    prevItem() {
        if (this.activeIndex > 0) {
            this.activeIndex--;
        }
    },

    nextOrComplete() {
        if (!this.completedIndices.includes(this.activeIndex)) {
            this.completedIndices.push(this.activeIndex);
        }

        if (!this.isLastItem) {
            this.activeIndex++;
        } else {
            this.$wire.complete();
        }
    },
});
