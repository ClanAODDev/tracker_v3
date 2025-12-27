(function() {
    'use strict';

    var TrainingStepper = {
        currentStep: 0,
        checkpoints: {},
        totalCheckpoints: 0,

        init: function() {
            this.cacheElements();
            if (!this.$container) return;
            this.bindEvents();
            this.calculateTotals();
            this.addHeadingAnchors();
            this.openLinksInNewWindow();
            this.handleInitialHash();
        },

        cacheElements: function() {
            this.$container = document.querySelector('.training-stepper');
            if (!this.$container) return;

            this.$steps = this.$container.querySelectorAll('.training-stepper__step');
            this.$sections = this.$container.querySelectorAll('.training-stepper__section');
            this.$checkboxes = this.$container.querySelectorAll('.training-checkpoint');
            this.$progressFill = this.$container.querySelector('.training-stepper__progress-fill');
            this.$totalComplete = this.$container.querySelector('.total-complete');
        },

        bindEvents: function() {
            var self = this;

            this.$steps.forEach(function(step) {
                step.addEventListener('click', function() {
                    var stepIndex = parseInt(this.dataset.step, 10);
                    self.goToStep(stepIndex);
                });
            });

            this.$container.querySelectorAll('.training-stepper__prev, .training-stepper__next').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var target = parseInt(this.dataset.target, 10);
                    self.goToStep(target);
                });
            });

            this.$checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    self.handleCheckboxChange(this);
                });
            });

            window.addEventListener('hashchange', function() {
                self.handleHashChange();
            });

            var fullscreenBtn = this.$container.querySelector('.training-stepper__fullscreen-toggle');
            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', function() {
                    self.toggleFullscreen();
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && self.isFullscreen()) {
                    self.toggleFullscreen();
                }
            });

            this.$container.querySelectorAll('.training-stepper__checkpoint-toggle').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var wrapper = this.closest('.training-stepper__checkpoint-wrapper');
                    wrapper.classList.toggle('expanded');
                });
            });
        },

        isFullscreen: function() {
            return document.getElementById('training-container').classList.contains('training-fullscreen');
        },

        toggleFullscreen: function() {
            var container = document.getElementById('training-container');
            container.classList.toggle('training-fullscreen');
            document.body.style.overflow = this.isFullscreen() ? 'hidden' : '';
        },

        addHeadingAnchors: function() {
            var self = this;
            var headings = this.$container.querySelectorAll('.training-stepper__markdown h4, .training-stepper__markdown h5');

            headings.forEach(function(heading) {
                var text = heading.textContent.trim();
                var id = self.slugify(text);
                heading.id = id;

                var anchor = document.createElement('a');
                anchor.href = '#' + id;
                anchor.className = 'heading-anchor';
                anchor.innerHTML = '<i class="fa fa-link"></i>';
                anchor.title = 'Link to this section';

                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    history.pushState(null, null, '#' + id);
                    self.scrollToElement(heading);
                });

                heading.appendChild(anchor);
            });
        },

        openLinksInNewWindow: function() {
            var links = this.$container.querySelectorAll('.training-stepper__markdown a:not(.heading-anchor)');
            links.forEach(function(link) {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            });
        },

        slugify: function(text) {
            return text
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        },

        handleInitialHash: function() {
            if (window.location.hash) {
                this.handleHashChange();
            }
        },

        handleHashChange: function() {
            var hash = window.location.hash.substring(1);
            if (!hash) return;

            var target = document.getElementById(hash);
            if (!target) return;

            var section = target.closest('.training-stepper__section');
            if (!section) return;

            var sectionIndex = parseInt(section.dataset.section, 10);
            this.goToStep(sectionIndex);

            setTimeout(function() {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                target.classList.add('heading-highlight');
                setTimeout(function() {
                    target.classList.remove('heading-highlight');
                }, 2000);
            }, 100);
        },

        scrollToElement: function(element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            element.classList.add('heading-highlight');
            setTimeout(function() {
                element.classList.remove('heading-highlight');
            }, 2000);
        },

        calculateTotals: function() {
            var self = this;
            this.totalCheckpoints = this.$checkboxes.length;

            this.$steps.forEach(function(step, index) {
                var sectionCheckpoints = self.$container.querySelectorAll('.training-checkpoint[data-section="' + index + '"]');
                self.checkpoints[index] = {
                    total: sectionCheckpoints.length,
                    checked: 0
                };
            });
        },

        goToStep: function(stepIndex) {
            if (stepIndex < 0 || stepIndex >= this.$sections.length) return;

            this.$steps.forEach(function(step) {
                step.classList.remove('active');
            });
            this.$sections.forEach(function(section) {
                section.classList.remove('active');
            });

            this.$steps[stepIndex].classList.add('active');
            this.$sections[stepIndex].classList.add('active');
            this.currentStep = stepIndex;
        },

        handleCheckboxChange: function(checkbox) {
            var sectionIndex = parseInt(checkbox.dataset.section, 10);
            var label = checkbox.closest('.training-stepper__checkpoint');

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

        updateProgress: function() {
            var totalChecked = 0;
            for (var key in this.checkpoints) {
                totalChecked += this.checkpoints[key].checked;
            }

            var percentage = this.totalCheckpoints > 0
                ? (totalChecked / this.totalCheckpoints) * 100
                : 0;

            if (this.$progressFill) {
                this.$progressFill.style.width = percentage + '%';
            }
            if (this.$totalComplete) {
                this.$totalComplete.textContent = totalChecked;
            }
        },

        updateStepStatus: function(sectionIndex) {
            var step = this.$steps[sectionIndex];
            var data = this.checkpoints[sectionIndex];
            var countSpan = step.querySelector('.checkpoint-count');

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

    document.addEventListener('DOMContentLoaded', function() {
        TrainingStepper.init();
    });
})();
