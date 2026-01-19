import './bootstrap';
import { createApp } from 'vue';
import RecruitNewMember from './components/recruit/RecruitNewMember.vue';

const container = document.getElementById('recruiting-container');
if (container) {
    const app = createApp(RecruitNewMember, {
        division: container.getAttribute('data-division'),
        recruiterId: container.getAttribute('data-recruiter-id'),
        ranks: JSON.parse(container.getAttribute('data-ranks') || '{}'),
        rankLabels: JSON.parse(container.getAttribute('data-rank-labels') || '{}'),
    });
    app.mount('#recruiting-container');
}
