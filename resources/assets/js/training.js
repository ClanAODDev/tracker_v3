(function() {
    'use strict';

    const TrainingStepper = {
        currentStep: 0,
        checkpoints: {},
        totalCheckpoints: 0,

        init() {
            this.cacheElements();
            if (!this.$container) return;
            this.bindEvents();
            this.calculateTotals();
            this.addHeadingAnchors();
            this.openLinksInNewWindow();
            this.handleInitialHash();
        },

        cacheElements() {
            this.$container = document.querySelector('.training-stepper');
            if (!this.$container) return;

            this.$steps = this.$container.querySelectorAll('.training-stepper__step');
            this.$sections = this.$container.querySelectorAll('.training-stepper__section');
            this.$checkboxes = this.$container.querySelectorAll('.training-checkpoint');
            this.$progressFill = this.$container.querySelector('.training-stepper__progress-fill');
            this.$totalComplete = this.$container.querySelector('.total-complete');
        },

        bindEvents() {
            this.$steps.forEach((step) => {
                step.addEventListener('click', () => {
                    const stepIndex = parseInt(step.dataset.step, 10);
                    this.goToStep(stepIndex);
                });
            });

            this.$container.querySelectorAll('.training-stepper__prev, .training-stepper__next').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const target = parseInt(btn.dataset.target, 10);
                    this.goToStep(target);
                });
            });

            this.$checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    this.handleCheckboxChange(checkbox);
                });
            });

            window.addEventListener('hashchange', () => {
                this.handleHashChange();
            });

            const fullscreenBtn = this.$container.querySelector('.training-stepper__fullscreen-toggle');
            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', () => {
                    this.toggleFullscreen();
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isFullscreen()) {
                    this.toggleFullscreen();
                }
            });

            this.$container.querySelectorAll('.training-stepper__checkpoint-toggle').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const wrapper = btn.closest('.training-stepper__checkpoint-wrapper');
                    wrapper.classList.toggle('expanded');
                });
            });
        },

        isFullscreen() {
            return document.getElementById('training-container').classList.contains('training-fullscreen');
        },

        toggleFullscreen() {
            const container = document.getElementById('training-container');
            container.classList.toggle('training-fullscreen');
            document.body.style.overflow = this.isFullscreen() ? 'hidden' : '';
        },

        addHeadingAnchors() {
            const headings = this.$container.querySelectorAll('.training-stepper__markdown h4, .training-stepper__markdown h5');

            headings.forEach((heading) => {
                const text = heading.textContent.trim();
                const id = this.slugify(text);
                heading.id = id;

                const anchor = document.createElement('a');
                anchor.href = `#${id}`;
                anchor.className = 'heading-anchor';
                anchor.innerHTML = '<i class="fa fa-link"></i>';
                anchor.title = 'Link to this section';

                anchor.addEventListener('click', (e) => {
                    e.preventDefault();
                    history.pushState(null, null, `#${id}`);
                    this.scrollToElement(heading);
                });

                heading.appendChild(anchor);
            });
        },

        openLinksInNewWindow() {
            const links = this.$container.querySelectorAll('.training-stepper__markdown a:not(.heading-anchor)');
            links.forEach((link) => {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            });
        },

        slugify(text) {
            return text
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        },

        handleInitialHash() {
            if (window.location.hash) {
                this.handleHashChange();
            }
        },

        handleHashChange() {
            const hash = window.location.hash.substring(1);
            if (!hash) return;

            const target = document.getElementById(hash);
            if (!target) return;

            const section = target.closest('.training-stepper__section');
            if (!section) return;

            const sectionIndex = parseInt(section.dataset.section, 10);
            this.goToStep(sectionIndex);

            setTimeout(() => {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                target.classList.add('heading-highlight');
                setTimeout(() => {
                    target.classList.remove('heading-highlight');
                }, 2000);
            }, 100);
        },

        scrollToElement(element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            element.classList.add('heading-highlight');
            setTimeout(() => {
                element.classList.remove('heading-highlight');
            }, 2000);
        },

        calculateTotals() {
            this.totalCheckpoints = this.$checkboxes.length;

            this.$steps.forEach((step, index) => {
                const sectionCheckpoints = this.$container.querySelectorAll(`.training-checkpoint[data-section="${index}"]`);
                this.checkpoints[index] = {
                    total: sectionCheckpoints.length,
                    checked: 0
                };
            });
        },

        goToStep(stepIndex) {
            if (stepIndex < 0 || stepIndex >= this.$sections.length) return;

            this.$steps.forEach((step) => {
                step.classList.remove('active');
            });
            this.$sections.forEach((section) => {
                section.classList.remove('active');
            });

            this.$steps[stepIndex].classList.add('active');
            this.$sections[stepIndex].classList.add('active');
            this.currentStep = stepIndex;
        },

        handleCheckboxChange(checkbox) {
            const sectionIndex = parseInt(checkbox.dataset.section, 10);
            const label = checkbox.closest('.training-stepper__checkpoint');

            if (checkbox.checked) {
                label.classList.add('checked');
                this.checkpoints[sectionIndex].checked++;
            } else {
                label.classList.remove('checked');
                this.checkpoints[sectionIndex].checked--;
            }

            this.updateProgress();
            this.updateStepStatus(sectionIndex);
        },

        updateProgress() {
            let totalChecked = 0;
            Object.values(this.checkpoints).forEach((checkpoint) => {
                totalChecked += checkpoint.checked;
            });

            const percentage = this.totalCheckpoints > 0
                ? (totalChecked / this.totalCheckpoints) * 100
                : 0;

            if (this.$progressFill) {
                this.$progressFill.style.width = `${percentage}%`;
            }
            if (this.$totalComplete) {
                this.$totalComplete.textContent = totalChecked;
            }
        },

        updateStepStatus(sectionIndex) {
            const step = this.$steps[sectionIndex];
            const data = this.checkpoints[sectionIndex];
            const countSpan = step.querySelector('.checkpoint-count');

            if (countSpan) {
                countSpan.textContent = data.checked;
            }

            if (data.checked === data.total && data.total > 0) {
                step.classList.add('completed');
            } else {
                step.classList.remove('completed');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        TrainingStepper.init();
    });
})();
